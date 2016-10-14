<?php
class Sabai_Addon_File_UploadFormField extends Sabai_Addon_Form_Field_AbstractField
{
    private static $_uploadFields = array();

    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Add file upload field
        $allowed_extensions = isset($data['#allowed_extensions']) ? $data['#allowed_extensions'] : array('jpeg', 'jpg', 'gif', 'png', 'txt', 'pdf', 'zip');
        $max_file_size = !empty($data['#max_file_size']) ? $data['#max_file_size'] : (($size = @ini_get('upload_max_filesize')) ? $size * 1024 : 2048);
        $allow_only_images = !empty($data['#allow_only_images']);

        $file_settings = $data;
        $file_settings['#type'] = 'file';
        $file_settings['#label'] = array(
            '', // no title for this element
            sprintf(
                __('Supported file formats: %s', 'sabai'),
                $allow_only_images ? 'gif jpeg jpg png' : implode(' ', $allowed_extensions)
            )
        );
        $file_settings['#upload_dir'] = null; // file is uploaded by the storage plugin
        $file_settings['#max_file_size'] = $max_file_size;
        $file_settings['#allowed_extensions'] = $allowed_extensions;
        $file_settings['#allowed_only_images'] = $allow_only_images;
        $file_settings['#collapsible'] = false;
        $file_settings['#class'] = 'sabai-file-upload';
        $file_settings['#prefix'] = '<div class="sabai-file-upload-container">';
        $file_settings['#suffix'] = '</div>';
        $file_settings['#attributes']['id'] = $form->getFieldId($name) . '-file';

        // Define element settings
        $data = array(
            '#tree' => true,
            '#type' => $data['#type'],
            '#label' => $data['#label'],
            '#required' => $data['#required'],
            '#tree' => true,
            '#multiple' => !empty($data['#multiple']),
            '#class' => 'sabai-form-group ' . $data['#class'],
            '#children' => array(),
            '#max_num_files' => (int)@$file_settings['#max_num_files'],
        ) + $form->defaultElementSettings();
        
        // Assign #states to the parent form element
        if (isset($file_settings['#states'])) {
            $data['#states'] = $file_settings['#states'];
            unset($file_settings['#states']);
        }

        // Add current file selection fields
        $current_file_options = $data['#_current_files'] = array();
        $row_attr = isset($file_settings['#row_attributes']) ? $file_settings['#row_attributes'] : array();
        if (!empty($file_settings['#default_value'])) {
            if (is_array($file_settings['#default_value'])) {
                if (isset($file_settings['#default_value']['current'])) {
                    // values from previous submit
                    $file_settings['#default_value'] = array_keys($file_settings['#default_value']['current']);
                }
            } else {
                $file_settings['#default_value'] = array($file_settings['#default_value']);
            }
            if (!empty($file_settings['#default_value'])) {
                foreach ($this->_addon->getModel('File')->fetchByIds($file_settings['#default_value']) as $file) {
                    $current_file_options[$file->id] = array(
                        'name' => Sabai::h($file->title),
                        'size' => $file->getHumanReadableSize(),
                        'extension' => $file->extension,
                        'icon' => $file->is_image
                            ? sprintf('<img src="%s" alt="" />', $this->_addon->getApplication()->File_ThumbnailUrl($file->name))
                            : sprintf('<i class="fa %s" />', $this->_addon->getApplication()->File_Icon($file->extension)),
                    );
                    $data['#_current_files'][$file->id] = $file;
                    if (!isset($row_attr[$file->id]['@row']['class'])) {
                        $row_attr[$file->id]['@row']['class'] = 'sabai-file-row';
                    } else {
                        $row_attr[$file->id]['@row']['class'] .= ' sabai-file-row'; 
                    }
                }
                if (!empty($current_file_options)) {
                    $_current_file_options = array();
                    // Reorder options as it was stored
                    foreach ($file_settings['#default_value'] as $file_id) {
                        if (isset($current_file_options[$file_id])) {
                            $_current_file_options[$file_id] = $current_file_options[$file_id];
                        }
                    }
                    $current_file_options = $_current_file_options;
                }
            }
        }

