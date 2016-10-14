<?php
class Sabai_Addon_Directory_Controller_UploadPhotos extends Sabai_Addon_Form_Controller
{
    protected $_currentPhotos;
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = $this->Entity_Url($context->entity, '/photos');
        $this->_submitButtons['submit'] = array(
            '#value' => __('Submit', 'sabai-directory'),
            '#btn_type' => 'primary',
        );
        $form = array();
        // Add parent content static field if not on modal window or Ajax request
        if ($context->getContainer() !== '#sabai-modal' && !$context->getRequest()->isAjax()) {
            $form['listing'] = array(
                '#type' => 'item',
                '#title' => __('Listing', 'sabai-directory'),
                '#markup' => $this->Entity_Permalink($context->entity),
            );
        }
        
        // Fetch current photos
        $this->_currentPhotos = $this->_getCurrentPhotos($context);
        // Fetch photo file IDs
        $file_ids = $row_attr = array();
        foreach ($this->_currentPhotos as $photo) {
            $file_ids[] = $photo->file_image[0]['id'];
            if ($photo->isPending()) {
                $row_attr[$photo->file_image[0]['id']]['@row']['class'] = 'sabai-directory-pending';
            }
        }
        if ($field = $this->Entity_Field($context->entity, 'directory_photos')) {
            $widget_settings = $field->getFieldWidgetSettings();
        }
        $form['directory_photos'] = array(
            '#title' => __('Photos', 'sabai-directory'),
            '#type' => 'file_upload',
            '#max_file_size' => @$widget_settings['max_file_size'],
            '#multiple' => true,
            '#allow_only_images' => true,
            '#default_value' => empty($file_ids) ? null : $file_ids,
            '#max_num_files' => 0,
            '#row_attributes' => $row_attr,
        );
        
        $context->clearTabs();
        
        return $form;
    }
    
    protected function _getCurrentPhotos(Sabai_Context $context)
    {
        return $this->Entity_Query()
            ->propertyIs('post_entity_bundle_name', $this->getAddon()->getPhotoBundleName())
            ->propertyIsIn('post_status', array(Sabai_Addon_Content::POST_STATUS_PUBLISHED, Sabai_Addon_Content::POST_STATUS_PENDING))
            ->propertyIs('post_user_id', $this->getUser()->id)
            ->fieldIs('content_parent', $context->entity->getId())
            ->fieldIsNull('directory_photo', 'official')
            ->fieldIsNull('content_reference')
            ->sortByProperty('post_id', 'ASC')
            ->fetch();
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        // Update photos
        $current_photos = $submitted_photos = array();
        // Fetch current photos
        foreach ($this->_currentPhotos as $current_photo) {
            $current_photos[$current_photo->file_image[0]['id']] = $current_photo;
        }
        // Fetch submitted photos
        if (!empty($form->values['directory_photos'])) {
            foreach ($form->values['directory_photos'] as $file) {
                $submitted_photos[$file['id']] = $file['title'];
            }
        }
        // Remove deleted photos if any
        if ($deleted_photos = array_diff_key($current_photos, $submitted_photos)) {
            $this->getAddon('Entity')->deleteEntities(
                'content',
                $deleted_photos,
                array('content_skip_update_parent' => true) // we'll update parent listing later
            );
        }
        if (!empty($submitted_photos)) {
            // Add new photos if any
            if ($new_photos = array_diff_key($submitted_photos, $current_photos)) {
                foreach ($new_photos as $new_photo_id => $new_photo_title) {
                    $this->_application->Entity_Save(
                        $this->getAddon()->getPhotoBundleName(),
                        array(
                            'content_post_status' => $this->_getContentPostStatus($context),
                            'content_post_title' => $new_photo_title,
                            'file_image' => array('id' => $new_photo_id),
                            'content_parent' => $context->entity->getId()
                        ),
                        array('content_skip_update_parent' => true) // we'll update parent listing later
                    );
                }
            }
            // Update title of current photos if changed
            if ($current_photos = array_intersect_key($current_photos, $submitted_photos)) {
                foreach ($current_photos as $file_id => $current_photo) {
                    $photo_title = $submitted_photos[$file_id];
                    if ($photo_title != $current_photo->getTitle()) {
                        $this->_application->Entity_Save(
                            $current_photo,
                            array('content_post_title' => $photo_title),
                            array('content_skip_update_parent' => true) // we'll update parent listing later
                        );
                    }
                }
            }
        }

        if (!empty($deleted_photos) || !empty($new_photos)) {
            // Update parent listing
            $this->getAddon('Content')->updateParentPost($context->entity, false, true, true);
        }
        
        $context->setSuccess($this->Entity_Url($context->entity, '/photos', array('sort' => 'newest')));
    }
    
    protected function _getContentPostStatus(Sabai_Context $context)
    {
        return $this->HasPermission($this->getAddon()->getPhotoBundleName() . '_add2')
            ? Sabai_Addon_Content::POST_STATUS_PUBLISHED
            : Sabai_Addon_Content::POST_STATUS_PENDING;
    }
}
