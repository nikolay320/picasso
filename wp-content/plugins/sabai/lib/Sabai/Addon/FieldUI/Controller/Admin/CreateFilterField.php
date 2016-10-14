<?php
class Sabai_Addon_FieldUI_Controller_Admin_CreateFilterField extends Sabai_Addon_Form_Controller
{
    private $_field;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $bundle = $context->child_bundle ? $context->child_bundle : ($context->taxonomy_bundle ? $context->taxonomy_bundle : $context->bundle);
        $this->_cancelWeight = -99;
        $this->_ajaxOnContent = 'function(response, target, trigger){target.focusFirstInput();}';
        $this->_submitButtons = array(array('#value' => __('Add Field', 'sabai'), '#btn_type' => 'primary'));

        // Define form
        $form = array();
        $form['#action'] = $this->Url($context->getRoute());
        $form['#token_reuseable'] = true;
        $form['#enable_storage'] = true;
        $form['#bundle'] = $bundle->name;

        // Get available field types
        $field_types = $this->FieldUI_FilterableFieldTypes($bundle, true);
        
        if ((!$field_id = $context->getRequest()->asInt('field_id'))
            || (!$this->_field = $this->getModel('Field', 'Entity')->fetchById($field_id))
        ) {
            return false;
        }
        
        if (empty($field_types[$this->_field->getFieldType()]['filters'])
            || empty($field_types[$this->_field->getFieldType()]['creatable_filters'])
        ) {
            $context->setError(__('Invalid field type.', 'sabai'));
            return false;
        }
        $filters = array_intersect_key($field_types[$this->_field->getFieldType()]['filters'], $field_types[$this->_field->getFieldType()]['creatable_filters']);

        // Make sure a valid filter type is in the request
        if (!isset($formStorage['field_filter'])
            || !$this->Field_FilterImpl($formStorage['field_filter'], true)
        ) {
            unset($formStorage['field_filter']);
            
            if (count($filters) > 1) {
                // Display filter selection form
                $this->_submitButtons = array(array('#value' => __('Next', 'sabai')));
                asort($filters);
                $form['field_filter'] = array(
                    '#type' => 'radios',
                    '#title' => __('Select filter', 'sabai'),
                    '#options' => $filters,
                    '#default_value' => isset($field_types[$this->_field->getFieldType()]['default_filter']) ? $field_types[$this->_field->getFieldType()]['default_filter'] : current(array_keys($filters)),
                    '#required' => true,
                );
                $form['field_id'] = array(
                    '#type' => 'hidden',
                    '#value' => $this->_field->id,
                );

                return $form;
            }
            
            // Only 1 field filter available for this field, so try to select it and proceed
            $formStorage['field_filter'] = current(array_keys($filters));
            if (!$this->Field_FilterImpl($formStorage['field_filter'], true)) {
                $context->setError(__('Invalid field type.', 'sabai'));
                return false;
            }
        }

