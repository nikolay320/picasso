<?php
class Sabai_Addon_PaidDirectoryListings_Helper_UpgradeAddon extends Sabai_Helper
{    
    public function help(Sabai $application, $log, $previousVersion)
    {
        if (version_compare($previousVersion, '1.3.0dev2', '<')) {            
            if (!$application->isAddonLoaded('PaidListings')) return;
            
            $model = $application->getModel(null, 'PaidListings');
            $plans = $model->getGateway('Plan');
            $plans->updateByCriteria(new SabaiFramework_Criteria_Empty(), array('plan_entity_bundle_name' => 'directory_listing'));
            $plans->updateByCriteria($model->createCriteria('Plan')->type_is('directory_listing'), array('plan_type' => 'base', 'plan_onetime' => true));
            $plans->deleteByCriteria($model->createCriteria('Plan')->type_is('directory_listing_renewal'));
            $plans->updateByCriteria($model->createCriteria('Plan')->type_is('directory_listing_addon'), array('plan_type' => 'addon', 'plan_onetime' => true));
            $application->Entity_Field('directory_listing', 'directory_category')->setFieldMaxNumItems(0);
            if (!$currency = $application->getAddon('PaidDirectoryListings')->getConfig('paypal', 'currency')) {
                $currency = 'USD';
            }
            $plans->updateByCriteria(new SabaiFramework_Criteria_Empty(), array('plan_currency' => $currency));
            $new_plan_ids = array();
            $current_plans = $application->getModel('Plan', 'PaidListings')->fetch()->getArray();
            foreach ($application->getModel('Addon', 'System')->parentAddon_is('Directory')->fetch() as $addon) {
                $entity_bundle_name = $application->getAddon($addon->name)->getListingBundleName();
                foreach ($current_plans as $plan) {
                    if (isset($plan->features['paiddirectorylistings_renew'])) {
                        $plan_features = $plan->features;
                        $plan_features['paiddirectorylistings_claim'] = $plan_features['paiddirectorylistings_renew'];
                        unset($plan_features['paiddirectorylistings_renew']);
                        $plan->features = $plan_features;
                    }
                    $new_plan = clone $plan;
                    $new_plan->id = null;
                    $new_plan->entity_bundle_name = $entity_bundle_name;
                    $new_plan->currency = $currency;
                    $new_plan->markNew()->commit();
                    $new_plan_ids[$entity_bundle_name][$plan->id] = $new_plan->id;
                }
                $application->Entity_Field($entity_bundle_name, 'directory_category')->setFieldMaxNumItems(0);
            }
            $application->getModel(null, 'PaidListings')->commit();
            $application->getModel(null, 'Entity')->commit();
            $db = $application->getDB();
            if (!empty($new_plan_ids)) {
                foreach ($new_plan_ids as $entity_bundle_name => $_new_plan_ids) {
                    foreach ($_new_plan_ids as $old_plan_id => $new_plan_id) {
                        $sql = sprintf(
                            'UPDATE %1$spaidlistings_order o LEFT JOIN %1$scontent_post p ON p.post_id = o.order_entity_id SET o.order_plan_id = %2$d WHERE p.post_entity_bundle_name = %3$s AND o.order_plan_id = %4$d',
                            $db->getResourcePrefix(),
                            $new_plan_id,
                            $db->escapeString($entity_bundle_name),
                            $old_plan_id
                        );
                        $db->exec($sql);
                    }
                }
            }
            $sql = sprintf('
                INSERT INTO %1$sentity_field_paidlistings_plan (entity_type, bundle_id, entity_id, plan_id, addon_features, recurring_payment_id) 
                  SELECT "content", b.bundle_id, o.order_entity_id, MAX(o.order_plan_id), "a:0:{}", ""
                  FROM %1$spaidlistings_order o
                  LEFT JOIN %1$scontent_post p ON p.post_id = o.order_entity_id
                  LEFT JOIN %1$sentity_bundle b ON b.bundle_name = p.post_entity_bundle_name
                  GROUP BY o.order_entity_id',
                $db->getResourcePrefix()
            );
            $db->exec($sql);
            return;
        }
        
        if (version_compare($previousVersion, '1.3.7', '>=') && version_compare($previousVersion, '1.3.9', '<')) {
            $model = $application->getModel(null, 'PaidListings');
            foreach ($model->Plan->fetch()->getArray() as $plan) {
                if (!empty($plan->features['paiddirectorylistings_tabs']['tabs'])) {
                    if (empty($plan->features['paiddirectorylistings_claim'])) {
                        $plan->features['paiddirectorylistings_claim'] = array();
                    }
                    $plan->features['paiddirectorylistings_claim']['tabs'] = $plan->features['paiddirectorylistings_tabs']['tabs'];
                    unset($plan->features['paiddirectorylistings_tabs']['tabs']);
                }
            }
            $model->commit();
        }
    }
}
