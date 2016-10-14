<?php
class Sabai_Addon_File extends Sabai_Addon
    implements Sabai_Addon_Form_IFields,
               Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets,
               Sabai_Addon_Field_IRenderers,
               Sabai_Addon_File_IStorage,
               Sabai_Addon_System_IAdminSettings
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';
    
    protected $_path;
        
    protected function _init()
    {
        $this->_path = $this->_application->Path(dirname(__FILE__) . '/File');
    }
    
    public function systemGetMainRoutes()
    {
        $routes = array(
            '/sabai/file/upload' => array(
                'controller' => 'UploadFile',
            ),
        );
        foreach ($this->_application->getModel('FieldConfig', 'Entity')->type_in(array('file_image', 'file_file'))->fetch()->with('Fields', 'Bundle') as $field_config) {            
            foreach ($field_config->Fields as $field) {
                if (!$field->Bundle) continue;
                $base_path = empty($field->Bundle->info['permalink_path'])
                    ? $field->Bundle->getPath() . '/:entity_id'
                    : $field->Bundle->info['permalink_path'] . '/:slug';
                if (!isset($routes[$base_path . '/file/:file_id'])) {
                    $routes[$base_path . '/file/:file_id'] = array(
                        'controller' => 'File',
                        'type' => Sabai::ROUTE_CALLBACK,
                        'callback_path' => 'file',
                        'access_callback' => true,
                        'data' => array(
                            'fields' => array(),
                        ),
                    );
                }
                $routes[$base_path . '/file/:file_id']['data']['fields'][] = $field->getFieldName();
            }
        }

        return $routes;
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'file':
                if (!$file_id = $context->getRequest()->asInt('file_id')) {
                    return false;
                }
                $this->_application->Entity_LoadFields($context->entity);
                foreach ((array)$route['data']['fields'] as $field_name) {
                    if (!$files = $context->entity->getFieldValue($field_name)) {
                        continue;
                    }
                    foreach ($files as $file) {
                        if ($file['id'] === $file_id) {
                            $context->file = $file;
                            return true;
                        }
                    }
                }                
                return false;
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route){}

    public function fieldGetTypeNames()
    {
        return array('file_file', 'file_image');
    }

    public function fieldGetType($name)
    {
        return new Sabai_Addon_File_FieldType($this, $name);
    }

    public function fieldGetWidgetNames()
    {
        return array('file_upload');
    }

    public function fieldGetWidget($name)
    {
        return new Sabai_Addon_File_FieldWidget($this, $name);
    }
    
    public function fieldGetRendererNames()
    {
        return array('file_file', 'file_image', 'file_carousel');
    }

    public function fieldGetRenderer($name)
    {
        switch ($name) {
            case 'file_carousel':
                return new Sabai_Addon_File_CarouselFieldRenderer($this, $name);
            default:
            return new Sabai_Addon_File_FieldRenderer($this, $name);
        }
    }

    public function formGetFieldTypes()
    {
        return array('file_upload');
    }

    public function formGetField($type)
    {
        switch ($type) {
            case 'file_upload':
                return new Sabai_Addon_File_UploadFormField($this, $type);
        }
    }  

    public function onSabaiRunCron($lastrun, $logs)
    {
        // Delete expired tokens and associated files
        $tokens = $this->getModel('Token')->expires_isSmallerThan(time())->fetch()->with('Files');
        if ($count = count($tokens)) {        
            foreach ($tokens as $token) {
                $token->markRemoved();
                foreach ($token->Files as $file) {
                    $file->unlink();
                    $file->markRemoved();
                }
            }
            $this->getModel()->commit();
        }
        $logs[] = sprintf(__('Deleted %d expired file upload token(s).', 'sabai'), $count);
    }

    public function getDefaultConfig()
    {        
        return array(
            'tmp_dir' => '',
            'upload_dir' => '',
            'thumbnail_dir' => '',
            'thumbnail_width' => 200,
            'thumbnail_height' => 200,
            'image_medium_width' => 400,
            'image_large_width' => 1024,
            'no_pretty_url' => true,
            'resize_method' => 'scale',
        );
    }
    
    public function getUploadDir()
    {
        return $this->_config['upload_dir'] ? $this->_config['upload_dir'] : $this->getVarDir('files');
    }
    
    public function getThumbnailDir()
    {
        return $this->_config['thumbnail_dir'] ? $this->_config['thumbnail_dir'] : $this->getVarDir('thumbnails');
    }
    
    public function getTmpDir()
    {
        return $this->_config['tmp_dir'] ? $this->_config['tmp_dir'] : $this->getVarDir('tmp');
    }
    
    public function hasVarDir()
    {
        return array('tmp', 'files', 'thumbnails');
    }
    
    public function getStorage()
    {
        return $this;
    }
    
    public function fileStoragePut($name, $content, array $options)
    {
        $upload_dir = $this->getUploadDir();
        $this->_application->ValidateDirectory($upload_dir, true);
        
        if ($options['is_image'] && !empty($options['thumbnail'])) {
            $thumbnail_dir = $this->getThumbnailDir();
            $this->_application->ValidateDirectory($thumbnail_dir, true);
        }
        
        $upload_file = $upload_dir . '/' . $name;

        if (false === file_put_contents($upload_file, $content)) {
            throw new Sabai_RuntimeException(sprintf(__('Failed saving file %s to the upload directory.', 'sabai'), $name));
        }
 
        if ($options['is_image']) {
            // Read EXIF data and adjust orientation
            if (function_exists('exif_read_data')
                && ($exif = @exif_read_data($upload_file))
                && !empty($exif['Orientation'])
            ) {
                switch (intval($exif['Orientation'])) {
                    case 6:
                        $rotate = 90;
                        break;
                    case 3:
                        $rotate = 180;
                        break;
                    case 8:
                        $rotate = 270;
                        break;
                    default:
                        $rotate = false;
                }
                if ($rotate) {
                    if (extension_loaded('imagick') && class_exists('Imagick') && class_exists('ImagickPixel')) {
                        $imagick = new Imagick();
                        $imagick->readImage($upload_file);
                        $imagick->rotateImage(new ImagickPixel(), $rotate);
                        $imagick->setImageOrientation(defined('imagick::ORIENTATION_TOPLEFT') ? imagick::ORIENTATION_TOPLEFT : 1);
                        $imagick->writeImage();
                        $imagick->clear();
                        $imagick->destroy();
                    } elseif (extension_loaded('gd') && function_exists('gd_info')) {
                        // No Imagick, fallback to GD
                        // GD needs negative degrees
                        $rotate = -$rotate;

                        switch ($options['type']) {
                            case 'image/jpeg':
                                if (($source = imagecreatefromjpeg($upload_file))
                                    && ($rotated = imagerotate($source, $rotate, 0))
                                ) {
                                    imagejpeg($rotated, $upload_file);
                                    imagedestroy($source);
                                    imagedestroy($rotated);
                                }
                                break;
                            case 'image/png':
                                if (($source = imagecreatefrompng($upload_file))
                                    && ($rotated = imagerotate($source, $rotate, 0))
                                ) {
                                    imagepng($rotated, $upload_file);
                                    imagedestroy($source);
                                    imagedestroy($rotated);
                                }
                                break;
                            case 'image/gif':
                                if (($source = imagecreatefromgif($upload_file))
                                    && ($rotated = imagerotate($source, $rotate, 0))
                                ) {
                                    imagegif($rotated, $upload_file);
                                    imagedestroy($source);
                                    imagedestroy($rotated);
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            } 

            if (!isset($options['thumbnail']) || $options['thumbnail'] !== false) {
                $thumbnail_width = empty($options['thumbnail_width']) ? $this->_config['thumbnail_width'] : $options['thumbnail_width'];
                $thumbnail_height = empty($options['thumbnail_height']) ? $this->_config['thumbnail_height'] : $options['thumbnail_height'];
                if ($options['width'] <= $thumbnail_width && $options['height'] <= $thumbnail_height) {
                    // Do not resize if smaller than the requested dimension
                    file_put_contents($thumbnail_dir . '/' . $name, $content);
                } else {
                    $this->_application->getPlatform()->resizeImage(
                        $upload_file,
                        $thumbnail_dir . '/' . $name,
                        $this->_config['thumbnail_width'],
                        $this->_config['thumbnail_height'],
                        @$this->_config['resize_method'] === 'crop'
                    );
                }
            }
            if (!empty($options['medium_image'])) {
                $medium_image_width = empty($options['medium_image_width']) ? $this->_config['image_medium_width'] : $options['medium_image_width'];
                $this->_application->getPlatform()->resizeImage($upload_file, $upload_dir . '/m_' . $name, $medium_image_width, null);
            }
            if (!empty($options['large_image'])) {
                $large_image_width = empty($options['large_image_width']) ? $this->_config['image_large_width'] : $options['large_image_width'];
                $this->_application->getPlatform()->resizeImage($upload_file, $upload_dir . '/l_' . $name, $large_image_width, null);
            }
        }
    }
    
    public function fileStorageGetStream($name, $size = null)
    {
        if (isset($size)) {
            if ($size === 'medium') {
                $name = 'm_' . $name;
            } elseif ($size === 'large') {
                $name = 'l_' . $name;
            }
        }
        $file = $this->getUploadDir() . '/' . $name;

        if (!file_exists($file)) {
            throw new Sabai_RuntimeException(sprintf(__('File %s does not exist.', 'sabai'), $name));
        }

        if (false === $resource = fopen($file, 'rb')) {
            throw new Sabai_RuntimeException(sprintf(__('Failed opening stream for file %s.', 'sabai'), $name));
        }

        return $resource;
    }
    
    public function fileStorageGetUrl($name, $size = null)
    {
        if ($size) {
            if ($size === 'medium') {
                $name = 'm_' . $name;
            } elseif ($size === 'large') {
                $name = 'l_' . $name;
            }
        }
        return $this->_application->FileUrl($this->getUploadDir() . '/' . $name);
    }
    
    public function fileStorageGetThumbnailUrl($name)
    {
        return $this->_application->FileUrl($this->getThumbnailDir() . '/' . $name);
    }
    
    public function fileStorageDelete($name)
    {
        @unlink($this->getUploadDir() . '/' . $name);
    }
    
    public function onEntityDeleteFieldConfigsSuccess($removedFields)
    {
        foreach ($removedFields as $removed_field) {
            if (in_array($removed_field->type, array('file_file', 'file_image'))) {
                // Reload system routing tables to reflect changes
                $this->_application->getAddon('System')->reloadRoutes($this);
                return; // once is enough
            }
        }
    }
    
    public function onEntityCreateBundlesSuccess($entityType, $bundles)
    {        
        foreach ($bundles as $bundle) {
            foreach (array('file_file', 'file_image') as $field_type) {
                // Add the file field
                if (!empty($bundle->info[$field_type])) {
                    $field_settings = $bundle->info[$field_type];
                    $this->_application->getAddon('Entity')->createEntityField(
                        $bundle,
                        $field_type,
                        array(
                            'type' => $field_type,
                            'hide_label' => !empty($field_settings['hide_label']),
                            'label' => isset($field_settings['label']) ? $field_settings['label'] : __('File Attachments', 'sabai'),
                            'description' => isset($field_settings['description']) ? $field_settings['description'] : '',
                            'widget' => isset($field_settings['widget']) ? $field_settings['widget'] : 'file_upload',
                            'widget_settings' => isset($field_settings['widget_settings']) ? $field_settings['widget_settings'] : array(),
                            'required' => !empty($field_settings['required']),
                            'weight' => isset($field_settings['weight']) ? $field_settings['weight'] : null,
                            'max_num_items' => isset($field_settings['max_num_items']) ? $field_settings['max_num_items'] : 0,
                        ),
                        Sabai_Addon_Entity::FIELD_REALM_ALL
                    );
                    $bundle->setInfo($field_type, true);
                }
            }
        }

        // Reload system routing tables to reflect changes
        $this->_application->getAddon('System')->reloadRoutes($this);
    }
    
    public function onEntityUpdateBundlesSuccess($entityType, $bundles)
    {
        $this->onEntityCreateBundlesSuccess($entityType, $bundles);
    }
    
    public function onEntityDeleteBundlesSuccess($entityType, $bundles)
    {
        // Reload system routing tables to reflect changes
        $this->_application->getAddon('System')->reloadRoutes($this);
    }
    
    public function onFieldUISubmitFieldSuccess($field, $isEdit)
    {
        if ($isEdit || !in_array($field->getFieldType(), array('file_file', 'file_image'))) {
            return;
        }
        // Reload system routing tables to reflect changes
        $this->_application->getAddon('System')->reloadRoutes($this);
    }
    
    public function onEntityRenderContentHtml(Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Entity_Entity $entity, $displayMode, $id, &$classes, &$links, &$buttons)
    {
        if ($displayMode === 'preview'
            || $displayMode === 'full'
            || (isset($bundle->info['file_content_icons']) && false === $bundle->info['file_content_icons'])
        ) {
            return;
        }
        
        if ($file_field_names = $entity->getFieldNamesByType('file_file')) {
            foreach ($file_field_names as $field_name) {
                if ($entity->getFieldValue($field_name)) {
                    $entity->data['entity_icons']['file_file'] = array(
                        'icon' => 'file-o',
                        'title' => __('This post has one or more files attached.', 'sabai'),
                    );
                    break;
                }
            }
        }
        
        if ($image_field_names = $entity->getFieldNamesByType('file_image')) {
            foreach ($image_field_names as $field_name) {
                if ($entity->getFieldValue($field_name)) {
                    $entity->data['entity_icons']['file_image'] = array(
                        'icon' => 'file-image-o',
                        'title' => __('This post has one or more images attached.', 'sabai'),
                    );
                    break;
                }
            }
        }
    }

    public function systemGetAdminRoutes()
    {
        return array(
            '/sabai/file/upload' => array(
                'controller' => 'UploadFile',
            ),
        );
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route){}

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route){}
    
    public function systemGetAdminSettingsForm()
    {
        return array(
            'upload_dir' => array(
                '#type' => 'textfield',
                '#default_value' => $this->_config['upload_dir'],
                '#title' => __('File upload directory', 'sabai'),
                '#element_validate' => array(array(array($this, 'validateDir'), array('upload_dir'))),
                '#description' => sprintf(
                    __('Enter the path to a directory where uploaded files are stored. Leave blank to use the system default (%s). This directory must be writeable by the server.', 'sabai'),
                    $this->getVarDir('files')
                ),
            ),
            'tmp_dir' => array(
                '#type' => 'textfield',
                '#default_value' => $this->_config['tmp_dir'],
                '#title' => __('Temporary file upload directory', 'sabai'),
                '#element_validate' => array(array(array($this, 'validateDir'), array('tmp_dir'))),
                '#description' => sprintf(
                    __('Enter the path to a directory where temporary uploaded files are stored. Leave blank to use the system default (%s). This directory must be writeable by the server.', 'sabai'),
                    $this->getVarDir('tmp')
                ),
            ),
            'thumbnail_dir' => array(
                '#type' => 'textfield',
                '#default_value' => $this->_config['thumbnail_dir'],
                '#title' => __('Thumbnail directory', 'sabai'),
                '#element_validate' => array(array(array($this, 'validateDir'), array('thumbnail_dir'))),
                '#description' => sprintf(
                    __('Enter the path to a directory where thumbnail files are stored. Leave blank to use the system default (%s). This directory must be writeable by the server and accessible by the web browser.', 'sabai'),
                    $this->getVarDir('thumbnails')
                ),
            ),
            'thumbnail_size' => array(
                '#class' => 'sabai-form-inline',
                '#tree' => false,
                '#title' => __('Thumbnail size', 'sabai'),
                '#description' => __('Enter the dimension of thumbnail files in pixels.', 'sabai'),
                '#collapsible' => false,
                'thumbnail_width' => array(
                    '#type' => 'number',
                    '#size' => 5,
                    '#integer' => true,
                    '#min_value' => 0,
                    '#default_value' => $this->_config['thumbnail_width'],
                    '#field_suffix' => ' x ',
                ),
                'thumbnail_height' => array(
                    '#type' => 'number',
                    '#size' => 5,
                    '#integer' => true,
                    '#min_value' => 0,
                    '#default_value' => $this->_config['thumbnail_height'],
                ),
            ),
            'resize_method' => array(
                '#title' => __('Thumbnail resize method', 'sabai'),
                '#type' => 'radios',
                '#options' => array('crop' => __('Crop', 'sabai'), 'scale' => __('Scale', 'sabai')),
                '#default_value' => $this->_config['resize_method'],
                '#class' => 'sabai-form-inline',
            ),
            'no_pretty_url' => array(
                '#type' => 'checkbox',
                '#title' => __('Disable pretty URLs', 'sabai'),
                '#default_value' => !empty($this->_config['no_pretty_url']),
            ),
        );
    }
    
    public function validateDir($form, &$value, $element, $self)
    {
        $value = trim($value);
        if (!strlen($value)) {
            return;
        }
        foreach (array('upload_dir', 'thumbnail_dir', 'tmp_dir') as $dir) {
            if ($dir !== $self && $form->values[$dir] === $value) {
                $form->setError(__('The path must be different fom the other two.', 'sabai'), $element);
                return;
            }
        }
        $this->_application->ValidateDirectory($value, true);
    }
}