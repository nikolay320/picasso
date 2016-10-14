<?php
class Sabai_Addon_System_Controller_UserProfile extends Sabai_Controller
{    
    protected function _doExecute(Sabai_Context $context)
    {
        $links = array(
            $context->identity->url => array(
                'label' => $context->identity->url,
                'rel' => 'nofollow'
            ),
        );
        $context->addTemplate('system_userprofile')
            ->setAttributes(array(
                'profile' => $this->Filter('system_user_profile', $this->getPlatform()->getUserProfileHtml($context->identity->id), array($context->identity)),
                'activities' => $this->System_UserActivity($context->identity),
                'links' => $this->Filter('system_user_profile_links', $links, array($context->identity)),
            ));
    }
}