<?php
class Sabai_Addon_DirectoryCSVImport_Controller_Admin_Import extends Sabai_Addon_Form_MultiStepController
{    
    protected function _getSteps(Sabai_Context $context, array &$formStorage)
    {
        return array('upload', 'settings', 'default_settings');
    }
    
    protected function _getFormForStepUpload(Sabai_Context $context, array &$formStorage)
    {
        return array(
            'file' => array(
                '#type' => 'file',
                '#title' => __('CSV file', 'sabai-directory'),
                '#upload_dir' => $this->getAddon('File')->getTmpDir(),
                '#allowed_extensions' => array('csv'),
                '#required' => true,
                // The finfo_file function used by the uploader to check mime types for CSV files is buggy. We can skip it safely here since this is for admins only.
                '#skip_mime_type_check' => true,
            ),
            'delimiter' => array(
                '#type' => 'textfield',
                '#title' => __('CSV file field delimiter', 'sabai-directory'),
                '#size' => 5,
                '#description' => __('Enter the character to be used as the field delimiter of CSV file fields.', 'sabai-directory'),
                '#min_length' => 1,
                '#max_length' => 1,
                '#default_value' => ',',
                '#required' => true,
            ),
            'enclosure' => array(
                '#type' => 'textfield',
                '#title' => __('CSV file field enclosure', 'sabai-directory'),
                '#size' => 5,
                '#description' => __('Enter the character to be used as the field enclosure of CSV file fields.', 'sabai-directory'),
                '#min_length' => 1,
                '#max_length' => 1,
                '#default_value' => '"',
                '#required' => true,
            ),
            'convert_encoding' => array(
                '#type' => 'checkbox',
                '#title' => __('Convert encoding of CSV file data to UTF-8', 'sabai-directory'),
                '#default_value' => false,
            ),
            'convert_crlf' => array(
                '#type' => 'checkbox',
                '#title' => __('Convert line endings of CSV file to CR/LF', 'sabai-directory'),
                '#default_value' => false,
            ),
        );
    }
    
