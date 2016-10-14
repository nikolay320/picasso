<?php
abstract class Sabai_Addon_Entity_Controller_ViewEntity extends Sabai_Controller
{
    protected $_template;
    
    protected function _doExecute(Sabai_Context $context)
    {
        // Load entity
        $entity = $this->_getEntity($context);
        $this->Entity_LoadFields($entity);
        // Set context title and URL
        $title = $this->Filter('entity_title', $entity->getTitle(), array($entity));
        $url = $this->Entity_Url($entity);
        $info = $this->Filter('entity_info', array(array('url' => $url, 'title' => $title)), array($entity));
        $context->popInfo();
        $context->setTitle($title)->setUrl($url)->setInfo($info)->setHtmlHeadTitle($title);;
        // Set context HTML head title if a custom field named field_meta_title exists and its value is a valid string 
        if (isset($entity->field_meta_title)
            && ($meta_title = $entity->getSingleFieldValue('field_meta_title'))
        ) {
            $context->setHtmlHeadTitle((string)$meta_title);
        }
        
        // Set meta description if a custom field named field_meta_description exists and its value is a valid string, otherwise auto-generate description
        if (isset($entity->field_meta_description)
            && ($meta_description = $entity->getSingleFieldValue('field_meta_description'))
        ) {
            $context->setSummary($this->Summarize(is_array($meta_description) ? $meta_description['html'] : (string)$meta_description, 0, ''));
        } else {
            $context->setSummary($this->Summarize($entity->getContent(), 100));
        }
        // Render    
        $context->clearTabs()
            ->addTemplate(isset($this->_template) ? $this->_template : $entity->getBundleType() . '_single_full')
            ->setAttributes(current($this->Entity_Render($entity)));
        // Invoke other add-ons
        $this->Action('entity_view_entity', array($entity));
    }
    
    /**
     *@return Sabai_Addon_Entity_IEntity $entity 
     */
    abstract protected function _getEntity(Sabai_Context $context);
}