<?php
require_once SABAI_PACKAGE_PAIDLISTINGS_PATH . '/lib/PaidListings/IFeature.php';

class Sabai_Addon_PaidDirectoryListings_Feature implements Sabai_Addon_PaidListings_IFeature
{
    protected $_addon, $_name;
    
    public function __construct(Sabai_Addon_PaidDirectoryListings $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }
    
    public function paidListingsFeatureGetInfo($key = null)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
                 $info = array(
                    'label' => __('Claim Listing', 'sabai-directory'),
                    'default_settings' => array(
                        'duration' => 30,
                        'photos' => array(
                            'limit' => false,
                            'num' => 0,
                        ),
                        'category' => array(
                            'limit' => false,
                            'num' => 0,
                        ),
                        'tabs' => null,
                    ),
                    'bundles' => array('directory_listing' => array('base')),
                    'is_default' => true,
                    'onetime_only' => array('duration'),
                );
                break;
            case 'paiddirectorylistings_featured':
                $info = array(     
                    'label' => __('Featured Listing', 'sabai-directory'),
                    'default_settings' => array(
                        'enable' => true,
                        'priority' => 5,
                        'duration' => 7,
                    ),
                    'bundles' => array('directory_listing' => array('base', 'addon')),
                );
                break;
            case 'paiddirectorylistings_locations':
                $info = array(     
                    'label' => __('Additional Locations', 'sabai-directory'),
                    'default_settings' => array(
                        'enable' => true,
                        'num' => 1,
                    ),
                    'bundles' => array('directory_listing' => array('addon')),
                    'is_default_addon' => true,
                );
                break;
            case 'paiddirectorylistings_categories':
                $info = array(     
                    'label' => __('Additional Categories', 'sabai-directory'),
                    'default_settings' => array(
                        'enable' => true,
                        'num' => 5,
                    ),
                    'bundles' => array('directory_listing' => array('addon')),
                    'is_default_addon' => true,
                );
                break;
            case 'paiddirectorylistings_photos':
                $info = array(     
                    'label' => __('Additional Photos', 'sabai-directory'),
                    'default_settings' => array(
                        'enable' => true,
                        'num' => 5,
                    ),
                    'bundles' => array('directory_listing' => array('addon')),
                    'is_default_addon' => true,
                );
                break;
            case 'paiddirectorylistings_reviews':
                $info = array(     
                    'label' => _x('Reviews', 'paid plan feature', 'sabai-directory'),
                    'default_settings' => array(
                        'enable' => true,
                    ),
                    'bundles' => array('directory_listing' => array('base', 'addon')),
                );
                break;
            case 'paiddirectorylistings_leads':
                $info = array(     
                    'label' => __('Contact Us Form', 'sabai-directory'),
                    'default_settings' => array(
                        'enable' => true,
                        'to_owner' => true,
                    ),
                    'bundles' => array('directory_listing' => array('base', 'addon')),
                );
                break;
        }
        
        return isset($key) ? $info[$key] : $info;
    }
    
    public function paidListingsFeatureGetSettingsForm($entityBundleName, array $settings, array $parents)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
                $field_options = $this->_addon->getApplication()->Directory_FieldOptions($entityBundleName, false);
                $form = array(
                    '#title' => __('Basic Feature Settings', 'sabai-directory'),
                    'duration' => array(
                        '#type' => 'number',
                        '#title' => __('Duration', 'sabai-directory'),
                        '#description' => __('Enter the number of days listings will be claimed (0 = unlimited). This is applied to one-time payment only.', 'sabai-directory'),
                        '#default_value' => intval($settings['duration']),
                        '#size' => 7,
                        '#integer' => true,
                        '#field_suffix' => __('day(s)', 'sabai-directory'),
                        '#weight' => 1,
                        '#min_value' => 0,
                    ),
                    'fields' => array(
                        '#title' => __('Fields', 'sabai-directory'),
                        '#type' => 'checkboxes',
                        '#options' => $field_options[0],
                        '#options_disabled' => $field_options[1],
                        '#default_value' => isset($settings['fields']) ? array_merge($settings['fields'], $field_options[1]) : array_keys($field_options[0]),
                    ),
                );
                $weight = 2;
                $limitable_fields = array('directory_category', 'directory_photos');
                if ($this->_addon->getApplication()->isAddonLoaded('GoogleMaps')) {
                    $limitable_fields[] = 'directory_location';
                }
                foreach ($limitable_fields as $field_name) {
                    $field = $this->_addon->getApplication()->Entity_Field($entityBundleName, $field_name);
                    $weight += 5;
                    if (!$field->getFieldDisabled()) {
                        if (!$max = $field->getFieldMaxNumItems()) {
                            $max = 10;
                        }
                        $form[$field_name] = array(
                            '#title' => $field->getFieldLabel(),
                            '#class' => 'sabai-form-group',
                            '#collapsible' => false,
                            'limit' => array(
                                '#type' => 'checkbox',
                                '#title' => sprintf(__('Limit the number of %s allowed in the frontend', 'sabai-directory'), $field->getFieldLabel()),
                                '#default_value' => !empty($settings[$field_name]['limit']),
                            ),
                            'num' => array(
                                '#type' => 'select',
                                '#options' => array_combine(range(0, $max), range(0, $max)),
                                '#default_value' => isset($settings[$field_name]['num']) ? $settings[$field_name]['num'] : 0,
                                '#required' => array(array($this, 'isFieldRequired'), array($parents + array($field_name), 'limit')),
                                '#weight' => $weight,
                                '#states' => array(
                                    'visible' => array(
                                        sprintf('input[name="%s[%s][limit][]"]', $this->_addon->getApplication()->Form_FieldName($parents), $field_name) => array('type' => 'checked', 'value' => 1)
                                    ),
                                ),
                            ),
                        );
                    } else {
                        $form[$field_name] = array(
                            'limit' => array(
                                '#type' => 'hidden',
                                '#value' => !empty($settings[$field_name]['limit']),
                            ),
                            'num' => array(
                                '#type' => 'hidden',
                                '#value' => isset($settings[$field_name]['num']) ? $settings[$field_name]['num'] : 0,
                            ),
                        );
                    }
                }
                
                $application = $this->_addon->getApplication();
                $addon = $application->Entity_Addon($entityBundleName);
                $listing_tabs = $addon->getConfig('display', 'listing_tabs');
                if (isset($listing_tabs['options'])
                    && ($options = array_diff_key($listing_tabs['options'], $addon->getListingDefaultTabs()))
                ) {
                    $form['tabs'] = array(
                        '#title' => __('Single listing page tabs', 'sabai-directory'),
                        '#type' => 'checkboxes',
                        '#options' => $options,
                        '#default_value' => $settings['tabs'], 
                    );
                }
                
                return $form;
            case 'paiddirectorylistings_featured':
                return array(
                    '#title' => __('Featured Listing', 'sabai-directory'),
                    'enable' => array(
                        '#title' => __('Display as featured', 'sabai-directory'),
                    ),
                    'priority' => array(
                        '#type' => 'select',
                        '#title' => __('Priority', 'sabai-directory'),
                        '#options' => array(
                            9 => _x('High', 'priority', 'sabai-directory'),
                            5 => _x('Normal', 'priority', 'sabai-directory'),
                            1 => _x('Low', 'priority', 'sabai-directory'),
                        ),
                        '#default_value' => $settings['priority'],
                        '#required' => array(array($this, 'isFieldRequired'), array($parents)),
                    ),
                    'duration' => array(
                        '#type' => 'number',
                        '#description' => __('Enter the number of days listings will be featured on homepage.', 'sabai-directory'),
                        '#default_value' => $settings['duration'],
                        '#size' => 7,
                        '#integer' => true,
                        '#title' => __('Duration', 'sabai-directory'),
                        '#field_suffix' => __('day(s)', 'sabai-directory'),
                        '#required' => array(array($this, 'isFieldRequired'), array($parents)),
                        '#min_value' => 0,
                    ),
                );
            case 'paiddirectorylistings_locations':
                return array(
                    'num' => array(
                        '#type' => 'number',
                        '#default_value' => $settings['num'],
                        '#size' => 5,
                        '#integer' => true,
                        '#required' => array(array($this, 'isFieldRequired'), array($parents)),
                        '#min_value' => 1,
                        '#max_value' => 99,
                    ),
                );
            case 'paiddirectorylistings_categories':
                return array(
                    'num' => array(
                        '#type' => 'number',
                        '#default_value' => $settings['num'],
                        '#size' => 5,
                        '#integer' => true,
                        '#required' => array(array($this, 'isFieldRequired'), array($parents)),
                        '#min_value' => 1,
                        '#max_value' => 99,
                    ),
                );
            case 'paiddirectorylistings_photos':
                return array(
                    'num' => array(
                        '#type' => 'number',
                        '#default_value' => $settings['num'],
                        '#size' => 5,
                        '#integer' => true,
                        '#required' => array(array($this, 'isFieldRequired'), array($parents)),
                        '#min_value' => 1,
                        '#max_value' => 99,
                    ),
                );
            case 'paiddirectorylistings_leads':
                return array(
                    'enable' => array(
                        '#title' => __('Allow users to send messages via contact us form', 'sabai-directory'),
                    ),
                    'to_owner' => array(
                        '#type' => 'checkbox',
                        '#default_value' => !empty($settings['to_owner']),
                        '#title' => __('Send messages to owner email address', 'sabai-directory'),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[enable][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => 1),
                            ),
                        ),
                    ),
                    'to_contact' => array(
                        '#type' => 'checkbox',
                        '#default_value' => !empty($settings['to_contact']),
                        '#title' => __('Send messages to contact info email address (if any)', 'sabai-directory'),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[enable][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => 1),
                            ),
                        ),
                    ),
                );
            case 'paiddirectorylistings_reviews':
                return array(
                    'enable' => array(
                        '#title' => __('Allow users to post reviews', 'sabai-directory'),
                    ),
                );
        }
    }
    
    public function isFieldRequired($form, $parents, $dependee = 'enable')
    {
        $values = $form->getValue($parents);
        return !empty($values[$dependee]);
    }
    
    public function paidListingsFeatureGetOrderDescription(Sabai_Addon_PaidListings_Model_OrderItem $orderItem, array $settings)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
                // Claims ordered with recurring payments do not expire
                $duration = strlen($orderItem->Order->payment_type) ? 0 : $orderItem->getMeta('duration');
                return $duration
                    ? sprintf(__('Claim ownership of listing for %d days', 'sabai-directory'), $duration)
                    : __('Claim ownership of listing for unlimited number of days', 'sabai-directory');
            case 'paiddirectorylistings_featured':
                return ($duration = $orderItem->getMeta('duration'))
                    ? sprintf(__('Feature listing on homepage for %d days', 'sabai-directory'), $duration)
                    : __('Feature listing on home page for unlimited number of days', 'sabai-directory');
            case 'paiddirectorylistings_locations':
                return sprintf(__('Allow %d extra location addresses', 'sabai-directory'), $orderItem->getMeta('num'));
            case 'paiddirectorylistings_categories':
                return sprintf(__('Allow selection of %d extra categories', 'sabai-directory'), $orderItem->getMeta('num'));
            case 'paiddirectorylistings_photos':
                return sprintf(__('Allow upload of %d extra photos', 'sabai-directory'), $orderItem->getMeta('num'));
            case 'paiddirectorylistings_leads':
                return __('Allow users to contact the owner via contact us form', 'sabai-directory');
            case 'paiddirectorylistings_reviews':
                return __('Allow users to post reviews', 'sabai-directory');
        }
    }
    
    public function paidListingsFeatureApply(Sabai_Addon_Entity_Entity $entity, Sabai_Addon_PaidListings_Model_OrderItem $orderItem)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
                // Claims ordered with recurring payments do not expire
                $duration = strlen($orderItem->Order->payment_type) ? 0 : $orderItem->getMeta('duration');
                $this->_addon->getApplication()->Directory_ClaimListing($entity, $orderItem->Order->User, $duration);
                return true;
            case 'paiddirectorylistings_featured':
                $duration = $orderItem->getMeta('duration');
                $featured_at = time();
                $expires_at = $duration ? time() + $duration * 86400 : 0;
                if ($entity_featured = $entity->getFieldValue('content_featured')) {
                    // Already featured
                    if ($entity_featured[0]['expires_at'] == 0) {
                        // Already featured indefinitely
                        $expires_at = 0;
                    } elseif ($entity_featured[0]['expires_at'] > time()) {
                        $featured_at = $entity_featured[0]['featured_at'];
                        $expires_at = $duration
                            ? $entity_featured[0]['expires_at'] + $duration * 86400  // extend expiration time
                            : 0;
                    }
                }
                $this->_addon->getApplication()->Entity_Save($entity, array('content_featured' => array(
                    'value' => $orderItem->getMeta('priority'),
                    'featured_at' => $featured_at,
                    'expires_at' => $expires_at
                )));
                return true;
            default:
                return true;
        }
    }
    
    public function paidListingsFeatureUnapply(Sabai_Addon_Entity_Entity $entity, Sabai_Addon_PaidListings_Model_OrderItem $orderItem)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
                if ((!$current_claim = $entity->getSingleFieldValue('directory_claim'))
                    || $current_claim['claimed_by'] != $orderItem->Order->user_id
                ) {
                    // No valid claim
                    return;
                }
                if (empty($current_claim['expires_at'])) {
                    $value = false;
                } else {
                    $expires_at = $current_claim['expires_at'] - $orderItem->getMeta('duration') * 86400; // reset extended expiration time
                    if ($expires_at < time()) {
                        $value = false;
                    } else {
                        $value = array('claimed_by' => $orderItem->Order->user_id, 'claimed_at' => $current_claim['claimed_at'], 'expires_at' => $expires_at);
                    }
                }
                $this->_addon->getApplication()->Entity_Save($entity, array('directory_claim' => $value));
                return true;
            case 'paiddirectorylistings_featured':
                if (!$entity_featured = $entity->getFieldValue('content_featured')) {
                    return;
                }
                if ($entity_featured[0]['expires_at'] > time()
                    && ($duration = $orderItem->getMeta('duration'))
                ) {
                    $expires_at = $entity_featured[0]['expires_at'] - $duration * 86400; // undo expiration time
                    if ($expires_at < time()) {
                        $value = false;
                    } else {
                        $value = array('value' => $entity_featured[0]['value'], 'featured_at' => $entity_featured[0]['featured_at'], 'expires_at' => $expires_at);
                    }
                } else {
                    $value = false;
                }
                $this->_addon->getApplication()->Entity_Save($entity, array('content_featured' => $value));
                return true;
            default:
                return true;
        }
    }
    
    public function paidListingsFeatureIsAppliable(Sabai_Addon_Entity_Entity $entity, Sabai_Addon_PaidListings_Model_OrderItem $orderItem, $isManual = false)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
                if ($orderItem->getMeta('claim_renew')) {
                    return $entity->isPublished();
                }
                $application = $this->_addon->getApplication();
                if ((!$claim_id = $orderItem->getMeta('claim_id'))
                    || (!$claim = $application->getModel('Claim', 'Directory')->fetchById($claim_id))
                ) {
                    return false;
                }
                if ($claim->status === 'pending_payment') {
                    $claim_approval_settings = $application->Entity_Addon($entity)->getConfig('claims', 'process');
                    // Since payment should have been completed at this stage, we can safely change the status.
                    if (!empty($claim_approval_settings[$orderItem->getMeta('claim_new') ? 'auto_approve_new' : 'auto_approve'])) {
                        $claim->status = 'approved';
                        $is_auto_approved = true;
                    } else {
                        $claim->status = 'pending';
                    }
                    $claim->commit();
                    $application->Action('directory_listing_claim_status_change', array($claim));
                }
                if ($claim->status === 'approved') {
                    if ($entity->isPublished()) {
                        // Delete auto approved claim?
                        if (!empty($is_auto_approved) && !empty($claim_approval_settings['delete_auto_approved'])) {
                            $claim->reload()->markRemoved()->commit();
                        }
                        return true;
                    }
                    // Publish listing if the author has the permission to add listings without admin approval
                    if ($application->HasPermission($entity->getBundleName() . '_add2', $application->Entity_Author($entity))) {
                        $application->Content_PublishPosts($entity);
                        // Do not return true here since publishing the listing will trigger PaidListings_ApplyFeatures
                    }
                }
                return false;
            default:
                return $entity->isPublished();
        }
    }
    
    public function paidListingsFeatureOnOrder($planType, array $currentSettings, array $orderSettings)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
            case 'paiddirectorylistings_featured':
                return false;
            case 'paiddirectorylistings_locations':
            case 'paiddirectorylistings_categories':
            case 'paiddirectorylistings_photos':
                if (isset($currentSettings['num'])) {
                    $orderSettings['num'] += $currentSettings['num'];
                }
                return $orderSettings;
            case 'paiddirectorylistings_leads':
            case 'paiddirectorylistings_reviews':
                if ($planType !== 'addon') return false;
                $orderSettings['enable'] = true;
                return $orderSettings;
        }
    }
    
    public function paidListingsFeatureGetAddonSettingsForm($entityBundleName, array $plans, array $values, array $parents)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_locations':
                return array(
                    'num' => array(
                        '#type' => 'number',
                        '#default_value' => isset($values['num']) ? $values['num'] : 0,
                        '#size' => 5,
                        '#integer' => true,
                        '#min_value' => 0,
                        '#max_value' => 99,
                    ),
                );
            case 'paiddirectorylistings_categories':
                return array(
                    'num' => array(
                        '#type' => 'number',
                        '#default_value' => isset($values['num']) ? $values['num'] : 0,
                        '#size' => 5,
                        '#integer' => true,
                        '#min_value' => 0,
                        '#max_value' => 99,
                    ),
                );
            case 'paiddirectorylistings_photos':
                return array(
                    'num' => array(
                        '#type' => 'number',
                        '#default_value' => isset($values['num']) ? $values['num'] : 0,
                        '#size' => 5,
                        '#integer' => true,
                        '#min_value' => 0,
                        '#max_value' => 99,
                    ),
                );
            case 'paiddirectorylistings_leads':                
                return array(
                    'enable' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Allow users to contact the owner via contact us form', 'sabai-directory'),
                        '#default_value' => !empty($values['enable']),
                    ),
                    'to_owner' => array(
                        '#type' => 'checkbox',
                        '#default_value' => !empty($values['to_owner']),
                        '#title' => __('Send messages to owner email address', 'sabai-directory'),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[enable][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => 1),
                            ),
                        ),
                    ),
                    'to_contact' => array(
                        '#type' => 'checkbox',
                        '#default_value' => !empty($values['to_contact']),
                        '#title' => __('Send messages to contact info email address (if any)', 'sabai-directory'),
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[enable][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => 1),
                            ),
                        ),
                    ),
                );
            case 'paiddirectorylistings_reviews':                
                return array(
                    'enable' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Allow users to post reviews', 'sabai-directory'),
                        '#default_value' => !empty($values['enable']),
                    ),
                );
        }
    }
    
    public function paidListingsFeatureIsOrderable(Sabai_Addon_Entity_Entity $entity, array $settings)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_leads':
            case 'paiddirectorylistings_reviews':
                return empty($entity->paidlistings_plan[0]['addon_features'][$this->_name]['enable']);
            default:
                return true;
        }
    }
    
    public function paidListingsFeatureGetSummary($entityBundleName, array $settings, $paymentType)
    {
        switch ($this->_name) {
            case 'paiddirectorylistings_claim':
                if (empty($settings['enable'])) return false;
                
                $ret = array();
                if ($paymentType === '' && !empty($settings['duration'])) {
                    $ret[] = array('icon' => 'calendar', 'label' => sprintf(_n('%d day', '%d days', $settings['duration'], 'sabai-directory'), $settings['duration']));
                }
                if (!empty($settings['directory_category']['limit'])) {
                    $num = $settings['directory_category']['num'];
                    $ret[] = array('icon' => 'folder-open', 'label' => sprintf(_n('%d category', '%d categories', $num, 'sabai-directory'), $num));
                } else {
                    $field = $this->_addon->getApplication()->Entity_Field($entityBundleName, 'directory_category');
                    if ($num = $field->getFieldMaxNumItems()) {
                        $ret[] = array('icon' => 'folder-open', 'label' => sprintf(_n('%d category', '%d categories', $num, 'sabai-directory'), $num));
                    } else {
                        $ret[] = array('icon' => 'folder-open', 'label' => __('Unlimited categories', 'sabai-directory'));
                    }
                }
                if (!empty($settings['directory_photos']['limit'])) {
                    $num = $settings['directory_photos']['num'];
                    $ret[] = array('icon' => 'photo', 'label' => sprintf(_n('%d photo', '%d photos', $num, 'sabai-directory'), $num));
                } else {
                    $field = $this->_addon->getApplication()->Entity_Field($entityBundleName, 'directory_photos');
                    if ($num = $field->getFieldMaxNumItems()) {
                        $ret[] = array('icon' => 'photo', 'label' => sprintf(_n('%d photo', '%d photos', $num, 'sabai-directory'), $num));
                    } else {
                        $ret[] = array('icon' => 'photo', 'label' => __('Unlimited photos', 'sabai-directory'));
                    }
                }
                if ($this->_addon->getApplication()->isAddonLoaded('GoogleMaps')) {
                    if (!empty($settings['directory_location']['limit'])) {
                        $num = $settings['directory_location']['num'];
                        $ret[] = array('icon' => 'map-marker', 'label' => sprintf(_n('%d location', '%d locations', $num, 'sabai-directory'), $num));
                    } else {
                        $field = $this->_addon->getApplication()->Entity_Field($entityBundleName, 'directory_location');
                        if ($num = $field->getFieldMaxNumItems()) {
                            $ret[] = array('icon' => 'map-marker', 'label' => sprintf(_n('%d location', '%d locations', $num, 'sabai-directory'), $num));
                        } else {
                            $ret[] = array('icon' => 'map-marker', 'label' => __('Unlimited locations', 'sabai-directory'));
                        }
                    }
                }
                return $ret;
                
            case 'paiddirectorylistings_featured':
                $label = $settings['duration'] > 0
                    ? sprintf(_n('Featured %d day', 'Featured %d days', $settings['duration'], 'sabai-directory'), $settings['duration'])
                    : _x('Featured', 'pricing table', 'sabai-directory');
                return empty($settings['enable']) ? false : array(array('icon' => 'star', 'label' => $label));
            case 'paiddirectorylistings_locations':
                return empty($settings['enable']) ? false : array(array('icon' => 'map-marker', 'label' => sprintf(_n('Extra location', 'Extra %d locations', $settings['num'], 'sabai-directory'), $settings['num'])));
            case 'paiddirectorylistings_categories':
                return empty($settings['enable']) ? false : array(array('icon' => 'folder-open', 'label' => sprintf(_n('Extra category', 'Extra %d categories', $settings['num'], 'sabai-directory'), $settings['num'])));
            case 'paiddirectorylistings_photos':
                return empty($settings['enable']) ? false : array(array('icon' => 'photo', 'label' => sprintf(_n('Extra photo', 'Extra %d photos', $settings['num'], 'sabai-directory'), $settings['num'])));
            case 'paiddirectorylistings_leads':
                return empty($settings['enable']) ? false : array(array('icon' => 'envelope', 'label' => __('Contact Us Form', 'sabai-directory')));
            case 'paiddirectorylistings_reviews':
                return empty($settings['enable']) ? false : array(array('icon' => 'pencil', 'label' => _x('Reviews', 'paid plan feature', 'sabai-directory')));
        }
    }
}