        $max_file_size_str = $max_file_size >= 1024 ? round($max_file_size / 1024, 1) . 'MB' : $max_file_size . 'KB';
        $current_file_element = array(
            '#type' => 'grid',
            '#class' => 'sabai-file-current',
            '#empty_text' => isset($file_settings['#empty_text']) ? $file_settings['#empty_text'] : __('There are currently no files uploaded.', 'sabai'),
            '#column_attributes' => $allow_only_images
                ? array('thumbnail' => array('style' => 'width:25%;'))
                : array(),
            '#row_attributes' => $row_attr,
            '#description' => $data['#max_num_files']
                ? sprintf(__('Maximum number of files %d, maximum file size %s.', 'sabai'), $data['#max_num_files'], $max_file_size_str)
                : sprintf(__('Maximum file size %s.', 'sabai'), $max_file_size_str),
            '#disable_template_override' => true,
        );
        $current_file_element['#children'][0] = array(
            'check' => array(
                '#type' =>  'checkbox',
                '#title' => '',
                '#class' => 'sabai-form-check',
            )  + $form->defaultElementSettings(),
            'icon' => array(
                '#type' => 'item',
                '#title' => '',
            ),
            'name' => array(
                '#type' => 'textfield',
                '#title' => __('File Name', 'sabai'),
            ) + $form->defaultElementSettings(),
            'size' => array(
                '#type' => 'item',
                '#title' => __('Size', 'sabai'),
            ) + $form->defaultElementSettings(),
        );
        foreach ($current_file_options as $current_file_id => $current_file_option) {
            $current_file_element['#default_value'][$current_file_id] = array(
                'check' => true,
                'icon' => $current_file_option['icon'],
                'name' => $current_file_option['name'],
                'size' => $current_file_option['size'],
            );
        }
        $data['#children'][0]['current'] = $current_file_element + $form->defaultElementSettings();
        
        // Add upload field if not explicitly disabled
        if (!isset($data['#upload']) || $data['#upload'] !== false) {
            $data['#children'][0]['upload'] = $data['#_file_settings'] = $file_settings;

            // Register pre render callback if this is the first file_upload element
            if (empty(self::$_uploadFields)) {
                $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
            }
            self::$_uploadFields[$file_settings['#attributes']['id']] = array(
                'name' => $name,
                'route' => isset($file_settings['#upload_route']) ? $file_settings['#upload_route'] : '/sabai/file/upload',
                'multiple' => $data['#max_num_files'] !== 1,
                'uploader_settings' => array(
                    'allowed_extensions' => $allowed_extensions,
                    'max_file_size' => $max_file_size * 1024,
                    'image_only' => $allow_only_images,
                    'min_image_width' => isset($file_settings['#min_image_width']) ? $file_settings['#min_image_width'] : null,
                    'min_image_height' => isset($file_settings['#min_image_height']) ? $file_settings['#min_image_height'] : null,
                    'max_image_width' => isset($file_settings['#max_image_width']) ? $file_settings['#max_image_width'] : null,
                    'max_image_height' => isset($file_settings['#max_image_height']) ? $file_settings['#max_image_height'] : null,
                    'max_num_files' => $data['#max_num_files'],
                    'thumbnail' => !isset($file_settings['#thumbnail']) || false !== $file_settings['#thumbnail'],
                    'thumbnail_width' => !empty($file_settings['#thumbnail_width']) ? $file_settings['#thumbnail_width'] : null,
                    'thumbnail_height' => !empty($file_settings['#thumbnail_height']) ? $file_settings['#thumbnail_height'] : null,
                    'medium_image' => !isset($file_settings['#medium_image']) || false !== $file_settings['#medium_image'],
                    'medium_image_width' => !empty($file_settings['#medium_image_width']) ? $file_settings['#medium_image_width'] : null,
                    'large_image' => !isset($file_settings['#large_image']) || false !== $file_settings['#large_image'],
                    'large_image_width' => !empty($file_settings['#large_image_width']) ? $file_settings['#large_image_width'] : null,
                ),
                'current_file_ids' => array_keys($current_file_options),
                'sortable' => !empty($file_settings['#sortable']),
            );
        }
        
