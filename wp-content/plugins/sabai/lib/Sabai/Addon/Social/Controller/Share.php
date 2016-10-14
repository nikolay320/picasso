<?php
class Sabai_Addon_Social_Controller_Share extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        if (!$media_name = $context->getRequest()->asStr('media')) {
            $context->setError('Invalid social media.');
            return;
        }
        $medias = $this->Social_Medias();
        if ((!$media = @$medias[$media_name])
            || (isset($media['shareable']) && $media['shareable'] === false)
            || !isset($media['addon'])
            || (!$addon = $this->getAddon($media['addon']))
        ) {
            $context->setError('Invalid social media.');
            return;
        }
        
        if (!$url = $addon->socialMediaGetShareUrl($media_name, $context->entity)) {
            $context->setError();
            return;
        }
        
        $context->setRedirect($this->Filter('social_media_url', $url, array($context->entity, $media_name)));
    }
}