        // Set options
        $this->_ajaxOnSuccess = 'function(result, target, trigger) {
            $(SABAI).trigger("fieldui_filter_created.sabai", {trigger: trigger, result: result, target: target});
        }';
        $this->_ajaxOnError = 'function(error, target, trigger) {
            target.hide();
            alert(error.message);
        }';
        $this->_ajaxOnCancel = 'function(target) {
            target.hide();
        }';
        
        $ifieldfilter = $this->Field_FilterImpl($formStorage['field_filter']);
        $filter_settings = (array)$ifieldfilter->fieldFilterGetInfo('default_settings');
        
        $form['name'] = array(
            '#type' => 'textfield',
            '#title' => __('Field name', 'sabai'),
            '#description' => __('Enter a machine readable name for this form field. Only lowercase alphanumeric characters and underscores are allowed.', 'sabai'),
            '#max_length' => 255,
            '#required' => true,
            '#weight' => 2,
            '#size' => 20,
            '#regex' => '/^[a-z0-9_]+$/',
            '#element_validate' => array(array(array($this, 'validateName'), array($bundle->id))),
            '#default_value' => $this->_field->getFieldName(),
        );
        
        // Define fieldsets
        $form['basic'] = array(
            '#type' => 'fieldset',
            '#title' => __('Filter Settings', 'sabai'),
            '#tree' => false,
            '#weight' => 10,
            '#collapsed' => false,
        );
        $form['basic']['label'] = array(
            '#type' => 'textfield',
            '#title' => __('Label', 'sabai'),
            '#max_length' => 255,
            '#default_value' => ($default_label = $ifieldfilter->fieldFilterGetInfo('default_label')) ? $default_label : (string)$this->_field,
            '#weight' => 4,
            '#class' => 'sabai-form-field-less-margin',
            '#required' => true,
        );
        $form['basic']['hide_label'] = array(
            '#type' => 'checkbox',
            '#title' => __('Hide label', 'sabai'),
            '#weight' => 5,
        );
        $form['basic']['description'] = array(
            '#type' => 'textarea',
            '#title' => __('Description', 'sabai'),
            '#description' => __('Enter a short description of the field displayed to the user.', 'sabai'),
            '#rows' => 3,
            '#default_value' => null,
            '#weight' => 6,
        );
        $form['basic']['disabled'] = array(
            '#type' => 'checkbox',
            '#title' => __('Disabled', 'sabai'),
            '#default_value' => null,
            '#weight' => 9,
        );

        if ($filter_settings_form = (array)@$ifieldfilter->fieldFilterGetSettingsForm($this->_field, $filter_settings, array('settings'))) {
            $form['basic']['settings'] = array(
                '#type' => 'fieldset',
                '#tree' => true,
                '#tree_allow_override' => false,
                '#weight' => 50,
            ) + $filter_settings_form;
        }

        $form['field_id'] = array(
            '#type' => 'hidden',
            '#value' => $this->_field->getFieldId(),
        );
        
        $form['ele_id'] = array(
            '#type' => 'hidden',
            '#value' => $context->getRequest()->asStr('ele_id'),
        );

        $form['#name'] = 'fieldui_admin_create_filter_' . strtolower($formStorage['field_filter']);

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!isset($form->storage['field_filter'])) {
            // Filter selection form was submitted

            if (isset($form->values['field_filter'])) {
                $form->storage['field_filter'] = $form->values['field_filter'];
            }
            $form->rebuild = true;
            $form->settings = $this->_getFormSettings($context, $form->settings['#build_id'], $form->storage);

            return;
        }
        
        // Create filter
        $filter_data = array('column' => 1, 'row' => 1);
        $filter_data['label'] = isset($form->settings['basic']['label']) ? $form->values['label'] : '';
        $filter_data['hide_label'] = !empty($form->values['hide_label']);
        $filter_data['description'] = isset($form->settings['basic']['description']) ? $form->values['description'] : '';
        $filter_data['disabled'] = !empty($form->values['disabled']);
        $filter_data['settings'] = isset($form->settings['basic']['settings']) ? $form->values['settings'] : array();
        $filter_data['is_custom'] = true;
        
        $filter = $this->getAddon('Entity')->createEntityFilter($this->_field, $form->storage['field_filter'], $form->values['name'], $filter_data);
        
        $context->setSuccess(dirname($context->getRoute()))
            ->setSuccessAttributes(array(
                'id' => $filter->id,
                'label' => Sabai::h($filter->data['label']),
                'hide_label' => (int)$filter->data['hide_label'],
                'description' => $filter->data['description'],
                'type' => $filter->type,
                'type_normalized' => str_replace('_', '-', $filter->type),
                'name' => $filter->name,
                'disabled' => (int)!empty($filter->data['disabled']),
                'ele_id' => $context->getRequest()->asStr('ele_id'),
                'preview' => $this->FieldUI_PreviewFilter($this->_field, $filter),
            ));
        
        $this->Action('fieldui_submit_filter_success', array($filter, /*$isEdit*/ false));
    }

    public function validateName(Sabai_Addon_Form_Form $form, &$value, $element, $bundleId)
    {
        // Make sure the filter name is unique
        $repository = $this->getModel('Filter', 'Entity')->bundleId_is($bundleId)->name_is($value);
        if ($repository->count() > 0) {
            $form->setError(__('The name is already in use by another field.', 'sabai'), $element);
        }
    }
}