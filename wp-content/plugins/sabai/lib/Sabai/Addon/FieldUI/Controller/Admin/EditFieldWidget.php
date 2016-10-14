<?php
class Sabai_Addon_FieldUI_Controller_Admin_EditFieldWidget extends Sabai_Addon_Form_Controller
{
    private $_field;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Get available field types
        $field_types = $this->Field_Types();

        if ((!$field_id = $context->getRequest()->asInt('field_id'))
            || (!$this->_field = $this->getModel('Field', 'Entity')->fetchById($field_id))
            || empty($field_types[$this->_field->getFieldType()]['widgets']) // no supported widgets for this field type
        ) {
            $context->setError(__('Invalid field.', 'sabai'));

            return;
        }

        $bundle = $context->child_bundle ? $context->child_bundle : ($context->taxonomy_bundle ? $context->taxonomy_bundle : $context->bundle);
        
        // Define form
        $form = array();
        $form['#action'] = $this->Url($context->getRoute());
        $form['#token_reuseable'] = true;
        asort($field_types[$this->_field->getFieldType()]['widgets']);
        $form['field_widget'] = array(
            '#type' => 'radios',
            '#title' => __('Form element type', 'sabai'),
            '#description' => __('Select the type of form element you would like to present to the user when editing this field.', 'sabai'),
            '#options' => $field_types[$this->_field->getFieldType()]['widgets'],
            '#default_value' => $this->_field->getFieldWidget(),
            '#required' => true,
        );
        $form['field_id'] = array(
            '#type' => 'hidden',
            '#value' => $this->_field->id,
        );
        $form['ele_id'] = array(
            '#type' => 'hidden',
            '#value' => $context->getRequest()->asStr('ele_id'),
        );

        // Set options
        $this->_cancelWeight = -99;
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_ajaxCancelType = 'remote';
        $this->_cancelUrl = $this->Url($bundle->getAdminPath() . '/fields/edit', array('ele_id' => $context->getRequest()->asStr('ele_id'), 'field_id' => $this->_field->id));

        // Bring back to the edit field page if success
        $this->_ajaxOnSuccess = sprintf('function(result, target, trigger) {
            SABAI.ajax({
                type: "get",
                container: "#" + target.attr("id"),
                url: "%1$s",
                onContent: function(response, target, trigger) {target.focusFirstInput();},
                onError: function(error, target, trigger) {SABAI.flash(error.message, "danger");},
                scroll: true,
                modalWidth: 0
            });
        }', $this->Url($bundle->getAdminPath() . '/fields/edit', array('ele_id' => $context->getRequest()->asStr('ele_id'), 'field_id' => $this->_field->id), '', '&'));
        $this->_ajaxOnContent = 'function(response, target, trigger){target.focusFirstInput();}';

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (isset($form->settings['field_widget'])
            && $this->_field->getFieldWidget() != $form->values['field_widget'] // widget changed?
        ) {
            $ifieldwidget = $this->Field_WidgetImpl($form->values['field_widget']);
            $this->_field->setFieldWidget($form->values['field_widget']);
            $this->_field->setFieldWidgetSettings($this->_field->getFieldWidgetSettings() + (array)$ifieldwidget->fieldWidgetGetInfo('default_settings'));
        }

        $this->getModel(null, 'Entity')->commit();
        
        $context->setSuccessAttributes(array(
            'ele_id' => $context->getRequest()->asStr('ele_id'),
        ));
    }
}