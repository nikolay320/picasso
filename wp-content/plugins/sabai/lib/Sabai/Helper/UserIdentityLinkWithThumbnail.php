<?php
class Sabai_Helper_UserIdentityLinkWithThumbnail extends Sabai_Helper
{
    private $_links;

    /**
     * Creates an HTML link of a user
     *
     * @return string
     * @param SabaiFrameworkApplication $application
     * @param SabaiFramework_User_Identity $identity
     */
    public function help(Sabai $application, SabaiFramework_User_Identity $identity, $thumbnailUrl, $thumbnailSize, $padding)
    {
        if ($identity->isAnonymous()) {
            $style = $this->_getStyle($application, $thumbnailUrl, $thumbnailSize, $padding);
            return $identity->url
                ? sprintf('<a href="%s" style="%s" target="_blank" rel="nofollow external" class="sabai-user sabai-user-anonymous">%s</a>', Sabai::h($identity->url), $style, Sabai::h($identity->name))
                : sprintf('<span style="%s" class="sabai-user sabai-user-anonymous sabai-user-with-thumbnail">%s</span>', $style, Sabai::h($identity->name));
        }

        $id = $identity->id;
        if (!isset($this->_links[$id][$thumbnailSize])) {
            $style = $this->_getStyle($application, $thumbnailUrl, $thumbnailSize, $padding);
            $url = $application->UserIdentityUrl($identity);
            $this->_links[$id][$thumbnailSize] = $url
                ? $application->LinkTo($identity->name, $url, array(), array('class' => 'sabai-user sabai-user-with-thumbnail', 'style' => $style, 'rel' => 'nofollow', 'data-popover-url' => $application->MainUrl('/sabai/user/profile/' . $identity->username)))
                : sprintf('<span class="sabai-user sabai-user-with-thumbnail" style="%s">%s</span>', $style, Sabai::h($identity->name));
        }

        return $this->_links[$id][$thumbnailSize];
    }
    
    protected function _getStyle(Sabai $application, $thumbnailUrl, $thumbnailSize, $padding)
    {
        return sprintf(
            'background:%4$s center url(%1$s) no-repeat transparent; height:%2$dpx; padding-%4$s:%3$dpx; display:inline-block; background-size:%2$dpx %2$dpx',
            $thumbnailUrl,
            $thumbnailSize,
            $thumbnailSize + $padding,
            $application->getPlatform()->isLanguageRTL() ? 'right' : 'left'
        );
    }
}
