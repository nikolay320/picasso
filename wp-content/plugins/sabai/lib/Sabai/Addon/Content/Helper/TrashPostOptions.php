<?php
class Sabai_Addon_Content_Helper_TrashPostOptions extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        return array(
            Sabai_Addon_Content::TRASH_TYPE_SPAM => __('Spam or offensive', 'sabai'),
            Sabai_Addon_Content::TRASH_TYPE_OFFTOPIC => __('Off topic', 'sabai'),
            Sabai_Addon_Content::TRASH_TYPE_OTHER => __('Other reason', 'sabai'),
        );
    }
}