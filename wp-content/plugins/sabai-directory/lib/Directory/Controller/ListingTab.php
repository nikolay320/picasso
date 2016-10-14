<?php
class Sabai_Addon_Directory_Controller_ListingTab extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        $fields = array();
        foreach ($this->Entity_Fields($context->entity, true) as $field) {
            $view = 'tab_' . $context->tab_name;
            if (!$field_output = $this->Entity_RenderField($context->entity, $field, $view)) continue;
            
            $fields[$field->getFieldName()] = array(
                'output' => $field_output,
                'type' => $field->getFieldType(),
                'title' => $field->getFieldTitle($view)
            );
        }
        $context->fields = $fields;
        $context->addTemplate('directory_listing_tab')
            ->addTemplate('directory_listing_tab_' . $context->tab_name);
    }
}