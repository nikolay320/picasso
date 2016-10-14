<?php
class Sabai_Addon_Taxonomy_Controller_Admin_DeleteTerm extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = array(
            'filter' => array('#type' => 'hidden', '#value' => $context->getRequest()->asStr('filter')),
            'sort' => array('#type' => 'hidden', '#value' => $context->getRequest()->asStr('sort')),
            'order' => array('#type' => 'hidden', '#value' => $context->getRequest()->asStr('order')),
        );
        $term_label = $this->Entity_BundleLabel($this->Entity_Bundle($context->entity), true);
        $form['#header'][] = sprintf(
            '<div class="sabai-alert sabai-alert-warning">%s</div>',
            sprintf(__('Are you sure you want to delete this %s?', 'sabai'), $term_label)
        );
        $this->_submitButtons['submit'] = array(
            '#value' => sprintf(_x('Delete %s', 'Delete taxonomy term form submit button', 'sabai'), $term_label),
            '#btn_type' => 'primary',
        );
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $this->getAddon('Entity')->deleteEntities('taxonomy', array($context->entity->getId() => $context->entity));
        $this->getPlatform()->deleteCache('taxonomy_terms_' . $context->taxonomy_bundle->name); // clear taxonomy terms cache
        $context->setSuccess($this->Url($this->Entity_Bundle($context->entity)->getAdminPath(), array(
            'filter' => $context->getRequest()->asStr('filter'),
            'sort' => $context->getRequest()->asStr('sort'),
            'order' => $context->getRequest()->asStr('order')
        )));
    }
}