<?php
require_once dirname(__FILE__) . '/Photos.php';

class Sabai_Addon_Directory_Controller_ListingPhotos extends Sabai_Addon_Directory_Controller_Photos
{
    protected $_displayMode = 'full';
    
    protected function _doExecute(Sabai_Context $context)
    {
        parent::_doExecute($context);
        $context->link_to_listing = false;
        $context->no_comments = (bool)$this->getAddon()->getConfig('display', 'no_photo_comments');
    }
    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $bundle)
            ->fieldIs('content_parent', $context->entity->getId());
    }
    
    protected function _getLinks(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null, array $urlParams = array())
    {
        if ($this->getUser()->isAnonymous() || !$this->HasPermission($context->child_bundle->name . '_add')) {
            return array();
        }
        return array(
            1 => array(
                $this->LinkTo(
                    __('Upload Photos', 'sabai-directory'),
                    $this->Entity_Url($context->entity, '/photos/add'),
                    array('icon' => 'camera'),
                    array(
                        'title' => __('Add Photos', 'sabai-directory'),
                        'class' => 'sabai-btn sabai-btn-sm sabai-btn-primary sabai-directory-btn-photo'
                    )
                ),
            )
        );
    }
}