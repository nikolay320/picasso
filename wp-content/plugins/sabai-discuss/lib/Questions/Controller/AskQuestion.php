<?php
class Sabai_Addon_Questions_Controller_AskQuestion extends Sabai_Addon_Content_Controller_AddPost
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {   
        $form = parent::_doGetFormSettings($context, $formStorage);
        $this->_cancelUrl = dirname($context->getRoute());
        $this->_submitButtons['submit'] = array(
            '#value' => __('Publiez la Question', 'sabai-discuss'),
            '#btn_type' => 'primary',
        );
        
        if (($term_id = $context->getRequest()->asInt('term_id'))
            && ($term = $this->Entity_Entity('taxonomy', $term_id, false))
        ) {
            if (isset($form[$term->getBundleName()]['#type'])) {
                if ($form[$term->getBundleName()]['#type'] === 'autocomplete') {
                    $form[$term->getBundleName()]['#default_value'] = array($term->getId() => $term->getSlug());
                } else {
                    $form[$term->getBundleName()]['#default_value'] = $term->getId();
                }
            } elseif (isset($form[$term->getBundleName()][0])) { // repeatable field
                $form[$term->getBundleName()][0]['#default_value'] = $term->getId();
            }
        }
        
        return $form;
    }
}
