<?php
class Sabai_Helper_UserIdentityThumbnail extends Sabai_Helper
{
    private $_cache;

    /**
     * Creates a thumbnail link to user
     *
     * @return string
     * @param SabaiFrameworkApplication $application
     * @param SabaiFramework_User_Identity $identity
     * @param string $thumbnailUrl
     * @param int $thumbnailSize
     */
    public function help(Sabai $application, SabaiFramework_User_Identity $identity, $thumbnailUrl, $thumbnailSize)
    {        
        if ($identity->isAnonymous()) {
            return $this->_getThumbnail($application, $identity, $thumbnailUrl, $thumbnailSize);
        }
        
        $id = $identity->id;
        if (!isset($this->_cache[$id][$thumbnailSize])) {
            $this->_cache[$id][$thumbnailSize] = $this->_getThumbnail($application, $identity, $thumbnailUrl, $thumbnailSize);
        }

        return $this->_cache[$id][$thumbnailSize];
    }
    
    protected function _getThumbnail(Sabai $application, SabaiFramework_User_Identity $identity, $thumbnailUrl, $thumbnailSize)
    {
        if (!$thumbnailUrl && (!$thumbnailUrl = $this->_getGravatarUrl($application, $identity, $thumbnailSize))) {
            return '';
        }
        return sprintf('<img src="%1$s" width="%2$d" height="%2$d" style="width:%2$dpx; height:%2$dpx;" alt="%3$s" title="%3$s" />', Sabai::h($thumbnailUrl), $thumbnailSize, Sabai::h($identity->name));
    }
    
    protected function _getGravatarUrl(Sabai $application, SabaiFramework_User_Identity $identity, $thumbnailSize)
    {
        if (!$identity->gravatar || !$identity->email) {
            return;
        }
        return $application->GravatarUrl($identity->email, $thumbnailSize, $identity->gravatar_default, $identity->gravatar_rating);
    }
}