<?php
class Sabai_Addon_System_Controller_Admin_DeleteAddon extends Sabai_Addon_Form_Controller
{
    private $_addon;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $addon_name = $context->getRequest()->asStr('addon_name');

        // May not delete installed or non cloned add-on
        if ($this->getModel('Addon', 'System')->name_is($addon_name)->count()
            || (!$this->_addon = $this->fetchAddon($addon_name))
            || !$this->_addon->hasParent()
        ) {
            return false;
        }
        
        $this->_submitButtons[] = array('#value' => __('Delete Add-on', 'sabai'), '#btn_type' => 'danger');
        $message = sprintf(__('Are you sure you want to delete <strong>%s</strong>?', 'sabai'), Sabai::h($addon_name));
        return array(
            '#name' => 'system-admin-delete-' . strtolower($this->_addon->hasParent()),
            '#header' => array('<div class="sabai-alert sabai-alert-danger">' . $message . '</div>'),
            '#addon' => $addon_name,
        );
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        unlink($this->getClonesDir() . '/' . $this->_addon->getName() . '.php');
        $context->setSuccess($this->Url('/settings'))
            ->addFlash(sprintf(__('Add-on %s has been deleted.', 'sabai'), $this->_addon->getName()));
    }
}