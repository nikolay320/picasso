<?php
class Sabai_Addon_Directory_Controller_Admin_ViewListingClaim extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $context->claim->with('Entity'); // load listing associated with the claim
        if ($context->claim->Entity && $context->claim->status === 'pending') {
            $this->_submitButtons = array(
                'reject' => array(
                    '#value' => __('Reject', 'sabai-directory'),
                    '#weight' => 1,
                    '#submit' => array(array(array($this, 'rejectClaim'), array($context))),
                    '#btn_type' => 'danger',
                ),
                'approve' => array(
                    '#value' => __('Approve', 'sabai-directory'),
                    '#weight' => 2,
                    '#submit' => array(array(array($this, 'approveClaim'), array($context))),
                    '#btn_type' => 'success',
                ),
            );
            // Allow approving this claim only if the listing does not already have a valid claim
            $this->Entity_LoadFields($context->claim->Entity);
            if ($this->Directory_ListingOwner($context->claim->Entity)) {
                $warning = __('This claim may not be approved because the listing has already been claimed by another user.', 'sabai-directory');
                $this->_submitButtons['approve']['#attributes']['class'] = 'sabai-btn-success sabai-disabled'; 
                $this->_submitButtons['approve']['#attributes']['disabled'] = 'disabled'; 
            }
        } else {
            $this->_submitButtons = array(
                'delete' => array(
                    '#value' => __('Delete', 'sabai-directory'),
                    '#weight' => 1,
                    '#submit' => array(array(array($this, 'deleteClaim'), array($context))),
                    '#btn_type' => 'danger',
                ),
            );
        }
        $form = array(
            '#header' => isset($warning) ? array('<div class="sabai-alert sabai-alert-warning">' . $warning . '</div>') : array(),
            'info' => array(
                '#type' => 'markup',
                '#value' => '<p>' . sprintf(
                    __('%s submitted %s for listing %s', 'sabai-directory'),
                    $this->UserIdentityLinkWithThumbnailSmall($context->claim->User),
                    $this->DateTime($context->claim->created),
                    $context->claim->Entity ? ($context->claim->Entity->isPublished() ? $this->LinkTo($context->claim->Entity->getTitle(), $this->Entity_Bundle($context->claim->Entity)->getAdminPath() . '/' . $context->claim->Entity->getId()) : Sabai::h($context->claim->Entity->getTitle())) : __('Unknown', 'sabai-directory')
                ) . '</p>',
                '#weight' => 1,
            ),
            'name' => array(
                '#title' => __('Contact name', 'sabai-directory'),
                '#type' => 'item',
                '#value' => Sabai::h($context->claim->name),
                '#weight' => 2,
            ),
            'email' => array(
                '#title' => __('E-mail', 'sabai-directory'),
                '#type' => 'item',
                '#value' => Sabai::h($context->claim->email),
                '#weight' => 3,
            ),
            'admin_note' => array(
                '#type' => $context->claim->status === 'pending' ? 'textarea' : 'item',
                '#title' => __('Admin note', 'sabai-directory'),
                '#description' => $context->claim->status === 'pending'
                    ? __('This note may be used for administration purpose or embedded in notifcation mail using the {claim_admin_note} tag.', 'sabai-directory')
                    : null,
                '#rows' => 5,
                '#default_value' => $context->claim->status === 'pending' ? null : $context->claim->admin_note,
                '#weight' => 10,
            ),
        );
        if ($context->claim->comment) {
            $form['comment'] = array(
                '#title' => __('Comment', 'sabai-directory'),
                '#type' => 'item',
                '#value' => nl2br(strip_tags($context->claim->comment)),
                '#weight' => 5,
            );
        }
        
        return $form;
    }
    
    public function deleteClaim(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $context->claim->markRemoved()->commit();
        $context->setSuccess($context->bundle->getAdminPath() . '/claims');
        // Notify
        $this->Action('directory_listing_claim_deleted', array($context->claim));
    }
    
    public function approveClaim(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        // Allow approving this claim only if the listing does not already have a valid claim
        if ($this->Directory_ListingOwner($context->claim->Entity)) return;
        
        $this->Directory_ClaimListing($context->claim->Entity, $context->claim->User, $this->getAddon()->getConfig('claims', 'duration'));
        $this->_updateClaim($context->claim, 'approved', $form->values['admin_note']);
        $context->setSuccess($context->bundle->getAdminPath() . '/claims');
    }
    
    public function rejectClaim(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {        
        $this->_updateClaim($context->claim, 'rejected', $form->values['admin_note']);
        $context->setSuccess($context->bundle->getAdminPath() . '/claims');
    }
        
    protected function _updateClaim(Sabai_Addon_Directory_Model_Claim $claim, $status, $adminNote)
    {
        $claim->set('status', $status)->set('admin_note', $adminNote)->commit()->reload();
        $this->Action('directory_listing_claim_status_change', array($claim));
    }
}