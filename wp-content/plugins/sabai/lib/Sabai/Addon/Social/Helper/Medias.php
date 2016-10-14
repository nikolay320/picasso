<?php
class Sabai_Addon_Social_Helper_Medias extends Sabai_Helper
{
    /**
     * Returns all available social medias
     * @param Sabai $application
     */
    public function help(Sabai $application)
    {
        if (!$medias = $application->getPlatform()->getCache('social_medias')) {
            $medias = array();
            foreach ($application->getInstalledAddonsByInterface('Sabai_Addon_Social_IMedias') as $addon_name) {
                if (!$application->isAddonLoaded($addon_name)) continue;
                
                foreach ($application->getAddon($addon_name)->socialGetMediaNames() as $media_name) {
                    if (!$media_info = $application->getAddon($addon_name)->socialMediaGetInfo($media_name)) {
                        continue;
                    }
                    $medias[$media_name] = array('addon' => $addon_name) + $media_info;
                }
            }
            $application->getPlatform()->setCache($application->Filter('social_medias', $medias), 'social_medias', 0);
        }
        return $medias;
    }
}
