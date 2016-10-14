<?php
class Sabai_Addon_PaidDirectoryListings_Helper_CreateDefaultPlans extends Sabai_Helper
{    
    public function help(Sabai $application, $entityBundleName)
    {
        $model = $application->getModel(null, 'PaidListings');
        $plan = $model->create('Plan')->markNew();
        $plan->name = __('7-day Free Trial', 'sabai-directory');
        $plan->description = __('Try and see if listing on this site works for your business.', 'sabai-directory');
        $plan->type = 'base';
        $plan->entity_bundle_name = $entityBundleName;
        $plan->price = 0.00;
        $plan->currency = 'USD';
        $plan->features = array(
            'paiddirectorylistings_claim' => array('enable' => true, 'duration' => 7),
            'paiddirectorylistings_reviews' => array('enable' => true),
        );
        $plan->active = true;
        
        $plan2 = $model->create('Plan')->markNew();
        $plan2->name = __('30-day Basic Listing', 'sabai-directory');
        $plan2->description = __('Claim your listing for 30 days.', 'sabai-directory');
        $plan2->type = 'base';
        $plan2->entity_bundle_name = $entityBundleName;
        $plan2->price = 15.00;
        $plan2->currency = 'USD';
        $plan2->weight = 1;
        $plan2->features = array(
            'paiddirectorylistings_claim' => array('enable' => true, 'duration' => 30),
            'paiddirectorylistings_reviews' => array('enable' => true),
        );
        $plan2->active = true;
        
        $plan4 = $model->create('Plan')->markNew();
        $plan4->name = __('30-day Featured Listing Add-on', 'sabai-directory');
        $plan4->description = __('Get your listing featured on homepage for 30 days.', 'sabai-directory');
        $plan4->type = 'addon';
        $plan4->entity_bundle_name = $entityBundleName;
        $plan4->price = 15.00;
        $plan4->currency = 'USD';
        $plan4->weight = 3;
        $plan4->features = array(
            'paiddirectorylistings_featured' => array('enable' => true, 'duration' => 30),
        );
        $plan4->active = true;
        
        $plan5 = $model->create('Plan')->markNew();
        $plan5->name = __('Contact Form Add-on', 'sabai-directory');
        $plan5->description = __('Add a contact form to your listing page to capture leads.', 'sabai-directory');
        $plan5->type = 'addon';
        $plan5->entity_bundle_name = $entityBundleName;
        $plan5->price = 15.00;
        $plan5->currency = 'USD';
        $plan5->weight = 3;
        $plan5->features = array(
            'paiddirectorylistings_leads' => array('enable' => true),
        );
        $plan5->active = true;
        
        $model->commit();
    }
}