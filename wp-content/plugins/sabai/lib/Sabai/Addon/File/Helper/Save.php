<?php
class Sabai_Addon_File_Helper_Save extends Sabai_Helper
{
    public function help(Sabai $application, array $fileData, Sabai_Addon_File_Model_Token $token = null)
    {
        if (false === $file_content = file_get_contents($fileData['tmp_name'])) {
            throw new Sabai_RuntimeException(sprintf(__('Failed fetching content of file %s.', 'sabai'), $fileData['name']));
        }
        if (false === $file_hash = md5_file($fileData['tmp_name'])) {
            throw new Sabai_RuntimeException(sprintf(__('Failed generating hash value for file %s.', 'sabai'), $fileData['name']));
        }

        // Create file metadata
        $file = $application->getModel(null, 'File')->create('File');
        $file->title = $fileData['name'];
        $file->extension = $fileData['file_ext'];
        $file->size = $fileData['size'];
        $file->type = $fileData['type'];
        $file->is_image = $fileData['is_image'];
        $file->width = $fileData['width'];
        $file->height = $fileData['height'];
        $file->user_id = $application->getUser()->id;
        $file->hash = $file_hash;
        $file->name = md5($file->hash . $file->user_id) . '.' . $file->extension;
        $file->content = $file_content;
        if (isset($token)) {
            $file->Token = $token;
        }
        $file->markNew();
        $file->commit();

        // Put file to storage
        $application->getAddon('File')->getStorage()->fileStoragePut($file->name, $file_content, array(
            'type' => $file->type,
            'is_image' => $file->is_image,
            'width' => $file->width,
            'height' => $file->height,
            'thumbnail' => isset($token) ? $token->settings['thumbnail'] : true,
            'thumbnail_width' => isset($token) ? $token->settings['thumbnail_width'] : null,
            'medium_image' => isset($token) ? $token->settings['medium_image'] : true,
            'medium_image_width' => isset($token) ? $token->settings['medium_image_width'] : null,
            'large_image' => isset($token) ? $token->settings['large_image'] : true,
            'large_image_width' => isset($token) ? $token->settings['large_image_width'] : null,
        ));

        return $file;
    }
}