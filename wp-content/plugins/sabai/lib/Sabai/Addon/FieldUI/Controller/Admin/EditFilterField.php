<?php
class Sabai_Addon_FieldUI_Controller_Admin_EditFilterField extends Sabai_Addon_Form_Controller
{
    private $_filter;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $bundle = $context->child_bundle ? $context->child_bundle : ($context->taxonomy_bundle ? $context->taxonomy_bundle : $context->bundle);
        $this->_cancelWeight = -99;
        $this->_ajaxOnContent = 'function(response, target, trigger){target.focusFirstInput();}';

        // Define form
        $form = array();
        $form['#action'] = $this->Url($context->getRoute());
        $form['#token_reuseable'] = true;
        $form['#enable_storage'] = true;
        $form['#bundle'] = $bundle->name;
        
        // Get available field types
        $field_types = $this->FieldUI_FilterableFieldTypes($bundle);
        
        if ((!$filter_id = $context->getRequest()->asInt('filter_id'))
            || (!$this->_filter = $this->getModel('Filter', 'Entity')->fetchById($filter_id))
        ) {
            return false;
        }
        if (empty($field_types[$this->_filter->Field->getFieldType()]['filters'])) {
            // no supported filters for this field type
            $this->_filter->markRemoved()->commit();
            $context->setError(__('Invalid field type.', 'sabai'));
            return false;
        }

        // Set options
        $this->_submitButtons = array(array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary'));
        $this->_ajaxOnSuccess = 'function(result, target, trigger) {
            $(SABAI).trigger("fieldui_filter_updated.sabai", {trigger: trigger, result: result, target: target});
        }';
        $this->_ajaxOnError = 'function(error, target, trigger) {
            target.hide();
            alert(error.message);
        }';
        $this->_ajaxOnCancel = 'function(target) {
            $(SABAI).trigger("fieldui_filter_cancelled.sabai", {trigger: $(this), target: target});
        }';

        try {
            $ifieldfilter = $this->Field_FilterImpl($this->_filter->type);
        } catch (Sabai_IException $e) {
            $default_filter = $field_types[$this->_filter->Field->getFieldType()]['default_filter'];
            if ($default_filter == $this->_filter->type) {
                // the default filter is the one that does not exist
                throw $e;
            }
            // Change filter to the default filter
            $this->_filter->set('type', $default_filter)->commit();
            $ifieldfilter = $this->Field_FilterImpl($default_filter);
        }
 
        $settings = $this->_filter->data['settings'] + (array)$ifieldfilter->fieldFilterGetInfo('default_settings');
        
        $form['filter_id'] = array(
            '#type' => 'hidden',
            '#value' => $this->_filter->id,
        );
        
        // Define fieldsets
        $form['basic'] = array(
            '#type' => 'fieldset',
            '#title' => __('Filter Settings', 'sabai'),
            '#tree' => false,
            '#weight' => 10,
            '#collapsed' => false,
        );
        
        // Display field type and link to switch to another filter
        $edit_filter_url = $this->Url($bundle->getAdminPath() . '/fields/filter/edit_filter', array('filter_id' => $this->_filter->id, 'ele_id' => $context->getRequest()->asStr('ele_id')));
        $filter_type = $this->_filter->isCustomFilter() && count($field_types[$this->_filter->Field->getFieldType()]['filters']) > 1
            ? $this->LinkToRemote($ifieldfilter->fieldFilterGetInfo('label'), $context->getContainer(), $edit_filter_url, array('scroll' => true))
            : $ifieldfilter->fieldFilterGetInfo('label');
        $form['field'] = array(
            '#type' => 'item',
            '#field_prefix' => __('Filter type:', 'sabai'),
            '#value' => sprintf(_x('%s (target field: %s)', 'filter_type', 'sabai'), $filter_type, $this->_filter->Field->getFieldLabel()),
            '#weight' => 1,
        );
        $form['basic']['label'] = array(
            '#type' => 'textfield',
            '#title' => __('Label', 'sabai'),
            '#max_length' => 255,
            '#default_value' => $this->_filter->getLabel(),
            '#weight' => 4,
            '#class' => 'sabai-form-field-less-margin',
            '#required' => true,
        );
        $form['basic']['hide_label'] = array(
            '#type' => 'checkbox',
            '#title' => __('Hide label', 'sabai'),
            '#default_value' => !empty($this->_filter->data['hide_label']),
            '#weight' => 5,
        );
        $form['basic']['description'] = array(
            '#type' => 'textarea',
            '#title' => __('Description', 'sabai'),
            '#description' => __('Enter a short description of the field displayed to the user.', 'sabai'),
            '#rows' => 3,
            '#default_value' => $this->_filter->data['description'],
            '#weight' => 6,
        );
        $form['basic']['disabled'] = array(
            '#type' => 'checkbox',
            '#title' => __('Disabled', 'sabai'),
            '#default_value' => !empty($this->_filter->data['disabled']),
            '#weight' => 9,
        );

        if ($settings_form = (array)@$ifieldfilter->fieldFilterGetSettingsForm($this->_filter->Field, $settings, array('settings'))) {
            $form['basic']['settings'] = array(
                '#type' => 'fieldset',
                '#tree' => true,
                '#tree_allow_override' => false,
                '#weight' => 50,
            ) + $settings_form;
        }
        $form['ele_id'] = array(
            '#type' => 'hidden',
            '#value' => $context->getRequest()->asStr('ele_id'),
        );
        
        $form['#name'] = 'fieldui_admin_edit_filter_' . strtolower($this->_filter->type);
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!isset($this->_filter)) {
            // Filter selection form was submitted

            if (isset($form->values['field_filter'])) {
                $form->storage['field_filter'] = $form->values['field_filter'];
            }
            $form->rebuild = true;
            $form->settings = $this->_getFormSettings($context, $form->settings['#build_id'], $form->storage);

            return;
        }

        // Field edit form submitted
        
        $filter_is_new = $this->_filter->id ? false : true;
        
        // Update filter
        $filter_data = array();
        $filter_data['label'] = $form->values['label'];
        $filter_data['hide_label'] = !empty($form->values['hide_label']);
        $filter_data['description'] = $form->values['description'];
        $filter_data['disabled'] = !empty($form->values['disabled']);
        $filter_data['is_custom'] = !empty($this->_filter->data['is_custom']);
        if (isset($form->settings['basic']['settings'])) {
            $filter_data['settings'] = $form->values['settings'];
        }
        $filter = $this->getAddon('Entity')->createEntityFilter($this->_filter->Field, $this->_filter->type, $this->_filter->name, $filter_data);
        
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
                'is_new' => $filter_is_new,
                'ele_id' => $context->getRequest()->asStr('ele_id'),
                'preview' => $this->FieldUI_PreviewFilter($this->_filter->Field, $filter),
            ));
        
        $this->Action('fieldui_submit_filter_success', array($filter, /*$isEdit*/ !$filter_is_new));
    }
}