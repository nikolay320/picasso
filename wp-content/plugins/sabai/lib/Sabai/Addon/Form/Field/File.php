<?php
class Sabai_Addon_Form_Field_File extends Sabai_Addon_Form_Field_AbstractField
{    
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $element = $form->createHTMLQuickformElement('file', $name, $data['#label'], $data['#attributes']);
        if (!empty($data['#multiple'])) $element->setMultiple();

        return $element;
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Init value
        $value = empty($data['#multiple']) ? null : array();

        // Fetch value from $_FILES
        $files = $this->_addon->getSubmittedFiles($name);

        if (empty($files)) {
            if ($form->isFieldRequired($data)) {
                $form->setError(__('File must be uploaded.', 'sabai'), $name);
            }

            return;
        }

        // Init upload options
        $options = array(
            'allowed_extensions' => !empty($data['#allowed_extensions']) ? $data['#allowed_extensions'] : null,
            'max_file_size' => !empty($data['#max_file_size']) ? $data['#max_file_size'] * 1024 : null,
            'image_only' => isset($data['#allow_only_images']) ? $data['#allow_only_images'] : null,
            'max_image_width' => !empty($data['#max_image_width']) ? $data['#max_image_width'] : null,
            'max_image_height' => !empty($data['#max_image_height']) ? $data['#max_image_height'] : null,
            'min_image_width' => !empty($data['#min_image_width']) ? $data['#min_image_width'] : null,
            'min_image_height' => !empty($data['#min_image_height']) ? $data['#min_image_height'] : null,
            'upload_dir' => !empty($data['#upload_dir']) ? $data['#upload_dir'] : null,
            'upload_file_name_prefix' => !empty($data['#upload_file_name_prefix']) ? $data['#upload_file_name_prefix'] : null,
            'upload_file_name_max_length' => !empty($data['#upload_file_name_max_length']) ? $data['#upload_file_name_max_length'] : null,
            'upload_file_permission' => !empty($data['#upload_file_permission']) ? $data['#upload_file_permission'] : 0644,
            'hash_upload_file_name' => isset($data['#hash_upload_file_name']) ? $data['#hash_upload_file_name'] : true,
            'skip_mime_type_check' => isset($data['#skip_mime_type_check']) ? $data['#skip_mime_type_check'] : false,
        );

        if (!empty($data['#multiple'])) {
            // Get maximum number of upload files
            if (0 >= $max_upload_num = intval(@$data['#multiple_max'])) {
                $max_upload_num = 1;
            }

            // Iterate through files data until the max limit is reached
            foreach (array_keys($files['name']) as $i) {
                $_file = array(
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'size' => $files['size'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                );

                try {
                    $_file = $this->_addon->getApplication()->Upload($_file, $options);
                } catch (Sabai_RuntimeException $e) {
                    if ($e->getCode() !== UPLOAD_ERR_NO_FILE) {
                        throw $e;
                    }

                    // No file, so just skip its process
                    continue;
                }

                if (isset($_file['saved_file_path'])) {
                    // Save the file path of uploaded file so that the file can be removed upon cleanup process in case the form submit failed
                    $data['#_uploaded_files'][] = $_file['saved_file_path'];
                }

                $value[] = $_file;

                --$max_upload_num;
                if ($max_upload_num === 0) break;
            }

            if ($form->isFieldRequired($data) && empty($value)) {
                $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('File must be uploaded.', 'sabai'), $name);
            }

        } else {
            try {
                $file = $this->_addon->getApplication()->Upload($files, $options);
            } catch (Sabai_RuntimeException $e) {
                if ($e->getCode() !== UPLOAD_ERR_NO_FILE) {
                    throw $e;
                }

                // No file
                if ($form->isFieldRequired($data)) {
                    $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('File must be uploaded.', 'sabai'), $name);
                }

                return;
            }


            if (isset($file['saved_file_path'])) {
                // Save the file path of uploaded file so that the file can be removed upon cleanup process in case the form submit failed
                $data['#_uploaded_files'][] = $file['saved_file_path'];
            }

            $value = $file;
        }
    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if ($form->isSubmitted() // form submission did not fail
            || empty($data['#_uploaded_files']) // no new file upload
        ) return;

        // Form submission failed, so remove the files that have been uploaded in the process
        foreach ($data['#_uploaded_files'] as $file_path) @unlink($file_path);
    }

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }
}