<?php
class Sabai_Addon_Content_Helper_StatusOptions extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        $options = array(
            Sabai_Addon_Content::POST_STATUS_PUBLISHED => __('Published', 'sabai'),
            Sabai_Addon_Content::POST_STATUS_DRAFT => __('Draft', 'sabai'),
            Sabai_Addon_Content::POST_STATUS_PENDING => __('Pending', 'sabai'),
            Sabai_Addon_Content::POST_STATUS_TRASHED => __('Trashed', 'sabai'),
        );
        return $application->Filter('content_status_options', $options, array($bundle));
    }
}