    protected function _getFormForStepSettings(Sabai_Context $context, array &$formStorage)
    {
        @setlocale(LC_ALL, $this->getPlatform()->getLocale());
        
        $csv_file = $formStorage['values']['upload']['file']['saved_file_path'];
        $convert_encoding = !empty($formStorage['values']['upload']['convert_encoding']);
        $convert_crlf = !empty($formStorage['values']['upload']['convert_crlf']);
        $fp = $this->CsvFile($csv_file, $convert_encoding, $convert_crlf);      
        $delimiter = $formStorage['values']['upload']['delimiter'];
        $enclosure = $formStorage['values']['upload']['enclosure'];
        if (false === $csv_columns = fgetcsv($fp, 0, $delimiter, $enclosure)) {
            throw new Sabai_RuntimeException(sprintf('Failed reading headers from CSV file %s.', $csv_file));
        }
        
        $fields = array(
            '' => __('Do not import', 'sabai-directory'),
            'id' => __('ID', 'sabai-directory'),
            'title' => __('Title', 'sabai-directory'),
            'description' => __('Description', 'sabai-directory'),
            'category' => __('Category', 'sabai-directory'),
            'street' => __('Street Address', 'sabai-directory'),
            'city' => __('City', 'sabai-directory'),
            'state' => __('State / Province / Region', 'sabai-directory'),
            'country' => __('Country', 'sabai-directory'),
            'zip' => __('Postal / Zip Code', 'sabai-directory'),
            'lat' => __('Latitude', 'sabai-directory'),
            'lng' => __('Longitude', 'sabai-directory'),
            'date' => __('Published Date', 'sabai-directory'),
            'author_id' => sprintf(__('%s (%s)', 'sabai-directory'), __('Author', 'sabai-directory'), __('User ID', '@@sabai_package_name')),
            'owner_id' => sprintf(__('%s (%s)', 'sabai-directory'), __('Owner', 'sabai-directory'), __('User ID', '@@sabai_package_name')),
            'owner_end_date' => sprintf(__('%s (%s)', 'sabai-directory'), __('Owner', 'sabai-directory'), __('End Date', '@@sabai_package_name')),
            'phone' => __('Phone Number', 'sabai-directory'),
            'mobile' => __('Mobile Number', 'sabai-directory'),
            'fax' => __('Fax Number', 'sabai-directory'),
            'email' => __('E-mail', 'sabai-directory'),
            'website' => __('Website', 'sabai-directory'),
            'twitter' => __('Twitter', 'sabai-directory'),
            'facebook' => __('Facebook URL', 'sabai-directory'),
            'googleplus' => __('Google+ URL', 'sabai-directory'),
            'photo' => __('Photos', 'sabai-directory'),
            'status' => __('Status', 'sabai-directory'),
        );

        // Add custom fields
        $custom_fields = array();
        foreach ($this->Entity_Field($context->bundle->name) as $field) {
            if (!$field->isCustomField()
                || !in_array($field->getFieldType(), array('boolean', 'text', 'string', 'choice', 'number', 'user', 'markdown_text', 'date_timestamp'))
            ) {
                continue;
            }
            
            $fields[$field->getFieldName()] = sprintf(__('Custom field - %s', 'sabai-directory'), $field->getFieldLabel());
            $custom_fields[$field->getFieldName()] = array('type' => $field->getFieldType(), 'label' => $field->getFieldLabel());
        }
        $formStorage['custom_fields'] = $custom_fields;
        
        $form = array(
            '#header' => array(
                '<div>' . __('Set up the associations between your CSV file data and directory listing fields. The "Category" field can be associated with more than one column if the field is configured to accept multiple values.', 'sabai-directory') . '</div>',
            ),
            '#fields' => $fields,
            'header' => array(
                '#type' => 'markup',
                '#markup' => '<table class="sabai-table"><thead><tr><th>' . __('CSV Column', 'sabai-directory') . '</th><th>' . __('Directory Listing Field', 'sabai-directory') . '</th></tr></thead><tbody>'
            ),
            'fields' => array(
                '#tree' => true,
                '#class' => 'sabai-form-group',
                '#element_validate' => array(array(array($this, 'validateFields'), array($context))),
            ),
        );
        
        foreach ($csv_columns as $csv_column_key => $csv_column_label) {
            $form['fields'][$csv_column_key] = array(
                '#template' => false,
                '#prefix' => '<tr><td>'. $csv_column_label .'</td><td>',
                '#suffix' => '</td></tr>',
                '#type' => 'select',
                '#options' => $fields,
            );
        }
        $form['footer'] = array(
            '#type' => 'markup',
            '#markup' => '</tbody></table>',
        );
        
        return $form;
    }
    
    public function validateFields($form, &$value, $element, $context)
    {
        $count = array_count_values($value);
        foreach ($count as $field_name => $_count) {
            if ($field_name !== '' && $_count > 1) {
                if ($field_name === 'category' && $this->_isMultipleCategoriesAllowed($context)) {
                    continue; // category field allows multiple values
                } elseif ($field_name === 'photo') {
                    continue;
                }
                $form->setError(sprintf(
                    __('You may not associate multiple columns with the "%s" field.', 'sabai-directory'),
                    $form->settings['#fields'][$field_name]
                ));
            }
        }
    }
    
    protected function _isMultipleCategoriesAllowed(Sabai_Context $context)
    {
        $category_field = $this->Entity_Field($context->bundle->name, 'directory_category');
        if (!$category_field) {
            return false; // this should never happen
        }
        return $category_field->getFieldMaxNumItems() > 1;
    }
    
    protected function _getFormForStepDefaultSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_submitButtons[] = array('#value' => __('Import', 'sabai-directory'), '#btn_type' => 'primary');
        $form = array();
        