        $data['#children'][0]['progress'] = array(
            '#type' => 'markup',
            '#markup' => '<div class="sabai-progress" style="display:none;"><div class="sabai-progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>'
        ) + $form->defaultElementSettings();

        return $form->createFieldset($name, $data);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        $_values = $data['#_saved_file_ids'] = $data['#_new_file_ids'] = array();

        // File uploading enabled?
        if (!empty($data['#_file_settings'])) {
            if (!empty($value['current'])) $data['#_file_settings']['#required'] = false;
            // Validate uploaded file
            $this->_addon->getApplication()->Form_FieldImpl('file')->formFieldOnSubmitForm(
                $name . '[upload]',
                $value['upload'],
                $data['#_file_settings'],
                $form
            );
            if ($form->hasError($name . '[upload]')) {
                foreach ($form->getError($name . '[upload]') as $upload_error) {
                    $form->setError($upload_error, $name . '[current]');
                }
                return;
            }
            
            // Process custom validations if any
            foreach ($data['#_file_settings']['#element_validate'] as $callback) {
                try {
                    $this->_addon->getApplication()->CallUserFuncArray($callback, array($form, &$value['upload'], $data['#_file_settings']));
                } catch (Sabai_IException $e) {
                    $form->setError($e->getMessage(), $name . '[current]');
                }
            }

            // Save any newly uploaded file
            if (!empty($value['upload'])) {
                if (!$data['#multiple']) {
                    $value['upload'] = array($value['upload']);
                }
                foreach ($value['upload'] as $file_uploaded) {
                    $file = $this->_addon->getApplication()->File_Save($file_uploaded);
                    $data['#_saved_file_ids'][$file->id] = $file->title;
                }
            }
        }

        if ($data['#multiple'] || empty($data['#_saved_file_ids'])) {
            // Any current file selected?
            if (!empty($value['current'])) {
                $new_titles = array();
                foreach ($value['current'] as $file_id => $file_info) {
                    if (empty($file_info['check'][0])) {
                        continue;
                    }
                    $_values[$file_id] = $file_info['name'];
                    if (!isset($data['#_current_files'][$file_id])) {
                        // File uploaded via Ajax
                        $data['#_new_file_ids'][] = $file_id;
                        $new_titles[$file_id] = $file_info['name'];
                    } else {
                        if ($data['#_current_files'][$file_id]->title !== $file_info['name']) {
                            // Update file title
                            $new_titles[$file_id] = $file_info['name'];
                        }
                    }

                    if (!$data['#multiple']) break;
                }
                
                if (!empty($new_titles)) {
                    $new_title_files = $this->_addon->getModel('File')
                        ->id_in(array_keys($new_titles))
                        ->fetch();
                        
                    foreach ($new_title_files as $_file) {
                        $_file->title = $new_titles[$_file->id];
                    }
                    $this->_addon->getModel()->commit();
                }
            }
        }
        
        if (!empty($data['#_saved_file_ids'])) {
            foreach ($data['#_saved_file_ids'] as $file_id => $file_title) {
                $_values[$file_id] = $file_title;
            }
        }

        $value = array();
        if (!empty($_values)) {
            if (empty($data['#multiple'])) {
                $_values = array_slice($_values, 0, 1, true);
            }
            foreach ($_values as $file_id => $file_title) {
                $value[] = array('id' => $file_id, 'title' => $file_title);
            }
        }
        
        if ($data['#max_num_files'] && count($value) > $data['#max_num_files']) {
            $form->setError(sprintf(__('You may not upload more than %d files.', 'sabai'), $data['#max_num_files']), $name . '[current]');
        }
    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $model = $this->_addon->getModel();

