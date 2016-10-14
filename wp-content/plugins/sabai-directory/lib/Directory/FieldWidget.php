<?php
class Sabai_Addon_Directory_FieldWidget implements Sabai_Addon_Field_IWidget
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Directory $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldWidgetGetInfo($key = null)
    {
        switch ($this->_name) {
            case 'directory_rating':
                $info = array(
                    'label' => __('Rating Stars', 'sabai-directory'),
                    'field_types' => array('directory_rating'),
                    'default_settings' => array('criterion' => array(), 'step' => '0.5'),
                    'requirable' => false,
                    'accept_multiple' => true,
                    'disable_edit_max_num_items' => true,
                );
                break;
            case 'directory_contact':
                $info = array(
                    'label' => __('Contact Info', 'sabai-directory'),
                    'field_types' => array('directory_contact'),
                    'default_settings' => array(
                        'require_phone' => false,
                        'require_mobile' => false,
                        'require_fax' => false,
                        'require_email' => false,
                        'require_website' => false,
                        'hide_phone_field' => false,
                        'hide_mobile_field' => false,
                        'hide_fax_field' => false,
                        'hide_email_field' => false,
                        'hide_website_field' => false,
                        'autopopulate_email' => false,
                        'autopopulate_website' => false,
                    ),
                    'requirable' => false,
                    'is_fieldset' => true,
                );
                break;
            case 'directory_social':
                $info = array(
                    'label' => __('Social Accounts', 'sabai-directory'),
                    'field_types' => array('directory_social'),
                    'default_settings' => array(
                        'require_twitter' => false,
                        'require_facebook' => false,
                        'require_googleplus' => false,
                    ),
                    'requirable' => false,
                    'is_fieldset' => true,
                    'labelable' => false,
                );
                break;
            case 'directory_claim':
                $info = array(
                    'label' => 'Listing Owner',
                    'field_types' => array('directory_claim'),
                    'admin_only' => true,
                );
                break;
            case 'directory_photos':
                $info = array(
                    'label' => __('Photos', 'sabai-directory'),
                    'field_types' => array('directory_photos'),
                    'default_settings' => array(
                        'max_file_size' => 2048,
                    ),
                    'accept_multiple' => true,
                );
                break;
        }
        return isset($key) ? @$info[$key] : $info;
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        switch ($this->_name) {
            case 'directory_rating':
                return array(
                    'criterion' => array(
                        '#type' => 'options',
                        '#title' => __('Rating Criteria', 'sabai-directory'),
                        '#multiple' => true,
                        '#default_value' => $settings['criterion'],
                        '#value_title' => __('slug', 'sabai-directory'),
                        '#value_regex' => '/^[a-z0-9][a-z0-9_]{0,48}[a-z0-9]$/',
                        '#value_regex_error_message' => __('Slugs must consist of lowercase alphanumeric characters and underscores.', 'sabai-directory'),
                        '#require_default' => true,
                        '#description' => __('Note that changing the slug will reset the ratings of the criterion in all reviews. Also the overall ratings in both listings and reviews will stay the same even when a criterion is added or removed until the reviews are updated.', 'sabai-directory'),
                    ),
                    'step' => array(
                        '#type' => 'radios',
                        '#title' => __('Rating Step', 'sabai-directory'),
                        '#default_value' => $settings['step'],
                        '#options' => array('0.5' => '<span class="sabai-rating sabai-rating-5"></span> 0.5', '1' => '<span class="sabai-rating sabai-rating-10"></span> 1.0'),
                        '#class' => 'sabai-form-inline',
                        '#title_no_escape' => true,
                    ),
                );
            case 'directory_contact':
                return array(
                    'hide_phone_field' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Disable phone number field', 'sabai-directory'),
                        '#default_value' => !empty($settings['hide_phone_field']),
                    ),
                    'hide_email_field' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Disable e-mail field', 'sabai-directory'),
                        '#default_value' => !empty($settings['hide_email_field']),
                    ),
                    'hide_mobile_field' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Disable mobile number field', 'sabai-directory'),
                        '#default_value' => !empty($settings['hide_mobile_field']),
                    ),
                    'hide_fax_field' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Disable fax number field', 'sabai-directory'),
                        '#default_value' => !empty($settings['hide_fax_field']),
                    ),
                    'hide_website_field' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Disable website field', 'sabai-directory'),
                        '#default_value' => !empty($settings['hide_website_field']),
                    ),
                    'require_phone' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require phone number', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_phone']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_phone_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                    'require_mobile' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require mobile number', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_mobile']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_mobile_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                    'require_fax' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require fax number', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_fax']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_fax_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                    'require_email' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require e-mail address', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_email']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_email_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                    'require_website' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require website URL', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_website']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_website_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                    'autopopulate_email' => array(
                        '#type' => 'checkbox',
                        '#title' => __("Auto-populate e-mail address field with the current user's e-mail address", 'sabai-directory'),
                        '#default_value' => !empty($settings['autopopulate_email']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_email_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                    'autopopulate_website' => array(
                        '#type' => 'checkbox',
                        '#title' => __("Auto-populate website URL field with the current user's website URL", 'sabai-directory'),
                        '#default_value' => !empty($settings['autopopulate_website']),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[hide_website_field][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                );
            case 'directory_social':
                return array(
                    'require_twitter' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require Twitter username', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_twitter']),
                    ),
                    'require_facebook' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require Facebook URL', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_facebook']),
                    ),
                    'require_googleplus' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Require Google+ URL', 'sabai-directory'),
                        '#default_value' => !empty($settings['require_googleplus']),
                    ),
                );
                
            case 'directory_photos':
                return array(
                    'max_file_size' => array(
                        '#type' => 'textfield',
                        '#title' => __('Maximum photo upload size', 'sabai-directory'),
                        '#description' => __('The maximum file size of uploaded files in kilobytes. Leave this field blank for no limit.', 'sabai-directory'),
                        '#size' => 7,
                        '#integer' => true,
                        '#field_suffix' => 'KB',
                        '#default_value' => $settings['max_file_size'],
                        '#weight' => 2,
                    ),
                );
                
            default:
                return array();
        }
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        switch ($this->_name) {
            case 'directory_rating':
                return array(
                    '#type' => 'voting_rateit',
                    '#rateit_min' => 0,
                    '#rateit_max' => 5,
                    '#step' => $settings['step'],
                    '#default_value' => $value,
                    '#criteria' => $this->_getValidCriteria($settings),
                );
            case 'directory_contact':
                $form = array(
                    '#type' => 'fieldset',
                );
                if (!$settings['hide_phone_field']) {
                    $form['phone'] = array(
                        '#type' => 'textfield',
                        '#default_value' => isset($value) ? $value['phone'] : null,
                        '#description' => '<i class="fa fa-phone fa-fw"></i> ' . __('Phone Number', 'sabai-directory'),
                        '#max_length' => 50,
                        '#required' => !empty($settings['require_phone']),
                        '#weight' => 1,
                    );
                }
                if (!$settings['hide_email_field']) {
                    $form['email'] = array(
                        '#type' => 'email',
                        '#description' => '<i class="fa fa-envelope fa-fw"></i> ' . __('E-mail', 'sabai-directory'),
                        '#default_value' => isset($value) ? $value['email'] : null,
                        '#max_length' => 100,
                        '#required' => !empty($settings['require_email']),
                        '#weight' => 15,
                        '#auto_populate' => empty($settings['autopopulate_email']) ? null : 'email',
                    );
                }
                if (!$settings['hide_website_field']) {
                    $form['website'] = array(
                        '#type' => 'url',
                        '#description' => '<i class="fa fa-globe fa-fw"></i> ' . __('Website', 'sabai-directory'),
                        '#default_value' => isset($value) ? $value['website'] : null,
                        '#required' => !empty($settings['require_website']),
                        '#weight' => 20,
                        '#auto_populate' => empty($settings['autopopulate_website']) ? null : 'url',
                    );
                }
                if (!$settings['hide_mobile_field']) {
                    $form['mobile'] = array(
                        '#type' => 'textfield',
                        '#description' => '<i class="fa fa-mobile fa-fw"></i> ' . __('Mobile Number', 'sabai-directory'),
                        '#default_value' => isset($value) ? $value['mobile'] : null,
                        '#max_length' => 50,
                        '#required' => !empty($settings['require_mobile']),
                        '#weight' => 5,
                    );
                }
                if (!$settings['hide_fax_field']) {
                    $form['fax'] = array(
                        '#type' => 'textfield',
                        '#description' => '<i class="fa fa-fax fa-fw"></i> ' . __('Fax Number', 'sabai-directory'),
                        '#default_value' => isset($value) ? $value['fax'] : null,
                        '#max_length' => 50,
                        '#required' => !empty($settings['require_fax']),
                        '#weight' => 10,
                    );
                }
                return $form;
                
            case 'directory_social':
                return array(
                    '#type' => 'fieldset',
                    'twitter' => array(
                        '#type' => 'textfield',
                        '#default_value' => isset($value) ? $value['twitter'] : null,
                        '#regex' => '/^@?(\w){1,15}$/',
                        '#regex_error_message' => __('You must enter a Twitter username.', 'sabai-directory'),
                        '#description' => '<i class="fa fa-twitter-square fa-fw"></i> ' . __('Twitter', 'sabai-directory'),
                        '#min_length' => 1,
                        '#max_length' => 16,
                        '#required' => !empty($settings['require_twitter']),
                        '#placeholder' => __('@username', 'sabai-directory'),
                    ),
                    'facebook' => array(
                        '#type' => 'url',
                        '#description' => '<i class="fa fa-facebook-square fa-fw"></i> ' . __('Facebook URL', 'sabai-directory'),
                        '#default_value' => isset($value) ? $value['facebook'] : null,
                        '#required' => !empty($settings['require_facebook']),
                    ),
                    'googleplus' => array(
                        '#type' => 'url',
                        '#description' => '<i class="fa fa-google-plus-square fa-fw"></i> ' . __('Google+ URL', 'sabai-directory'),
                        '#default_value' => isset($value) ? $value['googleplus'] : null,
                        '#required' => !empty($settings['require_googleplus']),
                    ),
                );
            case 'directory_claim':
                return array(
                    '#type' => 'fieldset',
                    'claimed_by' => array(
                        '#type' => 'user',
                        '#default_value' => isset($value['claimed_by']) ? array($value['claimed_by'] => $value['claimed_by']) : 0,
                        '#multiple' => false,
                    ),
                    'expires_at' => array(
                        '#title' => __('End Date', 'sabai-directory'),
                        '#type' => 'date_datepicker',
                        '#default_value' => !empty($value['expires_at']) ? $value['expires_at'] : null,
                        '#states' => array(
                            'invisible' => array(
                                sprintf('input[name="%s[claimed_by][select]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 0),
                            ),
                        ),
                    ),
                );
            case 'directory_photos':
                $application = $this->_addon->getApplication();                
                $form = array(
                    '#type' => 'file_upload',
                    '#multiple' => true,
                    '#allow_only_images' => true,
                    '#sortable' => true,
                    '#max_num_files' => $field->getFieldMaxNumItems(),
                    '#element_validate' => array(array(array($this, 'addDirectoryPhotosSubmitCallback'), array($bundle))),
                    '#max_file_size' => $settings['max_file_size'],
                    '#attributes' => array('accept' => 'image/*'),
                );

                if (isset($entity)) {
                    $query = $application->Entity_Query()
                        ->propertyIs('post_entity_bundle_name', $application->getAddon($bundle->addon)->getPhotoBundleName());
                    if ($entity->getBundleType() === 'directory_listing') {
                        // Fetch listing photos
                        $query->fieldIs('content_parent', $entity->getId())
                            ->sortByField('directory_photo', 'ASC', 'display_order');
                        if ($application->getUser()->isAdministrator()
                            || $application->Directory_IsListingOwner($entity)
                        ) {
                            $query->fieldIsNotNull('directory_photo', 'official');
                            $form['#official'] = true;
                        } else {
                            $query->fieldIs('directory_photo', 2, 'official');
                            $form['#official'] = false;
                        }
                        $form['#current_photos'] = $query->fetch();      
                    } else {
                        // Make sure listing for this review exists
                        if (!$application->Content_ParentPost($entity)) return; 
                        // Fetch review photos
                        $form['#current_photos'] = $query->fieldIs('content_reference', $entity->getId())
                            ->sortByProperty('post_id', 'ASC')
                            ->fetch();
                    }
                    if (!empty($form['#current_photos'])) {
                        $form['#default_value'] = array();
                       
                        foreach ($form['#current_photos'] as $photo) {
                            $file_id = $photo->file_image[0]['id'];
                            $form['#default_value'][] = $file_id;
                            if (!$photo->isPublished()) {
                                $form['#row_attributes'][$file_id]['@row']['class'] = 'sabai-active';
                            }
                        }
                        $form['#entity_set_default_value'] = false;
                    }
                }
                return $form;
        }
    }
    
    public function addDirectoryPhotosSubmitCallback(Sabai_Addon_Form_Form $form, &$value, $element, $bundle)
    {
        // Add submit callback for photos and make sure it is called after the default callback so that $form->settings['#entity'] is set
        $form->settings['#submit'][Sabai_Addon_Form::FORM_CALLBACK_WEIGHT_DEFAULT + 1] = array(array(array($this, 'directoryPhotosSubmitCallback'), array($bundle)));
    }
    
    public function directoryPhotosSubmitCallback($form, $bundle)
    {
        if (!isset($form->settings['#entity'])) return;
        
        $application = $this->_addon->getApplication();
        $photo_bundle_name = $application->getAddon($bundle->addon)->getPhotoBundleName();
        $guest_author = $form->settings['#entity']->getGuestAuthorInfo();
        switch ($bundle->type) {
            case 'directory_listing':
                $official = !empty($form->settings['directory_photos']['#official']) ? 1 : 2;
                if (!empty($form->settings['directory_photos']['#current_photos'])) {
                    // Fetch current photo files
                    $current_photos = $submitted_photos = $submitted_photos_order = array();
                    foreach ($form->settings['directory_photos']['#current_photos'] as $current_photo) {
                        $current_photos[$current_photo->file_image[0]['id']] = $current_photo;
                    }
                    
                    if (!empty($form->values['directory_photos'])) {
                        $display_order = 0;
                        // Fetch submitted photo files
                        foreach ($form->values['directory_photos'] as $file) {
                            $submitted_photos[$file['id']] = $file;
                            $submitted_photos_order[$file['id']] = ++$display_order;
                        }
                    }
                    
                    $deleted_photos = array_diff_key($current_photos, $submitted_photos);
                    $new_photos = array_diff_key($submitted_photos, $current_photos);
                    $new_current_photos = array_intersect_key($current_photos, $submitted_photos);
                    
                    // Remove deleted photos if any
                    if (!empty($deleted_photos)) {
                        $application->getAddon('Entity')->deleteEntities(
                            'content',
                            $deleted_photos,
                            array('content_skip_update_parent' => true) // we'll update parent listing later
                        );
                    }
                    // Update display order and title of current photos if changed
                    if (!empty($new_current_photos)) {
                        foreach ($new_current_photos as $file_id => $current_photo) {
                            $photo_title = $submitted_photos[$file_id]['title'];
                            $display_order = $submitted_photos_order[$file_id];
                            if ($display_order != $current_photo->directory_photo[0]['display_order']
                                || $photo_title != $current_photo->getTitle()
                                || $current_photo->directory_photo[0]['official'] != $official // mark the photo unofficial
                            ) {
                                $application->Entity_Save(
                                    $current_photo,
                                    array(
                                        'content_post_title' => $photo_title,
                                        'directory_photo' => array(
                                            'official' => $official,
                                            'display_order' => $display_order
                                        ),
                                    ),
                                    array('content_skip_update_parent' => true) // we'll update parent listing later
                                );
                            }
                        }
                    }
                    $update_parent_listing = !empty($deleted_photos) || !empty($new_photos);
                } else {
                    if (empty($form->values['directory_photos'])) return;
                    
                    $new_photos = $form->values['directory_photos'];
                    $update_parent_listing = true;
                }
                // Add photos
                foreach (array_values($new_photos) as $display_order => $file) {
                    if (isset($submitted_photos_order[$file['id']])) {
                        $display_order = $submitted_photos_order[$file['id']];
                    }
                    $application->Entity_Save(
                        $photo_bundle_name,
                        array(
                            'file_image' => $file,
                            'content_post_title' => $file['title'],
                            'content_post_status' => $form->settings['#entity']->getStatus(),
                            'content_parent' => $form->settings['#entity']->getId(),
                            'content_guest_author' => $guest_author,
                            'directory_photo' => array(
                                'official' => $official, // partially official
                                'display_order' => $display_order
                            ),
                        ),
                        array('content_skip_update_parent' => true) // we'll update parent listing later
                    );
                }
                if ($update_parent_listing) {
                    $application->getAddon('Content')->updateParentPost($form->settings['#entity'], false, true, true);
                }
                break;
            case 'directory_listing_review':
                if (!$listing = $application->Content_ParentPost($form->settings['#entity'])) return;
                
                if (!empty($form->settings['directory_photos']['#current_photos'])) {
                    // Fetch current photo files
                    $current_photos = $submitted_photos = array();
                    foreach ($form->settings['directory_photos']['#current_photos'] as $current_photo) {
                        $current_photos[$current_photo->file_image[0]['id']] = $current_photo;
                    }
                    if (!empty($form->values['directory_photos'])) {
                        // Fetch submitted photo files
                        foreach ($form->values['directory_photos'] as $file) {
                            $submitted_photos[$file['id']] = $file;
                        }
                    }
                    $deleted_photos = array_diff_key($current_photos, $submitted_photos);
                    $new_photos = array_diff_key($submitted_photos, $current_photos);
                    $new_current_photos = array_intersect_key($current_photos, $submitted_photos);
                    // Remove deleted photos if any
                    if (!empty($deleted_photos)) {
                        $application->getAddon('Entity')->deleteEntities(
                            'content',
                            $deleted_photos,
                            array('content_skip_update_parent' => true) // we'll update parent listing later
                        );
                    }
                    // Update title of current photos if changed
                    if (!empty($new_current_photos)) {
                        foreach ($new_current_photos as $file_id => $current_photo) {
                            $photo_title = $submitted_photos[$file_id]['title'];
                            if ($photo_title != $current_photo->getTitle()) {
                                $application->Entity_Save(
                                    $current_photo,
                                    array('content_post_title' => $photo_title),
                                    array('content_skip_update_parent' => true) // we'll update parent listing later
                                );
                            }
                        }
                    }
                    $update_parent_listing = !empty($deleted_photos) || !empty($new_photos);
                } else {
                    if (empty($form->values['directory_photos'])) return;
                    
                    $new_photos = $form->values['directory_photos'];
                    $update_parent_listing = true;
                }
                // Add new photos
                foreach ($new_photos as $file) {
                    $application->Entity_Save(
                        $photo_bundle_name,
                        array(
                            'file_image' => $file,
                            'content_post_title' => $file['title'],
                            'content_post_status' => $form->settings['#entity']->getStatus(),
                            'content_parent' => $listing->getId(),
                            'content_guest_author' => $guest_author,
                            'content_reference' => $form->settings['#entity']->getId(),
                        ),
                        array('content_skip_update_parent' => true) // we'll update parent listing later
                    );
                }
                if ($update_parent_listing) {
                    $application->getAddon('Content')->updateParentPost($listing, false, true, true);
                }
                break;
        }
    }
    
    protected function _getValidCriteria(array $settings)
    {
        if (!isset($settings['criterion']['options'])) {
            return array();
        }
        $criteria = $settings['criterion']['options'];
        $default = (array)@$settings['criterion']['default'];
        foreach (array_keys($criteria) as $option) {
            if (!in_array($option, $default)) {
                unset($criteria[$option]);
            }
        }
        return $criteria;
    }
    
    protected function _renderRatingMarkup()
    {
        $ret = array('<div>');
        for ($i = 0; $i < 5; $i++) {
            $ret[] = '<i class="fa fa-star"></i>';
        }
        $ret[] = '</div>';
        return implode('', $ret);
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        switch ($this->_name) {
            case 'directory_rating':
                $criteria = $this->_getValidCriteria($settings);
                if (empty($criteria)) {
                    return $this->_renderRatingMarkup();
                } else {
                    $markup = array('<table style="border:0; margin:0; padding:0;">');
                    foreach ($criteria as $label) {
                        $markup[] = '<tr><th style="border:0; padding:5px 5px 5px 0; text-align:left;">'. $label . '</th><td style="border:0; padding:5px 5px 5px 0; text-align:left;">'. $this->_renderRatingMarkup() .'</td></tr>';
                    }
                    $markup[] = '</table>';
                }
                return implode(PHP_EOL, $markup);
            case 'directory_contact':
                $required_label = '<span class="sabai-fieldui-widget-required">*</span>';
                return sprintf(
                    '<div%s>
    <div class="sabai-fieldui-widget-label">%s%s</div>
    <div><input type="textfield" disabled="disabled" style="width:100%%;" /></div>
</div>
<div%s>
    <div class="sabai-fieldui-widget-label">%s%s</div>
    <div><input type="textfield" disabled="disabled" style="width:100%%;" /></div>
</div>
<div%s>
    <div class="sabai-fieldui-widget-label">%s%s</div>
    <div><input type="textfield" disabled="disabled" style="width:100%%;" /></div>
</div>
<div%s>
    <div class="sabai-fieldui-widget-label">%s%s</div>
    <div><input type="textfield" disabled="disabled" style="width:100%%;" value="%s" /></div>
</div>
<div%s>
    <div class="sabai-fieldui-widget-label">%s%s</div>
    <div><input type="textfield" disabled="disabled" style="width:100%%;" value="%s" /></div>
</div>',
                    $settings['hide_phone_field'] ? ' style="display:none;"' : '',
                    __('Phone Number', 'sabai-directory'),
                    $settings['require_phone'] ? $required_label : '',
                    $settings['hide_mobile_field'] ? ' style="display:none;"' : '',
                    __('Mobile Number', 'sabai-directory'),
                    $settings['require_mobile'] ? $required_label : '',
                    $settings['hide_fax_field'] ? ' style="display:none;"' : '',
                    __('Fax Number', 'sabai-directory'),
                    $settings['require_fax'] ? $required_label : '',
                    $settings['hide_email_field'] ? ' style="display:none;"' : '',
                    __('E-mail', 'sabai-directory'),
                    $settings['require_email'] ? $required_label : '',
                    empty($settings['autopopulate_email']) ? '' : 'me@mydomain.com',
                    $settings['hide_website_field'] ? ' style="display:none;"' : '',
                    __('Website', 'sabai-directory'),
                    $settings['require_website'] ? $required_label : '',
                    empty($settings['autopopulate_website']) ? '' : 'http://www.mydomain.com/'
                );
            case 'directory_social':
                $required_label = '<span class="sabai-fieldui-widget-required">*</span>';
                return sprintf(
                    '<div>
    <div class="sabai-fieldui-widget-label"><i class="fa fa-twitter-square"></i> %s%s</div>
    <div><input type="textfield" disabled="disabled" style="width:100%%;" /></div>
</div>
<div>
    <div class="sabai-fieldui-widget-label"><i class="fa fa-facebook-square"></i> %s%s</div>
    <div><input type="textfield" disabled="disabled" style="width:100%%;" /></div>
</div>
<div>
    <div class="sabai-fieldui-widget-label"><i class="fa fa-google-plus-square"></i> %s%s</div>
    <div><input type="textfield" disabled="disabled" style="width:100%%;" /></div>
</div>',
                    __('Twitter', 'sabai-directory'),
                    $settings['require_twitter'] ? $required_label : '',
                    __('Facebook URL', 'sabai-directory'),
                    $settings['require_facebook'] ? $required_label : '',
                    __('Google+ URL', 'sabai-directory'),
                    $settings['require_googleplus'] ? $required_label : ''
                );
            case 'directory_photos':
                return '<input type="file" disabled="disabled" />';
        }
    }
    
    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $settings, array $parents = array())
    {

    }
}