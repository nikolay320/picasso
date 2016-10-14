<?php
class Sabai_Addon_System_Controller_Admin_CloneAddon extends Sabai_Addon_Form_Controller
{
    private $_addon;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $addon_name = $context->getRequest()->asStr('addon_name');

        // Fetch addon info from the database
        if (!$this->_addon = $this->getModel('Addon', 'System')->name_is($addon_name)->fetchOne()) {
            return false;
        }
        
        if (!$this->getAddon($this->_addon->name)->isCloneable()) {
            return false;
        }
        
        $this->_submitButtons[] = array('#value' => __('Clone Add-on', 'sabai'), '#btn_type' => 'primary');
        $message = sprintf(__('Press the button below to clone the <strong>%s</strong> add-on.', 'sabai'), Sabai::h($addon_name));
        return array(
            '#name' => 'system-admin-clone-' . strtolower($this->_addon->name),
            '#header' => array('<div>' . $message . '</div>'),
            '#addon' => $this->_addon->name,
            '#current_version' => $this->_addon->version,
            'name' => array(
                '#type' => 'textfield',
                '#title' => __('Add-on Name', 'sabai'),
                '#regex' => Sabai::ADDON_NAME_REGEX,
                '#element_validate' => array(array($this, 'validateName')),
                '#required' => true,
            ),
        );
    }
    
    public function validateName($form, &$value, $element)
    {
        $value = ucfirst($value);
        if ($this->getModel('Addon', 'System')->name_is($value)->count()) {
            $form->setError(__('The name is already in use by another add-on.', 'sabai'), $element);
        } elseif (file_exists($this->getClonesDir() . '/' . $value . '.php')) {
            $form->setError(__('There is already a cloned add-on with that name.', 'sabai'), $element);
        }
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $clone_name = ucfirst($form->values['name']);
        if (!$this->CloneAddon($this->_addon->name, $clone_name)) {
            $form->setError(sprintf(__('Add-on %s could not be created.', 'sabai'), $clone_name));
            return;
        }
        $context->setSuccess($this->Url('/settings', array(), 'sabai-system-admin-addons-installable'))
            ->addFlash(sprintf(__('Add-on %s has been created.', 'sabai'), $clone_name));
    }
}