        $selected_fields = $formStorage['values']['settings']['fields'];
        if (!in_array('category', $selected_fields)) {
            $form['category'] = array(
                '#tree' => false,
                '#title' => __('Category', 'sabai-directory'),
                '#collapsible' => false,
                '#class' => 'sabai-form-group',
                'category_type' => array(
                    '#type' => 'radios',
                    '#default_value' => 'none',
                    '#options' => array(
                        'none' => __('No category', 'sabai-directory'),
                        'existing' => __('Select from existing categories', 'sabai-directory'),
                        'new' => __('Create a new category', 'sabai-directory'),
                    ),
                ),
                'category_existing' => array(
                    '#type' => 'select',
                    '#options' => array(0 => __('Select Category', 'sabai-directory')) + $this->Taxonomy_Tree($this->getAddon($context->bundle->addon)->getCategoryBundleName()),
                    '#states' => array(
                        'visible' => array(
                            'input[name="category_type"]' => array('value' => 'existing'),
                        ),
                    ),
                    '#empty_value' => 0,
                    '#required' => array($this, 'isExistingCategoryRequired'),
                    '#size' => 5,
                ),
                'category_new' => array(
                    '#type' => 'textfield',
                    '#states' => array(
                        'visible' => array(
                            'input[name="category_type"]' => array('value' => 'new'),
                        ),
                    ),
                    '#required' => array($this, 'isNewCategoryRequired'),
                    '#attributes' => array('placeholder' => __('Enter a new category name', 'sabai-directory')),
                    '#size' => 30
                ),
            );
        } else {
            $form['category'] = array(
                '#tree' => false,
                '#title' => __('Category', 'sabai-directory'),
                '#collapsible' => false,
                '#class' => 'sabai-form-group',
                'category_create' => array(
                    '#type' => 'checkbox',
                    '#default_value' => true,
                    '#title' => __('Create non-existent categories', 'sabai-directory'),
                ),
                'category_separator' => array(
                    '#field_prefix' => __('Delimiter', 'sabai-directory'),
                    '#type' => 'textfield',
                    '#description' => sprintf(__('Enter the character to be used as delimiter if the %s column contains multiple values.', 'sabai-directory'), __('Category', 'sabai-directory')),
                    '#default_value' => ';',
                    '#size' => 5,
                    '#no_trim' => true,
                )
            );
        }
        if (!in_array('date', $selected_fields)) {
            $form['date'] = array(
                '#type' => 'date_datepicker',
                '#title' => __('Published Date', 'sabai-directory'),
                '#current_date_selected' => true,
            );
        }
        if (!in_array('author_id', $selected_fields)) {
            $form['author_id'] = array(
                '#type' => 'user',
                '#title' => __('Author', 'sabai-directory'),
                '#default_value' => $this->getUser()->id,
                '#multiple' => false,
                '#width' => '200px',
            );
        }
        if (!in_array('owner_id', $selected_fields)) {
            $form['owner_id'] = array(
                '#type' => 'user',
                '#title' => __('Owner', 'sabai-directory'),
                '#multiple' => false,
                '#width' => '200px',
            );
        }
        if (in_array('street', $selected_fields) || in_array('city', $selected_fields) || in_array('state', $selected_fields) || in_array('zip', $selected_fields) || in_array('country', $selected_fields)) {
            $address_format = in_array('street', $selected_fields) ? '{street}' : '';
            if (in_array('city', $selected_fields)) {
                $address_format .= ' {city}';
            }
            if (in_array('state', $selected_fields)) {
                $address_format .= ' {state}';
            }
            if (in_array('zip', $selected_fields)) {
                $address_format .= ' {zip}';
            }
            if (in_array('country', $selected_fields)) {
                $address_format .= ' {country}';
            }
            if (!in_array('lat', $selected_fields) || !in_array('lng', $selected_fields)) {
                $form['latlng'] = array(
                    '#tree' => false,
                    '#title' => __('Latitude / Longitude', 'sabai-directory'),
                    '#collapsible' => false,
                    '#class' => 'sabai-form-group',
                    'latlng_method' => array(
                        '#type' => 'radios',
                        '#default_value' => 'geocoding',
                        '#options' => array(
                            'geocoding' => __('Use Google geocoding service', 'sabai-directory'),
                            'manual' => __('Enter values manually', 'sabai-directory'),
                        ),
                        '#options_description' => array(
                            'geocoding' => __('Use the Google geocoding service to resolve the latitude/longitude coordinate from the address of each listing. This can be slow if the CSV data is large as well as there is a 2500 geocode request per day limit.', 'sabai-directory'),
                            'manual' => __('Manually enter the latitude/longitude coordinate that will be applied to all listings.', 'sabai-directory'),
                        ),
                    ),
                    'lat' => array(
                        '#type' => 'textfield',
                        '#size' => 15,
                        '#maxlength' => 9,
                        '#field_prefix' => __('Latitude:', 'sabai-directory'),
                        '#regex' => '/^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,5}/',
                        '#numeric' => true,
                        '#states' => array(
                            'visible' => array(
                                'input[name="latlng_method"]' => array('value' => 'manual'),
                            ),
                        ),
                        '#required' => array($this, 'isLatLngRequired'),
                    ),
                    'lng' => array(
                        '#type' => 'textfield',
                        '#size' => 15,
                        '#maxlength' => 10,
                        '#field_prefix' => __('Longitude:', 'sabai-directory'),
                        '#regex' => '/^-?([1]?[1-7][1-9]|[1]?[1-8][0]|[1-9]?[0-9])\.{1}\d{1,5}/',
                        '#numeric' => true,
                        '#states' => array(
                            'visible' => array(
                                'input[name="latlng_method"]' => array('value' => 'manual'),
                            ),
                        ),
                        '#required' => array($this, 'isLatLngRequired'),
                    ),
                );
            }
            $form['address_format'] = array(
                '#type' => 'textfield',
                '#title' => __('Address Format', 'sabai-directory'),
                '#default_value' => $address_format,
                '#required' => true,
                '#size' => 30,
            );
        }
        if (in_array('photo', $selected_fields)) {
            $form['photo'] = array(
                '#tree' => false,
                '#title' => __('Photos', 'sabai-directory'),
                '#collapsible' => false,
                '#class' => 'sabai-form-group',
                '#required' => true,
                'photo_separator' => array(
                    '#field_prefix' => __('Delimiter', 'sabai-directory'),
                    '#type' => 'textfield',
                    '#description' => sprintf(__('Enter the character to be used as delimiter if the %s column contains multiple values.', 'sabai-directory'), __('Photos', 'sabai-directory')),
                    '#default_value' => ';',
                    '#size' => 5,
                    '#no_trim' => true,
                ),
                'photo_archive' => array(
                    '#type' => 'file',
                    '#field_prefix' => __('Photo archive file (.zip format)', 'sabai-directory'),
                    '#upload_dir' => $this->getAddon('File')->getTmpDir(),
                    '#allowed_extensions' => array('zip'),
                    '#required' => true,
                ),
            );
        }
        
