<?php
abstract class Sabai_Addon_Form_MultiStepController extends Sabai_Addon_Form_Controller
{
    private $_currentStep, $_steps;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        if (!$this->_steps = $this->_getSteps($context, $formStorage)) {
            return false;
        }
        
        // Reset all properties
        $this->_submitable = true;
        $this->_submitButtons = array();
        $this->_ajaxSubmit = null;
        $this->_ajaxCancelType = 'hide';
        $this->_ajaxCancelUrl = null;
        $this->_ajaxOnCancel = 'function(target){}';
        $this->_ajaxOnSuccess = null;
        $this->_ajaxOnSuccessFlash = false;
        $this->_ajaxOnError = null;
        $this->_ajaxOnContent = null;
        $this->_cancelUrl = null;
        $this->_cancelWeight = 99;
        $this->_successFlash = null;

        if (isset($formStorage['step'])
            && ($formStorage['step'] !== $this->_getFirstStep())
        ) {
            $this->_currentStep = $formStorage['step'];
            // Get form for the current step
            if (!$form = $this->_getForm($this->_currentStep, $context, $formStorage)) {
                if ($form === false) {
                    return false;
                }
                $this->_complete($context, $formStorage);
                return;
            }
        } else {
            $this->_currentStep = $this->_getFirstStep();
            // Get form for the current step
            if (!$form = $this->_getForm($this->_currentStep, $context, $formStorage)) {
                if ($form === false) {
                    return false;
                }
                $this->_complete($context, $formStorage);
                return;
            }
            $form['#disable_back_btn'] = true;            
        }
        if (false !== $this->_submitButtons) { // false means to never add submit buttons
            if (empty($this->_submitButtons)) {
                $this->_submitButtons[] = array(
                    '#value' => isset($form['#next_btn_label']) ? $form['#next_btn_label'] : (false !== $this->_getNextStep() ? __('Next step', 'sabai') : __('Save', 'sabai')),
                    '#btn_type' => 'primary',
                    '#weight' => 10,
                );
            }
            if (empty($form['#disable_back_btn'])) {
                $this->_submitButtons['back'] = array(
                    '#value' => isset($form['#back_btn_label']) ? $form['#back_btn_label'] : __('Previous', 'sabai'),
                    '#weight' => -10,
                    '#submit' => array(array(array($this, 'previousForm'), array($context))),
                    '#skip_validate' => true, // skip validating the currently displayed form
                );
            }
        }
        $form['#enable_storage'] = true;
        $form['#token_id'] = $this->ControllerName(get_class($this));

        return $form;
    }

    public function previousForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (empty($form->settings['#back_to'])) {
            if (false === $previous_step = $this->_getPreviousStep()) {
                // this should never happen
                throw new Sabai_RuntimeException('Previus step does not exist');
            }
        } else {
            $previous_step = $form->settings['#back_to'];
        }
        $form->storage['step'] = $previous_step;
        $form->values = $form->storage['values'][$form->storage['step']];
        $form->rebuild = true;
        $form->settings = $this->_getFormSettings($context, $form->settings['#build_id'], $form->storage);
        return false; // stop processing the form
    }

    final public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        // Save submitted form values
        $form->storage['values'][$this->_currentStep] = $form->values;

        // Call submit callback if any exists
        if (false === $this->_submitForm($this->_currentStep, $context, $form)) {
            return;
        }
        
        // Return if error or redirect
        if ($context->isError()
            || $context->isRedirect()
        ) {
            return;
        }

        // One or more steps may have been skipped, so make sure there are more steps afterwards.
        if (false === $next_step = $this->_getNextStep()) {
            $this->_complete($context, $form->storage);
            return;
        }

        // Advance to the next step
        $form->storage['step'] = $next_step;
        if (!$form->redirect) {
            $form->rebuild = true;
            $form->settings['#submit'][] = array(array(array($this, 'getFormSettingsCallback'), array($context)));
        }
    }
    
    public function getFormSettingsCallback($form, $context)
    {
        $form->settings = $this->_getFormSettings($context, $form->settings['#build_id'], $form->storage);
    }

    final protected function _skipStep(array &$formStorage, $skipTo = null)
    {
        if (!isset($skipTo)) {
            $skipTo = $this->_getNextStep();
        }
        $this->_currentStep = $formStorage['step'] = $skipTo;
        return $this->_currentStep;
    }
    
    protected function _skipStepAndGetForm(Sabai_Context $context, array &$formStorage, $skipTo = null)
    {
        return ($step = $this->_skipStep($formStorage, $skipTo)) ? $this->_getForm($step, $context, $formStorage) : array();
    }
    
    protected function _getNextStep()
    {
        if ($this->_currentStep === false) return false;
        
        $next_step_key_index = array_search($this->_currentStep, $this->_steps) + 1;
        
        return isset($this->_steps[$next_step_key_index]) ? $this->_steps[$next_step_key_index] : false;
    }
    
    protected function _getPreviousStep()
    {
        if ($this->_currentStep === false) end(array_values($this->_steps));
        
        $previous_step_key_index = array_search($this->_currentStep, $this->_steps) - 1;
        
        return isset($this->_steps[$previous_step_key_index]) ? $this->_steps[$previous_step_key_index] : false;
    }
    
    protected function _getFirstStep()
    {
        return current(array_values($this->_steps));
    }
    
    protected function _getForm($step, Sabai_Context $context, array &$formStorage)
    {
        while ((!$form = call_user_func_array(array($this, '_getFormForStep' . $this->Camelize($step)), array($context, &$formStorage)))
            && false !== $form
            && ($step = $this->_getNextStep())
        ) {
            $this->_skipStep($formStorage, $step);
        }
        
        return $form;
    }
    
    protected function _submitForm($step, Sabai_Context $context, Sabai_Addon_Form_Form $form)
    {
        $method = '_submitFormForStep' . $this->Camelize($step);
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), array($context, $form));
        }
    }
    
    protected function _isBack(Sabai_Context $context)
    {
        return $context->getRequest()->has(Sabai_Addon_Form::FORM_BUILD_ID_NAME)
            && ($buttons = $context->getRequest()->get(Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME))
            && ($keys = array_keys($buttons))
            && array_shift($keys) === 'back';
    }

    /**
     * @return array
     */
    abstract protected function _getSteps(Sabai_Context $context, array &$formStorage);

    abstract protected function _complete(Sabai_Context $context, array $formStorage);
}
