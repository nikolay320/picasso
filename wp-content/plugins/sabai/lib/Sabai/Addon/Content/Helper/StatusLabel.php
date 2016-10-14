<?php
class Sabai_Addon_Content_Helper_StatusLabel extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity)
    {
        switch ($entity->getStatus()) {
            case Sabai_Addon_Content::POST_STATUS_PUBLISHED:
                return sprintf('<span class="sabai-label sabai-label-success">%s</span> ', __('Published', 'sabai'));
            case Sabai_Addon_Content::POST_STATUS_DRAFT:
                return sprintf('<span class="sabai-label sabai-label-default">%s</span> ', __('Draft', 'sabai'));
            case Sabai_Addon_Content::POST_STATUS_PENDING:
                return sprintf('<span class="sabai-label sabai-label-warning">%s</span> ', __('Pending', 'sabai'));
            case Sabai_Addon_Content::POST_STATUS_TRASHED:
                switch ($entity->content_trashed[0]['type']) {
                    case Sabai_Addon_Content::TRASH_TYPE_OFFTOPIC:
                        $label = $title = __('Off topic', 'sabai');
                        break;
                    case Sabai_Addon_Content::TRASH_TYPE_OTHER:
                        $label = __('Other reason', 'sabai');
                        $title = $entity->content_trashed[0]['reason'];
                        break;
                    default:
                        $label = $title = __('Spam', 'sabai');
                        break;
                }
                return '<span class="sabai-label sabai-label-danger" title="'. Sabai::h($title) .'">' . $label .'</span> ';
        }
    }
}