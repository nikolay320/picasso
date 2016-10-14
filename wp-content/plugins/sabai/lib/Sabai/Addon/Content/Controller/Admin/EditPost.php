<?php
class Sabai_Addon_Content_Controller_Admin_EditPost extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $bundle = $this->_getBundle($context);
        $this->_cancelUrl = $bundle->getAdminPath();
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        
        // Pass form values if form has been submitted. Usually, this is not needed to initialize form settings,
        // but the entity form needs to check values to see if any form fields have been added by the user.
        $values = null;
        if ($context->getRequest()->isPostMethod()
            && $context->getRequest()->has(Sabai_Addon_Form::FORM_BUILD_ID_NAME)
        ) {
            $values = $context->getRequest()->getParams();
        }

        $form = $this->Entity_Form($context->entity, $values, true);
        $status_options = array(
            Sabai_Addon_Content::POST_STATUS_PUBLISHED => __('Published', 'sabai'),
            Sabai_Addon_Content::POST_STATUS_DRAFT => __('Draft', 'sabai'),
        );
        if ($context->entity->isPending()) {
            $status_options[Sabai_Addon_Content::POST_STATUS_PENDING] = __('Pending', 'sabai');
        }
        $form['content_post_status'] = array(
            '#type' => 'select',
            '#title' => __('Status', 'sabai'),
            '#options' => $status_options,
            '#default_value' => $context->entity->getStatus(),
            '#weight' => -99,
        );
        $form['content_post_published'] = array(
            '#type' => 'date_datepicker',
            '#title' => __('Date', 'sabai'),
            '#default_value' => $context->entity->getTimestamp(),
            '#max_date' => time(),
            '#weight' => -98,
        );
        if (!empty($bundle->info['content_featurable'])) {
            $featured = $context->entity->getFieldValue('content_featured');
            $form['content_featured'] = array(
                '#title' => __('Featured', 'sabai'),
                '#tree' => true,
                '#collapsible' => false,
                '#weight' => -96,
                'enable' => array(
                    '#type' => 'checkbox',
                    '#title' => sprintf(__('This is a featured %s', 'sabai'), strtolower($this->Entity_BundleLabel($bundle, true))),
                    '#default_value' => !empty($featured[0]['value']),
                ),
                'value' => array(
                    '#type' => 'select',
                    '#title' => __('Priority', 'sabai'),
                    '#states' => array(
                        'visible' => array('input[name="content_featured[enable][]"]' => array('type' => 'checked', 'value' => true)),
                    ),
                    '#options' => array(
                        9 => _x('High', 'priority', 'sabai'),
                        5 => _x('Normal', 'priority', 'sabai'),
                        1 => _x('Low', 'priority', 'sabai'),
                    ),
                    '#default_value' => !empty($featured[0]['value']) ? $featured[0]['value'] : 5,
                ),
                'expires_at' => array(
                    '#type' => 'date_datepicker',
                    '#title' => __('End Date', 'sabai'),
                    '#states' => array(
                        'visible' => array('input[name="content_featured[enable][]"]' => array('type' => 'checked', 'value' => true)),
                    ),
                    '#default_value' => !empty($featured[0]['expires_at']) ? $featured[0]['expires_at'] : null,
                    '#empty_value' => 0,
                ),
            );
        }
        $form['content_post_user_id'] = array(
            '#weight' => -95,
            '#type' => 'user',
            '#title' => __('Author', 'sabai'),
            '#default_value' => $context->entity->getAuthorId(),
        );
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $was_pending = $context->entity->isPending();
        if (empty($form->values['content_featured']['enable'])) {
            $form->values['content_featured'] = false;
        }
        // Update entity
        $entity = $this->Entity_Save($context->entity, $form->values);
        if ($was_pending && $entity->isPublished()) {
            $this->Content_PublishPosts($entity, true); // publish child posts
        }
        $context->setSuccess($this->_getBundle($context)->getAdminPath() . '/' . $entity->getId())
            ->addFlash(sprintf(__('%s updated successfully.', 'sabai'), $this->Entity_BundleLabel($this->_getBundle($context), true)));
        return $entity;
    }
    
    /**
     * @return Sabai_Addon_Model_Bundle 
     */
    protected function _getBundle(Sabai_Context $context)
    {
        return $context->bundle;
    }
}