<?php
class Sabai_Addon_File_Controller_File extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        $this->File_Display($context->file, $context->file['is_image'] ? $context->getRequest()->asStr('size', 'large', array('medium', 'large')) : null);

        exit;
    }
}