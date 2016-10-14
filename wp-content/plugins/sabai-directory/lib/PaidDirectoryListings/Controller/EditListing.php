<?php
require_once dirname(dirname(dirname(__FILE__))) . '/Directory/Controller/EditListing.php';

class Sabai_Addon_PaidDirectoryListings_Controller_EditListing extends Sabai_Addon_Directory_Controller_EditListing
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_doGetFormSettings($context, $formStorage);
        
        if ($plan = $this->PaidListings_Plan($context->entity)) {
            // Limit location/category numbers?    
            foreach (array(
                'directory_location' => 'paiddirectorylistings_locations',
                'directory_category' => 'paiddirectorylistings_categories'
            ) as $field_name => $addon_feature_name) {
                if (isset($form[$field_name][0])) {
                    if (!@$plan->features['paiddirectorylistings_claim'][$field_name]['limit']) continue;
                    
                    $limit_num = (int)@$plan->features['paiddirectorylistings_claim'][$field_name]['num'];
                    
                    // Add numbers added by add-ons
                    $limit_num += (int)@$context->entity->paidlistings_plan[0]['addon_features'][$addon_feature_name]['num'];
                    
                    if (empty($limit_num)) {
                        unset($form[$field_name]);
                        continue;
                    }                    
                    if (isset($form[$field_name]['_add'])) {
                        unset($form[$field_name]['_add']);
                    } else {
                        foreach (array_keys($form[$field_name]) as $key) {
                            $current_num = 0;
                            foreach (array_keys($form[$field_name]) as $key) {
                                if (is_numeric($key)) {
                                    ++$current_num;
                                    if ($key + 1 > $limit_num) {
                                        // over limit num
                                        unset($form[$field_name][$key]);
                                    }
                                }
                            }
                            if ($current_num < $limit_num) {
                                $limit_num = $current_num;
                            }
                        }
                    }
                    for ($i = 1; $i < $limit_num; $i++) {
                        if (!isset($form[$field_name][$i])) {
                            $form[$field_name][$i] = $form[$field_name][0];
                            $form[$field_name][$i]['#default_value'] = null;
                            $form[$field_name][$i]['#required'] = false;
                        }
                    }
                    $this->_maxNumValues[$field_name] = $limit_num;
                }
            }
            // Limit fields?
            if (null !== $limit_fields = @$plan->features['paiddirectorylistings_claim']['fields']) {
                $form = $this->Directory_FilterFormFields($form, $limit_fields);
            }
            // Limit photo numbers?
            if (isset($form['directory_photos'])) {
                if (@$plan->features['paiddirectorylistings_claim']['directory_photos']['limit']) {
                    $limit_num = (int)@$plan->features['paiddirectorylistings_claim']['directory_photos']['num'];
                        + (int)@$context->entity->paidlistings_plan[0]['addon_features']['paiddirectorylistings_photos']['num'];
                    if (empty($limit_num)) {
                        unset($form['directory_photos']);
                    } else {
                        $form['directory_photos']['#max_num_files'] = $limit_num;
                    }
                }
            }
        }
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        // Preserve oroginal values
        if (isset($form->settings['directory_contact']) && $context->entity->directory_contact) {
            $form->values['directory_contact'][0] += $context->entity->directory_contact[0];
        }
        if (isset($form->settings['directory_social']) && $context->entity->directory_social) {
            $form->values['directory_social'][0] += $context->entity->directory_social[0];
        }

        parent::submitForm($form, $context);
    }
}