        if ($form->isSubmitSuccess()) {
            // Form was successfully submitted

            // Remove association between the current upload token and files uploaded via ajax
            if (!empty($data['#_new_file_ids'])) {
                $model->getGateway('File')->updateByCriteria(
                    $model->createCriteria('File')->id_in($data['#_new_file_ids']),
                    array('file_token_id' => 0)
                );
            }

        } else {
            // Form submit failed, we need to remove files that have been uploaded during the upload process

            // Delete file data that have been created during upload
            if (!empty($data['#_saved_file_ids'])) {
                foreach ($model->File->fetchByIds(array_keys($data['#_saved_file_ids'])) as $file) {
                    $file->markRemoved();
                    $file->unlink();
                }
                $model->commit();
            }
        }

        // Remove the current upload token and files associated with the token (files uploaded via Ajax)
        $tokens = $model->Token->userId_is($this->_addon->getApplication()->getUser()->id)
            ->formBuildId_is($form->settings['#build_id'])
            ->formFieldName_is($name . '[upload]')
            ->fetch()
            ->with('Files');
        $new_file_ids = isset($data['#_new_file_ids']) ? $data['#_new_file_ids'] : array();
        foreach ($tokens as $token) {
            $token->markRemoved();
            foreach ($token->Files as $file) {
                if (!in_array($file->id, $new_file_ids)) {
                    $file->unlink();
                    $file->markRemoved();
                }
            }
        }

        $model->commit();
        
        $data['#_saved_file_ids'] = $data['#_new_file_ids'] = array();
    }

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
        $form->renderChildElements($name, $data);
    }

    public function preRenderCallback($form)
    {
        if (empty(self::$_uploadFields)) return;
        
        $application = $this->_addon->getApplication();
        $application->LoadJqueryUi(array('widget', 'sortable'));
        $application->LoadJs('jquery.iframe-transport.min.js', 'jquery-iframe-transform', 'jquery');
        $application->LoadJs('jquery.fileupload.min.js', 'jquery-fileupload', 'jquery-ui-widget');
        $application->LoadJs('sabai-file-upload.min.js', 'sabai-file-upload', array('jquery-fileupload', 'sabai'));

        $js = array();
        $model = $this->_addon->getModel();
        foreach (self::$_uploadFields as $upload_id => $upload) {
            $token = $model->create('Token');
            $token->hash = md5(uniqid(mt_rand(), true));
            $token->form_build_id = $form->settings['#build_id'];
            $token->form_field_name = $upload['name'] . '[upload]';
            $token->expires = time() + 1800;
            $token->user_id = $application->getUser()->id;
            $token->settings = $upload['uploader_settings'];
            $token->markNew();
            $js[] = sprintf('SABAI.File.upload({
    selector: "#%1$s",
    url: "%2$s",
    inputName: "%3$s",
    formData: {"sabai_file_form_build_id": "%4$s", "sabai_file_upload_token": "%5$s"},
    maxNumFiles: %6$d,
    formSelector: "#%7$s",
    sortable: %8$s,
    maxNumFileExceededError: "%9$s",
});',
                Sabai::h($upload_id),
                $application->Url($upload['route']),
                Sabai::h($upload['name']),
                Sabai::h($form->settings['#build_id']),
                $token->hash,
                $upload['uploader_settings']['max_num_files'] > 0 ? $upload['uploader_settings']['max_num_files'] : 0,
                $form->settings['#id'],
                $upload['sortable'] ? 'true' : 'false',
                sprintf(__('You may not upload more than %d files', 'sabai'), $upload['uploader_settings']['max_num_files'])
            );
        }

        if (empty($js)) return;
        
        try {
            $model->commit();
        } catch (Exception $e) {
            $application->LogError($e);

            return;
        }

        $form->addJs(sprintf('jQuery(document).ready(function($){
  %s
});',
            implode(PHP_EOL, $js)
        ));
    }
}