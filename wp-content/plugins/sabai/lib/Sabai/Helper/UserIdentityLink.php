<?php
class Sabai_Helper_UserIdentityLink extends Sabai_Helper
{
    private $_links;

    /**
     * Creates an HTML link of a user
     *
     * @return string
     * @param SabaiFrameworkApplication $application
     * @param SabaiFramework_User_Identity $identity
     */
    public function help(Sabai $application, SabaiFramework_User_Identity $identity)
    {
        if ($identity->isAnonymous()) {
            return $identity->url
                ? sprintf('<a href="%s" target="_blank" rel="nofollow external" class="sabai-user sabai-user-anonymous">%s</a>', Sabai::h($identity->url), Sabai::h($identity->name))
                : '<span class="sabai-user sabai-user-anonymous">' . Sabai::h($identity->name) . '</span>';
        }

        $id = $identity->id;
        if (!isset($this->_links[$id])) {
            $url = $application->UserIdentityUrl($identity);
            $this->_links[$id] = $url
                ? $application->LinkTo($identity->name, $url, array(), array('class' => 'sabai-user', 'rel' => 'nofollow', 'data-popover-url' => $application->MainUrl('/sabai/user/profile/' . $identity->username)))
                : '<span class="sabai-user">' . Sabai::h($identity->name) . '</span>';
        }

        return $this->_links[$id];
    }
}