        if (!empty($formStorage['custom_fields'])) {
            foreach ($formStorage['custom_fields'] as $custom_field_name => $custom_field) {
                if ($custom_field['type'] === 'choice' && in_array($custom_field_name, $selected_fields)) {
                    $form[$custom_field_name] = array(
                        '#tree' => false,
                        '#title' => sprintf(__('Custom field - %s', 'sabai-directory'), $custom_field['label']),
                        '#collapsible' => false,
                        '#class' => 'sabai-form-group',
                        '#required' => true,
                        $custom_field_name . '_separator' => array(
                            '#field_prefix' => __('Delimiter', 'sabai-directory'),
                            '#type' => 'textfield',
                            '#description' => sprintf(__('Enter the character to be used as delimiter if the %s column contains multiple values.', 'sabai-directory'), $custom_field['label']),
                            '#default_value' => ';',
                            '#size' => 5,
                            '#no_trim' => true,
                        ),
                    );
                }
            }
        }
        
        // Skip this step if no fields to configure
        if (empty($form)) {
            return $this->_skipStepAndGetForm($context, $formStorage);
        }
            
        $form['#header'] = array(
            '<div>' . __('Here you can set the default values and settings for the fields below.', 'sabai-directory') . '</div>',
        );
        
        return $form;
    }
    
    public function isExistingCategoryRequired($form)
    {
        return $form->values['category_type'] === 'existing';
    }
        
    public function isNewCategoryRequired($form)
    {
        return $form->values['category_type'] === 'new';
    }
    
    public function isLatLngRequired($form)
    {
        return $form->values['latlng_method'] === 'manual';
    }
    
    protected function _submitFormForStepDefaultSettings(Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        @set_time_limit(0);
        @setlocale(LC_ALL, $this->getPlatform()->getLocale());
        
        $csv_file = $form->storage['values']['upload']['file']['saved_file_path'];
        $delimiter = $form->storage['values']['upload']['delimiter'];
        $enclosure = $form->storage['values']['upload']['enclosure'];     
        $fp = $this->CsvFile($csv_file);      
        if (false === $csv_columns = fgetcsv($fp, 0, $delimiter, $enclosure)) {
            @unlink($csv_file);
            throw new Sabai_RuntimeException(sprintf('Failed reading headers from CSV file %s.', $csv_file));
        }
        
        $photo_dir = null;
        if ($photo_archive_file = @$form->values['photo_archive']['saved_file_path']) {
            $this->getPlatform()->unzip($photo_archive_file, dirname($photo_archive_file));
            $photo_dir = dirname($photo_archive_file) . '/' . basename($form->values['photo_archive']['name'], '.zip');
            $photo_bundle_name = $this->Entity_Addon($context->bundle->name)->getPhotoBundleName();
            @unlink($form->values['photo_archive']['saved_file_path']);
        }
        
        $settings = $form->storage['values']['settings']['fields'];
        $geocode = !empty($form->values['latlng_method']) && $form->values['latlng_method'] === 'geocoding';
        
        $category = null;
        $category_bundle_name = $this->getAddon($context->bundle->addon)->getCategoryBundleName();
        $category_ids = array();
        if (!in_array('category', $settings)) {
            switch ($form->values['category_type']) {
                case 'new':
                    $category_entity = $this->Entity_Save($category_bundle_name, array('taxonomy_term_title' => $form->values['category_new']));
                    $category = array($category_entity->getId());
                    break;
                case 'existing':
                    $category = array($form->values['category_existing']);
                    break;
            }
        }
        
        $defaults = array(
            'category' => $category,
            'date' => !empty($form->values['date']) ? $form->values['date'] : time(),
            'author_id' => !empty($form->values['author_id']) ? $form->values['author_id'] : null,
            'owner_id' => !empty($form->values['owner_id']) ? $form->values['owner_id'] : null,
            'lat' => !$geocode && !empty($form->values['lat']) ? $form->values['lat'] : null,
            'lng' => !$geocode && !empty($form->values['lng']) ? $form->values['lng'] : null,
        );
        
        if (in_array('country', $settings)) {
            $countries = $this->Countries();
        }
        
        if (in_array('status', $settings)) {
            $statuses = array(
                'published' => Sabai_Addon_Content::POST_STATUS_PUBLISHED,
                'pending' => Sabai_Addon_Content::POST_STATUS_PENDING,
                'draft' => Sabai_Addon_Content::POST_STATUS_DRAFT,
            );
        }
        
        $rows_imported = $rows_geocoded = $rows_updated = 0;
        $row_number = 1;
        $rows_failed = $rows_geocoding_failed = $photos_failed = $photos_imported = array();
        while (false !== $csv_row = fgetcsv($fp, 0, $delimiter, $enclosure)) {
            ++$row_number;
            if (empty($csv_row) || !is_array($csv_row)) {
                continue;
            }
            $row = array('category' => array(), 'photo' => array());
            // Load row data from CSV
            foreach ($csv_columns as $csv_row_key => $csv_column_name) {
                if (!isset($csv_row[$csv_row_key])) continue;
                
                if (isset($settings[$csv_row_key]) && strlen($settings[$csv_row_key])) {
                    if (in_array($settings[$csv_row_key], array('category', 'photo'))) {
                        $separator = @$form->values[$settings[$csv_row_key] . '_separator'];
                        if ($separator) {
                            foreach (explode($separator, $csv_row[$csv_row_key]) as $_csv_row_value) {
                                $row[$settings[$csv_row_key]][] = trim($_csv_row_value);
                            }
                        } else {
                            $row[$settings[$csv_row_key]][] = $csv_row[$csv_row_key];
                        }
                    } else {
                        $row[$settings[$csv_row_key]] = $csv_row[$csv_row_key];
                    }
                }
            }
            // Check if country code
            if (isset($row['country'])) {
                if (strlen($row['country']) === 2) {
                    $row['country'] = strtoupper($row['country']);
                }
            }
            // Convert category names to IDs
            if (!empty($row['category'])) {
                // Check if any term slugs were specified instead of IDs
                $category_slugs = $category_labels = array();
                foreach ($row['category'] as $k => $category_name) {
                    if (!is_numeric($category_name)) {
                        $category_slug = $this->Slugify($category_name);
                        if (isset($category_ids[$category_slug])) {
                            // category already created
                            if (false !== $category_ids[$category_slug]) {
                                $row['category'][$k] = $category_ids[$category_slug];
                            }
                        } else {
                            $category_slugs[$category_slug] = $category_slug;
                            $category_labels[$category_slug] = $category_name;
                            unset($row['category'][$k]);
                        }
                    }
                }
                if (!empty($category_slugs)) {
                    foreach ($this->getModel('Term', 'Taxonomy')->entityBundleName_is($category_bundle_name)->name_in($category_slugs)->fetch() as $category) {
                        // category exists
                        $row['category'][] = $category->id;
                        $category_ids[$category->name] = $category->id;
                        unset($category_slugs[$category->name]); // found
                    }
                    // Categories not found. Create them and Cache ID to prevent from querying again
                    foreach ($category_slugs as $category_slug) {
                        if (!empty($form->values['category_create'])) {
                            // Create category
                            $category_entity = $this->Entity_Save($category_bundle_name, array('taxonomy_term_title' => $category_labels[$category_slug]));
                            $category_ids[$category_slug] = $row['category'][] = $category_entity->getId();
                        } else {
                            $category_ids[$category_slug] = false; // invalid category
                        }
                    }
                }
            }

            // Append default setting values
            $row += $defaults;
            
            $address = null;
            if (@$form->values['address_format']) {
                // Format address
                if (isset($row['country'])) {
                    $country = isset($countries[$row['country']]) ? $countries[$row['country']] : $row['country'];
                } else {
                    $country = '';
                }
                $address = str_replace(
                    array('{street}', '{city}', '{state}', '{zip}', '{country}'),
                    array($row['street'], (string)@$row['city'], (string)@$row['state'], (string)@$row['zip'], $country),
                    $form->values['address_format']
                );
            }
            
            // Init directory listing values
            $values = array(
                'content_post_id' => (int)@$row['id'],
                'content_post_title' => (string)@$row['title'],
                'content_post_status' => empty($row['status']) || !isset($statuses[$row['status']]) ? null : $statuses[$row['status']],
                'content_body' => isset($row['description']) && strlen($row['description']) ? $row['description'] : null,
                'directory_location' => array(
                    'address' => $address,
                    'street' => (string)@$row['street'],
                    'city' => (string)@$row['city'],
                    'state' => (string)@$row['state'],
                    'zip' => (string)@$row['zip'],
                    'country' => isset($row['country']) && isset($countries[$row['country']]) ? $countries[$row['country']] : '',
                    'lat' => (string)@$row['lat'],
                    'lng' => (string)@$row['lng'],
                ),
                'content_post_published' => !empty($row['date']) && is_numeric($row['date']) ? (int)$row['date'] : strtotime($row['date']),
                'content_post_user_id' => !empty($row['author_id']) ? $row['author_id'] : $defaults['author_id'],
                'directory_claim' => array(
                    'claimed_by' => !empty($row['owner_id']) ? $row['owner_id'] : $defaults['owner_id'],
                    'claimed_at' => time(),
                    'expires_at' => empty($row['owner_end_date']) || (!$time = strtotime($row['owner_end_date'])) ? null : $time,
                ),
                'directory_category' => !empty($row['category']) ? array_values($row['category']) : $defaults['category'],
                'directory_contact' => array(
                    'phone' => isset($row['phone']) && strlen($row['phone']) ? $row['phone'] : null,
                    'mobile' => isset($row['mobile']) && strlen($row['mobile']) ? $row['mobile'] : null,
                    'fax' => isset($row['fax']) && strlen($row['fax']) ? $row['fax'] : null,
                    'email' => isset($row['email']) && strlen($row['email']) ? $row['email'] : null,
                    'website' => isset($row['website']) && strlen($row['website'])
                        ? (strpos($row['website'], 'http') === 0 ? $row['website'] : 'http://' . $row['website'])
                        : null,
                ),
                'directory_social' => array(
                    'twitter' => isset($row['twitter']) && strlen($row['twitter']) ? str_replace('http://twitter.com/', '', $row['twitter']) : null,
                    'facebook' => isset($row['facebook']) && strlen($row['facebook']) ? $row['facebook'] : null,
                    'googleplus' => isset($row['googleplus']) && strlen($row['googleplus']) ? $row['googleplus'] : null,
                ),
            );
            
            if ($photo_dir && !empty($row['photo'])) {
                foreach ($row['photo'] as $key => $photo) {
                    if (!file_exists($photo_file_path = $photo_dir . '/' . $photo)) {
                        $photos_failed[] = sprintf(__('Photo file %s could not be imported. Error: %s', 'sabai-directory'), $photo, 'Non existent file ' . $photo_file_path);
                        unset($row['photo'][$key]);
                    }
                }
                $values['content_children_count'] = array('value' => count($row['photo']), 'child_bundle_name' => $photo_bundle_name);
            }
            
            // Process custom fields
            if (!empty($form->storage['custom_fields'])) {
                foreach ($form->storage['custom_fields'] as $custom_field_name => $custom_field) {
                    if (!isset($row[$custom_field_name]) || !strlen($row[$custom_field_name])) {
                        continue;
                    }
                    switch ($custom_field['type']) {
                        case 'choice':
                            if (!$separator = @$form->values[$custom_field_name . '_separator']) {
                                $separator = ',';
                            }
                            $values[$custom_field_name] = explode($separator, $row[$custom_field_name]);
                            break;
                        case 'markdown_text':
                            $values[$custom_field_name] = $row[$custom_field_name];
                            break;
                        default:
                            $values[$custom_field_name] = $row[$custom_field_name];
                    }
                }
            }
            
            if (empty($row['lat']) || empty($row['lng'])) {
                if ($geocode) {
                    try {
                        $geocode_addr = implode(' ', array($row['street'], (string)@$row['city'], (string)@$row['state'], (string)@$row['zip'], isset($row['country']) ? $countries[$row['country']] : ''));
                        $geocode_result = $this->GoogleMaps_GoogleGeocode($geocode_addr);
                        //$values['directory_location']['address'] = $geocode_result['address'];
                        $values['directory_location']['city'] = $geocode_result['city'];
                        $values['directory_location']['state'] = $geocode_result['state'];
                        $values['directory_location']['zip'] = $geocode_result['zip'];
                        $values['directory_location']['country'] = $geocode_result['country'];
                        $values['directory_location']['lat'] = $geocode_result['lat'];
                        $values['directory_location']['lng'] = $geocode_result['lng'];
                        ++$rows_geocoded;
                        if ($rows_geocoded % 10 === 0) {
                            sleep(1); // this is to prevent rate limit of 10 requests per second
                        }
                    } catch (Sabai_Addon_Google_GeocodeException $e) {
                        $rows_geocoding_failed[$row_number] = array($geocode_addr, $e->getMessage());
                    } catch (Exception $e) {
                        $rows_failed[$row_number] = $e->getMessage();
                        continue;
                    }
                }
            } else {
                $values['directory_location']['lat'] = $row['lat'];
                $values['directory_location']['lng'] = $row['lng'];
            }
            try {
                if (!empty($values['content_post_id']) && ($entity = $this->Entity_Entity('content', $values['content_post_id'], false))) {
                    $listing = $this->Entity_Save($entity, $values);
                    ++$rows_updated;
                } else {
                    $listing = $this->Entity_Save($context->bundle, $values);
                    ++$rows_imported;
                }
            } catch (Exception $e) {
                $rows_failed[$row_number] = $e->getMessage();
                continue;
            }    
            if ($photo_dir && !empty($row['photo'])) {
                $photos[$listing->getId()] = $row['photo'];
            }
        }
        $form->storage['rows_imported'] = $rows_imported;
        $form->storage['rows_updated'] = $rows_updated;
        $form->storage['rows_failed'] = $rows_failed;
        $form->storage['rows_geocoding_failed'] = $rows_geocoding_failed;
        
        // Add photos if any
        if ($photo_dir && !empty($photos)) {
            foreach ($photos as $listing_id => $_photos) {
                foreach ($_photos as $key => $photo) {
                    if ($photo && !isset($photos_imported[$photo])) {                        
                        try {
                            $file = array(
                                'name' => $photo,
                                'tmp_name' => $photo_file_path = $photo_dir . '/' . $photo,
                                'size' => @filesize($photo_file_path),
                                'is_image' => true,
                            );
                            $file = $this->File_Save($this->Upload($file, array('check_tmp_name' => false, 'image_only' => true)));
                            $photos_imported[$photo] = array('id' => $file->id, 'title' => $file->title);
                        } catch (Exception $e) {
                            $photos_failed[] = sprintf(__('File %s could not be imported. Error: %s', 'sabai-directory'), $photo, $e->getMessage());
                            continue;
                        }
                    }

                    try {
                        $this->Entity_Save(
                            $photo_bundle_name,
                            array(
                                'file_image' => array('id' => $photos_imported[$photo]['id']),
                                'content_post_title' => $photos_imported[$photo]['title'],
                                'content_parent' => $listing_id,
                                'directory_photo' => array(
                                    'official' => 1, // partially official
                                    'display_order' => $key
                                ),
                            ),
                            array('content_skip_update_parent' => true)
                        );
                    } catch (Exception $e) {
                        $photos_failed[] = sprintf(__('Photo file %s could not be imported. Error: %s', 'sabai-directory'), $photo, $e->getMessage());
                    }
                }
            }
            $form->storage['photos_failed'] = $photos_failed;
            $form->storage['photo_dir'] = $photo_dir;
        }
    }

    protected function _complete(Sabai_Context $context, array $formStorage)
    {
        $context->addTemplate('form_results');
        $success = array();
        $success[] = sprintf(_n('%d row imported successfullly.', '%d rows imported successfullly.', $formStorage['rows_imported'], 'sabai-directory'), $formStorage['rows_imported']);
        if (!empty($formStorage['rows_updated'])) {
            $success[] = sprintf(_n('%d row updated successfullly.', '%d rows updated successfullly.', $formStorage['rows_updated'], 'sabai-directory'), $formStorage['rows_updated']);
        }
        $context->success = $success;
        $error = array();
        if (!empty($formStorage['rows_failed'])) {
            foreach ($formStorage['rows_failed'] as $row_num => $error_message) {
                $error[] = sprintf(__('CSV data on row number %d could not be imported: %s', 'sabai-directory'), $row_num, $error_message);
            }
        }
        if (!empty($formStorage['rows_geocoding_failed'])) {
            foreach ($formStorage['rows_geocoding_failed'] as $row_num => $error_data) {
                $error[] = sprintf(__('Address (%s) for CSV data on row number %d could not be geocoded: %s', 'sabai-directory'), isset($error_data[0]) ? $error_data[0] : 'N/A', $row_num, $error_data[1]);
            }
        }
        if (!empty($formStorage['photos_failed'])) {
            foreach ($formStorage['photos_failed'] as $error_message) {
                $error[] = $error_message;
            }
        }
        $context->error = $error;
        @unlink($formStorage['values']['upload']['file']['saved_file_path']);
        @unlink($formStorage['photo_dir']);
    }
}
