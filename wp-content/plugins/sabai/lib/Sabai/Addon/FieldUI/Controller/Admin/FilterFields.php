<?php
class Sabai_Addon_FieldUI_Controller_Admin_FilterFields extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        $bundle = $context->child_bundle ? $context->child_bundle : ($context->taxonomy_bundle ? $context->taxonomy_bundle : $context->bundle);
        $admin_fields_path = $bundle->getAdminPath() . '/fields/filter';
        $filters = array();
        $attr = array(
            'form_submit_path' => $this->Url($admin_fields_path . '/submit'),
            'form_edit_field_url' => $this->Url($admin_fields_path . '/edit'),
            'form_create_field_url' => $this->Url($admin_fields_path . '/create'),
            'filters' => array(),
            'fields' => array(),
            'filterable_fields' => array(),
        );
        $field_types = $this->FieldUI_FilterableFieldTypes($bundle);
        $creatable_field_types = $this->FieldUI_FilterableFieldTypes($bundle, true);
        $column_count = $row_count = 1;
        foreach ($bundle->Fields->with('Filters')->getArray() as $field) {
            if (!isset($field_types[$field->getFieldType()])) continue;
            
            $attr['fields'][$field->getFieldName()] = $field;
            if (isset($creatable_field_types[$field->getFieldType()])) {
                $attr['filterable_fields'][$field->getFieldName()] = array('field' => $field, 'field_type' => $field_types[$field->getFieldType()]['label']);
            }
            foreach ($field->Filters as $filter) {
                if (!isset($field_types[$field->getFieldType()]['filters'][$filter->type]) // filter type may have been removed
                    || isset($filters[$filter->name]) // remove duplicate, which seems to happen occasionally
                ) {
                    $filter->markRemoved()->commit();
                    continue;
                }

                $filters[$filter->name] = array(
                    'filter' => $filter,
                    'field' => $field->getFieldName(),
                );
                if ($filter->data['column'] > $column_count) {
                    $column_count = $filter->data['column'];
                }
                if ($filter->data['row'] > $row_count) {
                    $row_count = $filter->data['row'];
                }
            }
        }
        uasort($filters, array($this, '_sortFilters'));
        if ($column_count > 6) {
            $column_count = 6;
        } else {
            while (!in_array($column_count, array(1, 2, 3, 4, 6))) {
                ++$column_count;
            }
        }
        $attr['column_count'] = $context->getRequest()->asInt('column_count', $column_count, array(1, 2, 3, 4, 6));
        $attr['row_count'] = $context->getRequest()->asInt('row_count', $row_count, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10));
        foreach ($filters as $filter) {
            if ((!$column = $filter['filter']->data['column'])
                || $column > $attr['column_count']
            ) {
                $column = $attr['column_count'];
            }
            if ((!$row = $filter['filter']->data['row'])
                || $row > $attr['row_count']
            ) {
                $row = $attr['row_count'];
            }
            $attr['filters'][$row][$column][$filter['filter']->id] = $filter;
        }
        $context->addTemplate('fieldui_admin_filter_fields')->setAttributes($attr);
        
        $this->Action('fieldui_admin_fields');
    }
    
    private function _sortFilters($a, $b)
    {
        return $a['filter']->data['weight'] < $b['filter']->data['weight'] ? -1 : 1;
    }
}