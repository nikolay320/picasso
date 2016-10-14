<?php
class Sabai_Addon_FieldUI_Controller_Admin_SubmitFilterFields extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        // Check request token
        if (!$this->_checkToken($context, 'fieldui_admin_submit_filter_fields', true)) return;

        $bundle = $context->child_bundle ? $context->child_bundle : ($context->taxonomy_bundle ? $context->taxonomy_bundle : $context->bundle);
        $current_filters = array();
        $field_types = $this->FieldUI_FilterableFieldTypes($bundle);
        foreach ($bundle->Fields->with('Filters')->getArray() as $field) {
            if (!isset($field_types[$field->getFieldType()])) continue;

            foreach ($field->Filters as $filter) {
                $current_filters[$filter->id] = $filter;
            }
        }
        if ($filters = $context->getRequest()->asArray('filters')) {   
            $weight = 0;
            $column = $row = 1;
            foreach ($filters as $weight => $filter_id) {
                if ($filter_id === '__COLUMN__') {
                    ++$column;
                    continue;
                }
                if ($filter_id === '__ROW__') {
                    ++$row;
                    $column = 1;
                    continue;
                }
                if (!isset($current_filters[$filter_id])) continue;
                
                $filter = $current_filters[$filter_id];
                unset($current_filters[$filter_id]);
                
                $filter->data = array('weight' => ++$weight, 'column' => $column, 'row' => $row) + $filter->data;
            }
        }
        
        // Remove fields
        foreach ($current_filters as $current_filter) {
            $current_filter->markRemoved();
        }
        $this->getModel(null, 'Entity')->commit();

        // Send success
        $context->setSuccess($bundle->getAdminPath() . '/fields/filter');
    }
}