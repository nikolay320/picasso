<?php
class Sabai_Addon_DirectoryCSVImport_Controller_Admin_ImportCategories extends Sabai_Addon_Form_MultiStepController
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
            'slug' => __('Slug', 'sabai-directory'),
            'description' => __('Description', 'sabai-directory'),
            'parent' => __('Parent Category', 'sabai-directory'),
            'thumbnail' => __('Thumbnail', 'sabai-directory'),
            'map_marker' => __('Map Marker', 'sabai-directory'),
        );
        // Add custom fields
        $custom_fields = array();
        foreach ($this->Entity_Field($context->taxonomy_bundle->name) as $field) {
            if (!$field->isCustomField()
                || !in_array($field->getFieldType(), array('boolean', 'text', 'string', 'choice', 'number', 'user', 'markdown_text', 'date_timestamp'))
            ) {
                continue;
            }
            
            $fields[$field->getFieldName()] = sprintf(__('Custom field - %s', 'sabai-directory'), $field->getFieldLabel());
            $custom_fields[$field->getFieldName()] = $field->getFieldType();
        }
        $formStorage['custom_fields'] = $custom_fields;
        
        $form = array(
            '#header' => array(
                '<div>' . __('Set up the associations between your CSV file data and directory category fields.', 'sabai-directory') . '</div>',
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
        if (!in_array('title', $value)) {
            $form->setError(__('Please select a column for the "Title" field.', 'sabai-directory'));
        }
        $count = array_count_values($value);
        foreach ($count as $field_name => $_count) {
            if ($field_name !== '' && $_count > 1) {
                $form->setError(sprintf(
                    __('You may not associate multiple columns with the "%s" field.', 'sabai-directory'),
                    $form->settings['#fields'][$field_name]
                ));
            }
        }
    }
    
    protected function _getFormForStepDefaultSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_submitButtons[] = array('#value' => __('Import', 'sabai-directory'), '#btn_type' => 'primary');
        $form = array();
        
        $selected_fields = $formStorage['values']['settings']['fields'];
        if (in_array('thumbnail', $selected_fields)) {
            $form['thumbnail_archive'] = array(
                '#type' => 'file',
                '#title' => __('Thumbnail image archive file (.zip format)', 'sabai-directory'),
                '#upload_dir' => $this->getAddon('File')->getTmpDir(),
                '#allowed_extensions' => array('zip'),
                '#required' => true,
            );
        }
        if (in_array('map_marker', $selected_fields)) {
            $form['map_marker_archive'] = array(
                '#type' => 'file',
                '#title' => __('Map marker icon archive file (.zip format)', 'sabai-directory'),
                '#upload_dir' => $this->getAddon('File')->getTmpDir(),
                '#allowed_extensions' => array('zip'),
                '#required' => true,
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

        if (!empty($form)) {  
            $form['#header'] = array(
                '<div>' . __('Here you can set the default values and settings for the fields below.', 'sabai-directory') . '</div>',
            );   
        } else {
            $form['#header'] = array(
                '<div>' . __('Press the button below to import categories.', 'sabai-directory') . '</div>',
            );
        }
        
        return $form;
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
        
        // Init archive files
        $file_dir = array();
        foreach (array('thumbnail', 'map_marker') as $archive_type) {
            $archive_name = $archive_type . '_archive';
            if (isset($form->values[$archive_name]['saved_file_path'])
                && ($archive_file = $form->values[$archive_name]['saved_file_path'])
            ) {
                $this->getPlatform()->unzip($archive_file, dirname($archive_file));
                $file_dir[$archive_type] = dirname($archive_file) . '/' . basename($form->values[$archive_name]['name'], '.zip');
                @unlink($form->values[$archive_name]['saved_file_path']);
            }
        }
       
        $settings = $form->storage['values']['settings']['fields'];
        $category_ids = array();
        $rows_imported = $rows_updated = 0;
        $row_number = 1;
        $rows_failed = $files_failed = array();
        $parent_separator = @$form->values['slug']['path_separator'];
        $parent_separator = '/';
        $uploader = null;
        while (false !== $csv_row = fgetcsv($fp, 0, $delimiter, $enclosure)) {
            ++$row_number;
            if (empty($csv_row) || !is_array($csv_row)) {
                continue;
            }
            $row = array('parent_id' => null);
            // Load row data from CSV
            foreach ($csv_columns as $csv_row_key => $csv_column_name) {
                if (!isset($csv_row[$csv_row_key])) continue;
                
                if (isset($settings[$csv_row_key]) && strlen($settings[$csv_row_key])) {
                    $row[$settings[$csv_row_key]] = $csv_row[$csv_row_key];
                }
            }
            
            if (isset($row['parent']) && strlen($row['parent'])) {
                $parent_titles = isset($parent_separator) ? explode($parent_separator, $row['parent']) : array($row['parent']);
                $id_allowed = true;
                $row['parent_id'] = 0;
                foreach ($parent_titles as $parent_title) {
                    if ($id_allowed
                        && is_numeric($parent_title)
                        && ($parent_category = $this->getModel('Term', 'Taxonomy')->entityBundleName_is($context->taxonomy_bundle->name)->id_is($parent_title)->fetchOne())
                    ) {
                        $category_ids[$parent_category->name] = $row['parent_id'] = $parent_category->id;
                        break;
                    }
                    $id_allowed = false; // id can only be specified if single only
                    $parent_slug = $this->Slugify($parent_title);
                    if (!isset($category_ids[$parent_slug])) {
                        if ($parent_category = $this->getModel('Term', 'Taxonomy')->entityBundleName_is($context->taxonomy_bundle->name)->name_is($parent_slug)->fetchOne()) {
                            $parent_id = $parent_category->id;
                        } else {
                            // Create category
                            if (!$parent_category = $this->Entity_Save($context->taxonomy_bundle, array('taxonomy_term_title' => $parent_title, 'taxonomy_term_parent' => $row['parent_id']))) {
                                continue;
                            }
                            $parent_id = $parent_category->getId();
                        }
                        $category_ids[$parent_slug] = $parent_id;
                    }
                    $row['parent_id'] = $category_ids[$parent_slug];
                }
            }
            
            // Init directory listing values
            $values = array(
                'taxonomy_term_id' => (int)@$row['id'],
                'taxonomy_term_title' => $row['title'],
                'taxonomy_term_name' => $row['slug'],
                'taxonomy_body' => isset($row['description']) && strlen($row['description']) ? $row['description'] : null,
                'taxonomy_term_parent' => $row['parent_id'],
            );
            
            // Thumbnail and marker images
            foreach (array('thumbnail', 'map_marker') as $archive_type) {
                if (isset($file_dir[$archive_type]) && !empty($row[$archive_type])) {
                    $file_info = array(
                        'name' => $row[$archive_type],
                        'tmp_name' => $file_path = $file_dir[$archive_type] . '/' . $row[$archive_type],
                        'size' => @filesize($file_path),
                        'is_image' => true,
                    );
                    try {
                        $file = $this->File_Save($this->Upload($file_info, array('check_tmp_name' => false, 'image_only' => true)));
                        $values['directory_' . $archive_type] = array('id' => $file->id);
                    } catch (Exception $e) {
                        $files_failed[] = sprintf(__('File %s could not be imported. Error: %s', 'sabai-directory'), $row[$archive_type], $e->getMessage());
                    }
                }
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
                            $values[$custom_field_name] = array('text' => $row[$custom_field_name], 'filtered_text' => $row[$custom_field_name]);
                            break;
                        default:
                            $values[$custom_field_name] = $row[$custom_field_name];
                    }
                }
            }
            
            try {
                if (!empty($values['taxonomy_term_id']) && ($entity = $this->Entity_Entity('taxonomy', $values['taxonomy_term_id'], false))) {
                    $category = $this->Entity_Save($entity, $values);
                    ++$rows_updated;
                } else {
                    $category = $this->Entity_Save($context->taxonomy_bundle, $values);
                    ++$rows_imported;
                }
                $category_ids[$category->getSlug()] = $category->getId();
            } catch (Exception $e) {
                $rows_failed[$row_number] = $e->getMessage();
            }
        }
        $form->storage['rows_imported'] = $rows_imported;
        $form->storage['rows_updated'] = $rows_updated;
        $form->storage['rows_failed'] = $rows_failed;
        $form->storage['files_failed'] = $files_failed;
        $form->storage['file_dir'] = $file_dir;
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
        $failed_count = count($formStorage['rows_failed']);
        $error = array();
        if ($failed_count) {
            foreach ($formStorage['rows_failed'] as $row_num => $error_message) {
                $error[] = sprintf(__('CSV data on row number %d could not be imported: %s', 'sabai-directory'), $row_num, $error_message);
            }
        }
        if (!empty($formStorage['files_failed'])) {
            foreach ($formStorage['files_failed'] as $error_message) {
                $error[] = $error_message;
            }
        }
        if (!empty($formStorage['file_dir'])) {
            foreach ($formStorage['file_dir'] as $file_dir) {
                @unlink($file_dir);
            }
        }
        $context->error = $error;
        @unlink($formStorage['values']['upload']['file']['saved_file_path']);
    }
}