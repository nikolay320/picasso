<?php
class Sabai_Addon_WordPress_Helper_DoShortcode extends Sabai_Helper
{    
    public function help(Sabai $application, $text, SabaiFramework_User_Identity $identity = null)
    {
        if (!isset($identity)) {
            if ($application->getUser()->isAdministrator()) {
                return $this->_doShortcode($text); 
            }
            $identity = $application->getUser();
        } else {
            if ($application->IsAdministrator($identity)) {
                return $this->_doShortcode($text); 
            }
        }
        // Do shortcode if author is allowed
        $shortcode_roles = $application->getAddon('WordPress')->getConfig('shortcode_roles');
        foreach ($application->getPlatform()->getUserRolesByUser($identity->id) as $role) {
            if (in_array($role, $shortcode_roles)) {
                return $this->_doShortcode($text); 
            }
        }
        return $text;
    }
    
    protected function _doShortcode($text)
    {
        if (strpos($text, '[/embed]') !== false) {
            $text = $GLOBALS['wp_embed']->run_shortcode($text);
        }
        return do_shortcode($text);
    }
}
