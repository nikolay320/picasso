<?php
if (!defined('SABAI_PACKAGE_PAIDLISTINGS_PATH')) return;

require_once SABAI_PACKAGE_PAIDLISTINGS_PATH . '/lib/PaidListings/IFeatures.php';
require_once SABAI_PACKAGE_PAIDLISTINGS_PATH . '/lib/PaidListings/IEntityBundleTypes.php';

class Sabai_Addon_PaidDirectoryListings extends Sabai_Addon
    implements Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_PaidListings_IFeatures,
               Sabai_Addon_System_IAdminMenus,
               Sabai_Addon_PaidListings_IEntityBundleTypes
{
    const VERSION = '1.3.28', PACKAGE = 'sabai-directory';

    public function isUninstallable($currentVersion)
    {
        return true;
    }
    
    public function systemGetMainRoutes()
    {
        $addon = $this->_application->getAddon('Directory');
        $routes = array(
            '/' . $addon->getSlug('add-listing') => array(
                'controller' => 'AddListing',
                'priority' => 6,
                'title' => $addon->getTitle('add-listing'),
            ),
            '/' . $addon->getSlug('dashboard') . '/renew-listing' => array(
                'controller' => 'RenewListing',
                'title_callback' => true,
                'access_callback' => true,
                'callback_path' => 'renew_listing',
                'priority' => 6,
            ),
            '/' . $addon->getSlug('dashboard') . '/order-listing-addon' => array(
                'controller' => 'OrderListingAddons',
                'title_callback' => true,
                'access_callback' => true,
                'callback_path' => 'order_listing_addons',
                'priority' => 6,
            ),
            '/' . $addon->getSlug('dashboard') . '/orders' => array(
                'controller' => 'Orders',
                'title_callback' => true,
                'callback_path' => 'orders',
                'priority' => 6,
                'type' => Sabai::ROUTE_TAB,
            ),
        );
        $routes += $this->_getMainRoutes($addon);
        foreach ($this->_application->getModel('Addon', 'System')->parentAddon_is('Directory')->fetch() as $addon) {
            $routes += $this->_getMainRoutes($this->_application->getAddon($addon->name));
        }
        return $routes;
    }
    
    private function _getMainRoutes(Sabai_Addon $addon)
    {
        return array(
            '/' . $addon->getSlug('listing') . '/:slug/edit' => array(
                'controller' => 'EditListing',
                'title_callback' => true,
                'access_callback' => true,
                'callback_path' => 'edit_listing',
                'callback_addon' => $addon->getName(),
                'priority' => 6,
            ),
            '/' . $addon->getSlug('listing') . '/:slug/' . $addon->getSlug('claim') => array(
                'controller' => 'ClaimListing',
                'access_callback' => true,
                'title_callback' => true,
                'callback_addon' => $addon->getName(),
                'callback_path' => 'claim',
                'priority' => 6,
            ),
            '/' . $addon->getSlug('listing') . '/:slug/contact' => array(
                'controller' => 'ListingContact',
                'title_callback' => true,
                'access_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_addon' => $this->_name,
                'callback_path' => 'listing_contact',
                'controller_addon' => 'Directory',
                'priority' => 6,
                'weight' => 60,
            ),
            '/' . $addon->getSlug('listing') . '/:slug/reviews' => array(
                'controller' => 'ListingReviews',
                'access_callback' => true,
                'title_callback' => true,
                'type' => Sabai::ROUTE_INLINE_TAB,
                'callback_addon' => $this->_name,
                'callback_path' => 'listing_reviews',
                'controller_addon' => 'Directory',
                'priority' => 6,
                'weight' => 6,
            ),
            '/sabai/paiddirectorylistings/pricing' => array(
                'controller' => 'PricingTable',
                'type' => Sabai::ROUTE_CALLBACK,
                'priority' => 5,
            ),
        );
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'renew_listing':
                // Check if the claim can expire and if expired the expiration date is within the grace period
                if (!$this->_isMyListingRequested($context)
                    || (!$claim = $context->entity->getSingleFieldValue('directory_claim'))
                    || empty($claim['expires_at'])  // never expires if empty
                    || $claim['expires_at'] <= time() - ($this->_application->Entity_Addon($context->entity)->getConfig('claims', 'grace_period') * 86400)
                    || !count($this->_application->PaidListings_ActivePlans($context->entity->getBundleName(), 'base')) > 0 // active plans exist?
                 ) {
                    $context->setForbiddenError('/' . $this->_application->getAddon('Directory')->getSlug('dashboard'));
                    return false;
                }
                return true;
            case 'order_listing_addons':
                if (!$this->_isMyListingRequested($context)
                    || !count($this->_application->PaidListings_ActivePlans($context->entity->getBundleName(), 'addon'))
                ) {
                    $context->setForbiddenError('/' . $this->_application->getAddon('Directory')->getSlug('dashboard'));
                    return false;
                }
                return true;
            case 'listing_contact':
                $lead_bundle_name = $this->_application->Entity_Addon($context->entity)->getLeadBundleName();
                if (!$this->_application->HasPermission($lead_bundle_name . '_add')
                    || (!$context->child_bundle = $this->_application->Entity_Bundle($lead_bundle_name))
                ) {
                    return false;
                }
                if (empty($context->entity->directory_claim)) {
                    return (bool)$this->_application->Entity_Addon($context->entity)->getConfig('claims', 'unclaimed', 'leads', 'enable');
                }
                if (!$plan = $this->_application->PaidListings_Plan($context->entity)) return true; // claimed but no plan
                return !empty($plan->features['paiddirectorylistings_leads']['enable'])
                    || !empty($context->entity->paidlistings_plan[0]['addon_features']['paiddirectorylistings_leads']['enable']);
            case 'listing_reviews':
                $review_bundle_name = $this->_application->Entity_Addon($context->entity)->getReviewBundleName();
                if (!$context->child_bundle = $this->_application->Entity_Bundle($review_bundle_name)) {
                    return false;
                }
                if (empty($context->entity->directory_claim)) {
                    $enabled = $this->_application->Entity_Addon($context->entity)->getConfig('claims', 'unclaimed', 'reviews', 'enable');
                    return !isset($enabled) || !empty($enabled);
                }
                if (!$plan = $this->_application->PaidListings_Plan($context->entity)) return true; // claimed but no plan
                return !isset($plan->features['paiddirectorylistings_reviews']['enable']) // for campat with < 1.3.7
                    || (!empty($plan->features['paiddirectorylistings_reviews']['enable'])
                        || !empty($context->entity->paidlistings_plan[0]['addon_features']['paiddirectorylistings_reviews']['enable']));
            case 'listing_tab':
                $context->tab_name = basename($route['path']);
                if (empty($context->entity->directory_claim)) {
                    return (null === $tabs = $this->_application->Entity_Addon($context->entity)->getConfig('claims', 'unclaimed', 'tabs'))
                        || in_array($context->tab_name, $tabs);
                }
                
                if (!$plan = $this->_application->PaidListings_Plan($context->entity)) return true; // claimed but no plan
                return !empty($plan->features['paiddirectorylistings_claim']['tabs']) 
                    && in_array($context->tab_name, $plan->features['paiddirectorylistings_claim']['tabs']);
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'listing_contact':
                return strlen($title) ? $title : __('Contact Us', 'sabai-directory');
            case 'listing_reviews':
                if (!strlen($title)) $title = _x('Reviews', 'tab', 'sabai-directory');
                if ($titleType !== Sabai::ROUTE_TITLE_TAB && $titleType !== Sabai::ROUTE_TITLE_TAB_DEFAULT) {
                    return $title;
                }
                return ($count = $context->entity->getSingleFieldValue('content_children_count', 'directory_listing_review'))
                    ? sprintf(_x('%s (%d)', 'tab', 'sabai-directory'), $title, $count)
                    : $title;
            case 'orders':
                return __('Orders', 'sabai-directory');
            case 'order_listing_addons':
                return __('Order Listing Add-on', 'sabai-directory');
            case 'renew_listing':
                return __('Renew Listing', 'sabai-directory');
        }
    }
    
    protected function _isMyListingRequested(Sabai_Context $context)
    {
        if ((!$id = $context->getRequest()->asInt('listing_id'))
            || (!$entity = $this->_application->Entity_Entity('content', $id))
            || !$this->_application->Directory_IsListingOwner($entity, false)
        ) {
            return false;
        }
        $context->entity = $entity;
        return true;
    }
    
    public function systemGetAdminRoutes()
    {
        $routes = array();
        foreach (array('Directory' => 'Directory') + $this->_application->getModel('Addon', 'System')->parentAddon_is('Directory')->fetch()->getArray('name', 'name') as $addon) {
            $routes['/' . strtolower($addon) . '/claims/:claim_id'] = array(
                'controller' => 'ViewListingClaim',
                'format' => array(':claim_id' => '\d+'),
                'title_callback' => true,
                'access_callback' => true,
                'controller_addon' => $this->_name,
                'callback_path' => 'claim',
                'callback_addon' => 'Directory',
                'priority' => 6,
            );
        }
        return $routes;
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route){}

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route){}
    
    public function paidListingsGetFeatureNames()
    {
        return array('paiddirectorylistings_claim', 'paiddirectorylistings_featured', 'paiddirectorylistings_categories',
            'paiddirectorylistings_photos', 'paiddirectorylistings_leads', 'paiddirectorylistings_locations', 'paiddirectorylistings_reviews');
    }
    
    public function paidListingsGetFeature($name)
    {
        if ($name === 'paiddirectorylistings_locations' && !$this->_application->isAddonLoaded('GoogleMaps')) {
            return false;
        }
        require_once dirname(__FILE__) . '/PaidDirectoryListings/Feature.php';
        return new Sabai_Addon_PaidDirectoryListings_Feature($this, $name);
    }
    
    public function systemGetAdminMenus()
    {
        return array();
    }
    
    private function _createDefaultPlans($entityBundleName)
    {
        $this->_application->PaidDirectoryListings_CreateDefaultPlans($entityBundleName);
    }
    
    public function onDirectoryDashboardListingActionsFilter(&$actions, $bundle, $listing, $identity, $expired)
    {
        if ($expired !== null) {        
            if ($listing->isPublished()) {
                $plans = $this->_application->PaidListings_ActivePlans($bundle->name);
                $expires_at = $listing->directory_claim[0]['expires_at'];
                if (!empty($plans['base'])) {
                    if (!empty($expires_at) // this listing expires
                        && $expires_at > time() - ($this->_application->Entity_Addon($listing)->getConfig('claims', 'grace_period') * 86400) // has not yet completely expired
                    ) {
                        $actions['renew'] = array(
                            'url' => $this->_application->Url('/' . $this->_application->getAddon('Directory')->getSlug('dashboard') . '/renew-listing', array('listing_id' => $listing->getId())),
                            'icon' => 'refresh',
                            'title' => __('Renew listing', 'sabai-directory'),
                        );
                    }
                }
                if (!empty($plans['addon'])
                    && $this->_application->PaidListings_Plan($listing) // must have a plan selected to order add-ons
                ) {
                    if (empty($expires_at) // this listing never expires
                        || $expires_at > time() // has not yet expired
                    ) {
                        $actions['addons'] = array(
                            'url' => $this->_application->Url('/' . $this->_application->getAddon('Directory')->getSlug('dashboard') . '/order-listing-addon', array('listing_id' => $listing->getId())),
                            'icon' => 'plus',
                            'title' => __('Order add-ons', 'sabai-directory'),
                        );
                    }
                }
            }
        }
        $actions['orders'] = array(
            'url' => $this->_application->Url('/' . $this->_application->getAddon('Directory')->getSlug('dashboard') . '/orders', array('entity_id' => $listing->getId())),
            'icon' => 'list-alt',
            'title' => __('View orders', 'sabai-directory'),
        );
    }
 
    public function onEntityUpdateContentEntitySuccess($bundle, $entity, $oldEntity, $values, $extraArgs)
    {
        if ($bundle->type !== 'directory_listing'
            || !isset($values['content_post_status']) // content status changed?
            || !($oldEntity->isPending() && $entity->isPublished()) // chanegd from pending to published?
        ) {
            return;
        }
        
        // Apply features if any pending
        $this->_application->PaidListings_ApplyFeatures($entity);
    }
    public function onFormBuildDirectoryAdminClaimsSettings(&$form, &$storage)
    {
        $form['duration_expiration']['duration']['#type'] = 'hidden';
        $form['process']['auto_approve_new']['#title'] = __('Automatically approve claims for new listings when payment is complete', 'sabai-directory');
        $form['process']['auto_approve']['#title'] = __('Automatically approve claims for existing listings when payment is complete', 'sabai-directory');
    }
    
    public function onFormBuildDirectoryAdminListingclaims(&$form, &$storage)
    {
        if (empty($form['claims']['#options'])) return;
        
        $claim_ids = array_keys($form['claims']['#options']);
        $order_ids = $this->_application->getModel(null, 'PaidListings')
            ->getGateway('OrderItem')
            ->getOrderIdsByMeta('claim_id', $claim_ids);
        $form['claims']['#header']['order'] = __('Order', 'sabai-directory');
        foreach ($claim_ids as $claim_id) {
            if (!isset($order_ids[$claim_id])) {
                continue;
            }
            $order_id = $order_ids[$claim_id];
            $form['claims']['#options'][$claim_id]['order'] = $this->_application->LinkTo(
                '#' . str_pad($order_id, 5, 0, STR_PAD_LEFT),
                $this->_application->Url('/' . strtolower($form['#bundle']->addon) . '/orders', array('order_id' => $order_id))
            );
        }
    }
    
    public function onContentAdminPostsDirectoryListingLinksFilter(&$links, $entity, $status)
    {
        $links[] = $this->_application->LinkTo(__('View Orders', 'sabai-directory'), $this->_application->Url('/' . strtolower($this->_application->Entity_Addon($entity)->getName()) . '/orders', array('entity_id' => $entity->getId())));
    }
    
    public function isInstallable()
    {
        if (!parent::isInstallable()) return false;
        
        $required_addons = array(
            'Directory' => '1.2.0',
            'PaidListings' => '1.2.0',
        );
        return $this->_application->CheckAddonVersion($required_addons);
    }
        
    public function onPaidDirectoryListingsInstallSuccess(Sabai_Addon $addon)
    {
        // Create plans for directory listings
        $this->_createDefaultPlans('directory_listing');
        // Update max num items of category fields
        $this->_application->Entity_Field('directory_listing', 'directory_category')->setFieldMaxNumItems(0);
        foreach ($this->_application->getModel('Addon', 'System')->parentAddon_is('Directory')->fetch() as $addon) {
            $entity_bundle_name = $this->_application->getAddon($addon->name)->getListingBundleName();
            $this->_createDefaultPlans($entity_bundle_name);
            // Update max num items of category fields
            $this->_application->Entity_Field($entity_bundle_name, 'directory_category')->setFieldMaxNumItems(0);
        }
        $this->_application->getModel(null, 'Entity')->commit();
    }
    
    public function onPaidDirectoryListingsUpgradeSuccess(Sabai_Addon $addon, $log, $previousVersion)
    {
        $this->_application->PaidDirectoryListings_UpgradeAddon($log, $previousVersion);
    }
    
    public function onEntityLoadFieldValuesFilter(&$fieldValues, $entity, $bundle, $fields, $cache)
    {        
        if (!$cache
            || empty($fieldValues['directory_claim'])
            || empty($fieldValues['paidlistings_plan'][0]['plan_id'])
            || (!$plan = $this->_application->PaidListings_Plan($fieldValues['paidlistings_plan'][0]['plan_id']))
            || (null === $fields_allowed = @$plan->features['paiddirectorylistings_claim']['fields'])
        ) return;
        
        $fieldValues = $this->_application->Directory_FilterFieldValues($fieldValues, $fields_allowed);
    }
    
    public function onEntityCreateBundlesSuccess($entityType, $bundles)
    {
        $reload_routes = false;
        foreach ($bundles as $bundle) {
            if ($bundle->type !== 'directory_listing') continue;
            
            $reload_routes = true;
            $this->_createDefaultPlans($bundle->name);
            $this->_application->Entity_Field($bundle->name, 'directory_category')->setFieldMaxNumItems(0)->commit();
        }  
        if ($reload_routes) {
            $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true);
        }
    }
    
    public function onEntityUpdateBundlesSuccess($entityType, $bundles)
    {
        $reload_routes = false;
        foreach ($bundles as $bundle) {
            if ($bundle->type !== 'directory_listing') continue;
            
            $reload_routes = true;
            break;
        }  
        if ($reload_routes) {
            $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true);
        }
    }
    
    public function onEntityDeleteBundlesSuccess($entityType, $bundles)
    {
        $this->onEntityUpdateBundlesSuccess($entityType, $bundles);
    }
    
    public function paidListingsGetEntityBundleTypes()
    {
        return array('directory_listing');
    }
    
    public function onDirectoryLeadAddedNotificationRecipientsFilter(&$recipients, $listing, $owner)
    {
        if (!$owner
            || (!$plan = $this->_application->PaidListings_Plan($listing))
        ) {
            return;
        }
        if (!empty($plan->features['paiddirectorylistings_leads']['enable'])) {
            $conf = $plan->features['paiddirectorylistings_leads'];
        } elseif (!empty($listing->paidlistings_plan[0]['addon_features']['paiddirectorylistings_leads']['enable'])) { 
            $conf = $listing->paidlistings_plan[0]['addon_features']['paiddirectorylistings_leads'];
        } else {
            return;
        }
        if (empty($conf['to_owner'])) {
            unset($recipients['owner']);
        }
        if (empty($conf['to_contact'])) {
            unset($recipients['contact']);
        }        
    }
    
    public function onFormBuildFielduiAdminCreateField(&$form, &$storage)
    {
        if (!isset($storage['field_widget'])) return;
        
        $this->_onFormBuildFielduiAdminField($form, $storage);
    }
    
    public function onFormBuildFielduiAdminEditField(&$form, &$storage)
    {
        $field_options = $this->_application->Directory_FieldOptions($form['#bundle'], true);
        // Check if the field can be enabled/disabled and not disabled by default
        $field_name = $form['#field']->getFieldName();
        if (isset($field_options[0][$field_name]) && !in_array($field_name, $field_options[1])) {
            $plans = $this->_application->PaidListings_ActivePlans($form['#bundle'], 'base');
            if (!empty($plans)) {
                $defaults = array();
                foreach ($plans as $plan) {
                    if (!isset($plan->features['paiddirectorylistings_claim']['fields']) // can be null if upgrading
                        || in_array($field_name, $plan->features['paiddirectorylistings_claim']['fields'])
                    ) {
                        $defaults[] = $plan->id;
                    }
                }
                $this->_onFormBuildFielduiAdminField($form, $storage, $defaults);
            }
        }
    }
    
    public function _onFormBuildFielduiAdminField(&$form, &$storage, array $defaults = null)
    {
        $plans = $this->_application->PaidListings_ActivePlans($form['#bundle'], 'base');
        if (!empty($plans)) {
            $form['basic']['paiddirectorylistings_plans'] = array(
                '#type' => 'checkboxes',
                '#options' => $plans,
                '#default_value' => isset($defaults) ? $defaults : array_keys($plans),
                '#title' => __('Enable this field for the following paid listing plans', 'sabai-directory'),
                '#weight' => 100,
            );
            $form['#submit'][Sabai_Addon_Form::FORM_CALLBACK_WEIGHT_DEFAULT - 1][] = array($this, 'submitFielduiAdminField');
        }
    }
    
    public function submitFielduiAdminField($form)
    {
        $enabled_plans = empty($form->values['paiddirectorylistings_plans']) ? array() : $form->values['paiddirectorylistings_plans'];
        if ($plans = $this->_application->PaidListings_ActivePlans($form->settings['#bundle'], 'base')) {
            if (isset($form->settings['#field'])) {
                $field_name = $form->settings['#field']->getFieldName();
            } else {
                $field_name = isset($form->settings['#field_name']) ? $form->settings['#field_name'] : 'field_' . $form->values['name'];
            }
            foreach ($plans as $plan) {
                $features = (array)@$plan->features;
                if (!isset($features['paiddirectorylistings_claim']['fields'])) {
                    // Enable all fields since the setting has not been initialized
                    $field_options = $this->_application->Directory_FieldOptions($form->settings['#bundle'], false);
                    // Exclude default fields
                    foreach ($field_options[1] as $default_field) {
                        unset($field_options[0][$default_field]);
                    }
                    $features['paiddirectorylistings_claim']['fields'] = array_keys($field_options[0]);
                }
                if (!empty($features['paiddirectorylistings_claim']['fields'])
                    && in_array($field_name, $features['paiddirectorylistings_claim']['fields'])
                ) {
                    if (!in_array($plan->id, $enabled_plans)) {
                        foreach (array_keys($features['paiddirectorylistings_claim']['fields'], $field_name) as $key) {
                            unset($features['paiddirectorylistings_claim']['fields'][$key]);
                        }
                        $plan->features = $features;
                        $commit = true;
                    }
                } else {
                    if (in_array($plan->id, $enabled_plans)) {
                        $features['paiddirectorylistings_claim']['fields'][] = $field_name;
                        $plan->features = $features;
                        $commit = true;
                    }
                }
            }
            if (!empty($commit)) {
                $this->_application->getModel(null, 'PaidListings')->commit();
            }
        }
        unset($form->values['paiddirectorylistings_plans']);
    }
    
    public function onDirectoryCustomTabRouteDefaultSettingsFilter(&$default, $addon)
    {
        $default['callback_addon'] = $this->_name;
    }
    
    public function onSabaiRunCron($lastRunTimestamp, $logs)
    {
        if (time() - $lastRunTimestamp < 86400) return; // Run this cron once a day

        // Fetch listings to un-feature
        $listings = $this->_application->Entity_Query('content')
            ->propertyIs('post_entity_bundle_type', 'directory_listing')
            ->fieldIsOrSmallerThan('content_featured', time(), 'expires_at')
            ->fieldIsGreaterThan('content_featured', 0, 'expires_at') // never expires if expires_at is 0
            ->fetch();
        $count = 0;
        foreach ($listings as $listing) {
            $this->_application->Entity_Save($listing, array('content_featured' => false));
            ++$count;
        }
        $logs[] = sprintf(__('Unfeatured %d listings', 'sabai-directory'), $count);
    }
}
