<?php
class Sabai_Addon_FieldUI_Controller_Admin_EditFilterFieldFilter extends Sabai_Addon_Form_Controller
{
    private $_filter;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        if ((!$filter_id = $context->getRequest()->asInt('filter_id'))
            || (!$this->_filter = $this->getModel('Filter', 'Entity')->fetchById($filter_id))
        ) {
            return false;
        }
        
        // Get available field types
        $field_types = $this->Field_Types();
        
        if (!$filters = @$field_types[$this->_filter->Field->getFieldType()]['filters']) {
            // no supported filters for this field type
            $this->_filter->markRemoved()->commit();
            $context->setError(__('Invalid field type.', 'sabai'));
            return false;
        }

        $bundle = $context->child_bundle ? $context->child_bundle : ($context->taxonomy_bundle ? $context->taxonomy_bundle : $context->bundle);
        
        // Define form
        $form = array();
        $form['#action'] = $this->Url($context->getRoute());
        $form['#token_reuseable'] = true;
        asort($filters);
        $form['field_filter'] = array(
            '#type' => 'radios',
            '#title' => __('Select filter', 'sabai'),
            '#options' => $filters,
            '#default_value' => $this->_filter->type,
            '#required' => true,
        );
        $form['filter_id'] = array(
            '#type' => 'hidden',
            '#value' => $this->_filter->id,
        );
        $form['ele_id'] = array(
            '#type' => 'hidden',
            '#value' => $context->getRequest()->asStr('ele_id'),
        );

        // Set options
        $this->_cancelWeight = -99;
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_ajaxCancelType = 'remote';
        $this->_cancelUrl = $this->Url($bundle->getAdminPath() . '/fields/filter/edit', array('ele_id' => $context->getRequest()->asStr('ele_id'), 'filter_id' => $this->_filter->id));

        // Bring back to the edit field page if success
        $this->_ajaxOnSuccess = sprintf('function(result, target, trigger) {
            SABAI.ajax({
                type: "get",
                container: "#" + target.attr("id"),
                url: "%1$s",
                onContent: function(response, target, trigger) {target.focusFirstInput();},
                onError: function(error, target, trigger) {SABAI.flash(error.message, "danger");},
                scroll: true
            });
        }', $this->Url($bundle->getAdminPath() . '/fields/filter/edit', array('ele_id' => $context->getRequest()->asStr('ele_id'), 'filter_id' => $this->_filter->id), '', '&'));
        $this->_ajaxOnContent = 'function(response, target, trigger){target.focusFirstInput();}';

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (isset($form->settings['field_filter'])
            && $this->_filter->type != $form->values['field_filter'] // filter changed?
        ) {          
            $this->getAddon('Entity')->createEntityFilter(
                $this->_filter->Field,
                $form->values['field_filter'],
                $this->_filter->name,
                array('settings' => (array)$this->Field_FilterImpl($form->values['field_filter'])->fieldFilterGetInfo('default_settings'))
            );
        }
    }
}