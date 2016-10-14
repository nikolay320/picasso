<?php
class Sabai_Addon_Directory_Controller_EditListing extends Sabai_Addon_Content_Controller_EditPost
{
    protected $_maxNumValues = array();
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {        
        $form = parent::_doGetFormSettings($context, $formStorage);
        
        if ($context->getRequest()->asBool('dashboard')) {
            $this->_cancelUrl = '/' . $this->getAddon('Directory')->getSlug('dashboard');
        }
        
        if (empty($context->entity->directory_claim)) {
            $unclaimed_listing_config = $this->Entity_Addon($context->entity)->getConfig('claims', 'unclaimed');      
            // Limit location/category numbers?    
            foreach (array('directory_location', 'directory_category') as $field_name) {
                if (isset($form[$field_name][0])) {
                    if (empty($unclaimed_listing_config[$field_name]['limit'])) continue;
                    
                    $limit_num = (int)@$unclaimed_listing_config[$field_name]['num'];
                    
                    if (empty($limit_num)) {
                        unset($form[$field_name]);
                        continue;
                    }                    
                    if (isset($form[$field_name]['add'])) {
                        unset($form[$field_name]['add']);
                    } else {
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
            if (isset($unclaimed_listing_config['fields']) && is_array($unclaimed_listing_config['fields'])) {
                $form = $this->Directory_FilterFormFields($form, $unclaimed_listing_config['fields']);
            }
            // Limit photo numbers?
            if (isset($form['directory_photos'])) {
                if (!empty($unclaimed_listing_config['directory_photos']['limit'])) {
                    if (empty($unclaimed_listing_config['directory_photos']['num'])) {
                        unset($form['directory_photos']);
                    } else {
                        if ($unclaimed_listing_config['directory_photos']['num'] < $form['directory_photos']['#max_num_files']) {
                            $form['directory_photos']['#max_num_files'] = $unclaimed_listing_config['directory_photos']['num'];
                        }
                    }
                }
            }
        }
        
        $form['dashboard'] = array('#type' => 'hidden', '#value' => $context->getRequest()->asInt('dashboard'));
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $this->Entity_Save($context->entity, $form->values, array('entity_field_max_num_values' => $this->_maxNumValues));
        $context->setSuccess()->addFlash(__('The listing has been updated successfully.', 'sabai-directory'));
        $context->setSuccessUrl($this->_cancelUrl);
    }
}
