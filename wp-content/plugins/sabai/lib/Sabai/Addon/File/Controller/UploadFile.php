<?php
class Sabai_Addon_File_Controller_UploadFile extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        $context->addTemplate('file_uploadfile')
            ->setContainer(false); // workaround to disable the layout
        if (!$context->getRequest()->isPostMethod()) {
            $context->error = 'Invalid request method';
            return;
        }
        if (!$form_build_id = $context->getRequest()->asStr('sabai_file_form_build_id', false)) {
            $context->error = 'Bad request';
            return;
        }
        if (!$token_value = $context->getRequest()->asStr('sabai_file_upload_token', false)) {
            $context->error = 'Bad request';
            return;
        }
        $token = $this->getModel('Token')->userId_is($this->getUser()->id)
            ->formBuildId_is($form_build_id)
            ->hash_is($token_value)
            ->fetchOne();
        if (!$token || $token->expires < time()) {
            $context->error = 'Forbidden';
            return;
        }
        $this->_uploadFile($token, $context);
    }

    private function _uploadFile(Sabai_Addon_File_Model_Token $token, Sabai_Context $context)
    {
        if ($token->settings['max_num_files'] && $token->file_count > $token->settings['max_num_files'] * 2) {
            $context->error = __('You have already uploaded enough files!', 'sabai');
            return;
        }
        
        $tmp_dir = $this->getAddon('File')->getTmpDir();
        try {
            $this->ValidateDirectory($tmp_dir, true);
        } catch (Sabai_IException $e) {
            $context->error = $e->getMessage();
            return;
        }
        
        if (!empty($_FILES['sabai_file'])) {
            // Upload from IE
            if (!empty($_FILES['sabai_file']['error'])) {
                $context->error = sprintf(__('Failed uploading file. Error code: %d', 'sabai'), $_FILES['sabai_file']['error']);
                return;
            }
            $tmp_name = $tmp_dir . '/' . basename($_FILES['sabai_file']['tmp_name']);
            if (!move_uploaded_file($_FILES['sabai_file']['tmp_name'], $tmp_name)) {
                $context->error = __('Failed creating temporary file', 'sabai');
                return;
            }
            $size = $_FILES['sabai_file']['size'];
            $name = $_FILES['sabai_file']['name'];
        } else {         
            if (!$tmp_name = tempnam($tmp_dir, 'sabai_file_')) {
                $context->error = __('Failed creating temporary file', 'sabai');
                return;
            }
            if (!$tmp_file = fopen($tmp_name, 'w')) {
                $context->error = __('Failed opening temporary file with write permission', 'sabai');
                return;
            }
            if (!$input = fopen('php://input', 'r')) {
                $context->error = __('Failed reading php input stream', 'sabai');
                return;
            }
            $size = stream_copy_to_stream($input, $tmp_file);
            fclose($input);
            fclose($tmp_file);
            $name = $_GET['sabai_file'];
        }

        try {
            $type = $this->FileType($tmp_name, !empty($token->settings['image_only']));
        } catch (Sabai_IException $e) {
            $context->error = $e->getMessage();
            @unlink($tmp_name);
            return;
        }

        $file = array(
            'name' => $name,
            'type' => $type,
            'size' => $size,
            'tmp_name' => $tmp_name
        );

        try {
            $context->files = array($this->File_Save($this->Upload($file, array('upload' => false) + $token->settings), $token));
            // Increment uploaded file count for this token
            $token->file_count = $token->file_count + 1;
            $token->commit();
        } catch (Exception $e){
            $context->error = $e->getMessage();
        }
        @unlink($tmp_name);
    }
}