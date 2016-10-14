<?php
class Sabai_Addon_Social extends Sabai_Addon
    implements Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminSettings,
               Sabai_Addon_Social_IMedias,
               Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets,
               Sabai_Addon_Field_IRenderers
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';
    
    public function systemGetMainRoutes()
    {
        $routes = array();
        foreach ($this->_application->getModel('Bundle', 'Entity')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            if (empty($bundle->info['social_shareable'])) continue;
            
            $routes[$bundle->info['permalink_path'] . '/:slug/share'] = array(
                'controller' => 'Share',
                'title_callback' => true,
                'callback_path' => 'share',
            );
        }
        return $routes;
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'share':
                return !empty($this->_config['enable_share']);
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'share':
                return __('Share', 'sabai');
        }
    }
    
    private function _onEntityBundlesChange($entityType)
    {
        $this->_application->getAddon('System')->reloadRoutes($this);
    }
    
    public function onEntityCreateBundlesSuccess($entityType, $bundles)
    {
        $this->_onEntityBundlesChange($entityType);
    }
    
    public function onEntityUpdateBundlesSuccess($entityType, $bundles)
    {
        $this->_onEntityBundlesChange($entityType);
    }
    
    public function onEntityDeleteBundlesSuccess($entityType, $bundles)
    {
        $this->_onEntityBundlesChange($entityType);
    }
    
    public function onEntityRenderHtml(Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Entity_IEntity $entity, $displayMode, $id, &$classes, &$links, &$buttons)
    {
        if (empty($this->_config['enable_share'])
            || $displayMode !== 'full'
            || empty($bundle->info['social_shareable'])
        ) return;
        
        $buttons['links']['share'] = array(
            $this->_application->LinkTo(
                __('Share', 'sabai'),
                '#',
                array('icon' => 'share-alt'),
                array('class' => 'sabai-social-btn-share')
            ),
        );
        
        foreach ($this->_application->Social_Medias() as $name => $media) {
            if (isset($media['shareable']) && false === $media['shareable']) continue; 
            
            if (isset($this->_config['medias']) && !in_array($name, $this->_config['medias'])) continue;

            $buttons['links']['share'][] = $this->_application->LinkTo(
                $media['label'],
                $this->_application->Entity_Url($entity, '/share', array('media' => $name)),
                array('icon' => $media['icon']),
                array('rel' => 'nofollow', 'target' => '_blank', 'title' => sprintf(__('Share this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true)))
            );
        }
    }
    
    public function socialGetMediaNames()
    {
        return array('facebook', 'twitter', 'googleplus', 'pinterest', 'tumblr', 'linkedin', 'flickr', 'youtube', 'instagram', 'rss', 'mail');
    }
    
    public function socialMediaGetInfo($name)
    {
        switch ($name) {
            case 'facebook': 
                return array('label' => 'Facebook', 'icon' => 'facebook-square', 'regex' => '/^https?:\/\/((w{3}\.)?)facebook.com\/.*/i', 'placeholder' => 'https://facebook.com/xxxxx');
            case 'twitter': 
                return array('label' => 'Twitter', 'icon' => 'twitter-square', 'regex' => '/^https?:\/\/twitter\.com\/(#!\/)?[a-z0-9_]+[\/]?$/i', 'placeholder' => 'https://twitter.com/xxxxx');
            case 'googleplus': 
                return array('label' => 'Google+', 'icon' => 'google-plus-square');
            case 'pinterest': 
                return array('label' => 'Pinterest', 'icon' => 'pinterest-square', 'shareable' => false);
            case 'tumblr': 
                return array('label' => 'Tumblr', 'icon' => 'tumblr-square');
            case 'linkedin': 
                return array('label' => 'LinkedIn', 'icon' => 'linkedin-square');
            case 'flickr': 
                return array('label' => 'Flickr', 'icon' => 'flickr', 'shareable' => false);
            case 'youtube': 
                return array('label' => 'YouTube', 'icon' => 'youtube-square', 'shareable' => false);
            case 'instagram': 
                return array('label' => 'Instagram', 'icon' => 'instagram', 'shareable' => false, 'feed' => false);
            case 'rss': 
                return array('label' => 'RSS', 'icon' => 'rss-square', 'shareable' => false);
            case 'mail': 
                return array('type' => 'email', 'label' => 'Mail', 'icon' => 'envelope-o', 'feed' => false, 'placeholder' => 'contact@example.com');
        }
    }
    
    public function socialMediaGetShareUrl($name, Sabai_Addon_Entity_IEntity $entity)
    {
        $url = $this->_application->Entity_Url($entity);
        switch ($name) {
            case 'facebook': 
                return $this->_application->Url(array(
                    'script_url' => 'https://www.facebook.com/sharer/sharer.php',
                    'params' => array(
                        'u' => (string)$url,
                    ),
                ));
            case 'twitter': 
                return $this->_application->Url(array(
                    'script_url' => 'https://twitter.com/intent/tweet',
                    'params' => array(
                        'url' => (string)$url,
                        'text' => $entity->getTitle(),
                    ),
                ));
            case 'googleplus': 
                return $this->_application->Url(array(
                    'script_url' => 'https://plus.google.com/share',
                    'params' => array(
                        'url' => (string)$url,
                    ),
                ));
            case 'tumblr':
                return $this->_application->Url(array(
                    'script_url' => 'http://www.tumblr.com/share/link',
                    'params' => array(
                        'url' => (string)$url,
                        'name' => $entity->getTitle(),
                        'description' => $this->_application->Summarize($entity->getContent(), 256),
                    ),
                ));
            case 'linkedin':
                return $this->_application->Url(array(
                    'script_url' => 'http://www.linkedin.com/shareArticle',
                    'params' => array(
                        'mini' => true,
                        'url' => (string)$url,
                        'title' => $entity->getTitle(),
                        'summary' => $this->_application->Summarize($entity->getContent(), 256),
                    ),
                ));
            case 'mail':
                return $this->_application->Url(array(
                    'script_url' => 'mailto:',
                    'params' => array(
                        'subject' => $entity->getTitle(),
                        'body' => (string)$url . "\n\n" . $this->_application->Summarize($entity->getContent(), 256),
                    ),
                ));
        }
    }

    public function fieldGetTypeNames()
    {
        return array('social_accounts');
    }

    public function fieldGetType($name)
    {
        return new Sabai_Addon_Social_FieldType($this, $name);
    }

    public function fieldGetWidgetNames()
    {
        return array('social_accounts');
    }

    public function fieldGetWidget($name)
    {
        return new Sabai_Addon_Social_FieldWidget($this, $name);
    }
    
    public function fieldGetRendererNames()
    {
        return array('social_accounts');
    }

    public function fieldGetRenderer($name)
    {
        return new Sabai_Addon_Social_FieldRenderer($this, $name);
    }
    
    public function onSocialIMediasInstalled(Sabai_Addon $addon, ArrayObject $log)
    {        
        $this->_application->getPlatform()->deleteCache('social_medias');
    }

    public function onSocialIMediasUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('social_medias');
    }

    public function onSocialIMediasUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('social_medias');
    }
    
    public function getDefaultConfig()
    {
        return array(
            'enable_share' => true,
            'medias' => null,
        );
    }
    
    public function systemGetAdminSettingsForm()
    {
        $medias = array();
        foreach ($this->_application->Social_Medias() as $media_name => $media) {
            if (isset($media['shareable']) && false === $media['shareable']) continue;
            
            $medias[$media_name] = sprintf('<i class="fa fa-%s"></i> %s', Sabai::h($media['icon']), Sabai::h($media['label']));
        }
        return array(
            'enable_share' => array(
                '#type' => 'checkbox',
                '#title' => __('Enable social sharing', 'sabai'),
                '#default_value' => $this->_config['enable_share'],
            ),
            'medias' => array(
                '#type' => 'checkboxes',
                '#title' => __('Social medias', 'sabai'),
                '#options' => $medias,
                '#default_value' => isset($this->_config['medias']) ? $this->_config['medias'] : array_keys($medias),
                '#title_no_escape' => true,
                '#class' => 'sabai-form-inline',
                '#states' => array(
                    'visible' => array(
                        'input[name="enable_share[]"]' => array('type' => 'checked', 'value' => true),
                    ),
                ),
            ),
        );
    }
}
