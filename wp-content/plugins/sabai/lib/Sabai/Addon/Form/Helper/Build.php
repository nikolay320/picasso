<?php
class Sabai_Addon_Form_Helper_Build extends Sabai_Helper
{
    static protected $_forms = array();
    
    public function help(Sabai $application, array $settings, $useCache = true, array $values = null, array $errors = array())
    {
        if (!isset($settings['#build_id'])) {
            $settings['#build_id'] = md5(uniqid(mt_rand(), true));
            $settings['#is_rebuild'] = false;
        } else { 
            // Is the form already built and cached?
            if ($settings['#build_id'] !== false && isset(self::$_forms[$settings['#build_id']])) {
                // Return cached form if rebuild is not necessary
                if ($useCache) return self::$_forms[$settings['#build_id']];
            }
            $settings['#is_rebuild'] = isset($settings['#is_rebuild']);
        }
        // Set id if not already set
        if (!isset($settings['#id'])) {
            $settings['#id'] = 'sabai-form-' . ($settings['#build_id'] !== false ? $settings['#build_id'] : md5(uniqid(mt_rand(), true)));
        }
        
        $settings['#method'] = isset($settings['#method']) && strtolower($settings['#method']) === 'get' ? 'get' : 'post';

        if ($settings['#build_id'] !== false
            && ($settings['#method'] !== 'get' || !empty($settings['#enable_storage']))
        ) { 
            // Embed build ID in hidden field
            $settings[Sabai_Addon_Form::FORM_BUILD_ID_NAME] = array(
                '#type' => 'hidden',
                '#value' => $settings['#build_id']
            );
        }

        // Initialize form storage
        $storage = array();
        if (!empty($settings['#enable_storage'])) {
            if (isset($settings['#initial_storage'])) $storage = $settings['#initial_storage'];

            $application->getAddon('Form')->setFormStorage($settings['#build_id'], $storage);
        }
        
        // Define submit buttons fieldset
        if (!isset($settings[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME])) {
            $settings[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME] = array();
        }

        // Allow other plugins to modify form settings and storage
        $application->Action(
            'form_build_form',
            array(&$settings, &$storage)
        );
        // Call with inherited form names
        if (!empty($settings['#inherits'])) {
            foreach (array_reverse($settings['#inherits']) as $inherited_form_name) {
                $application->Action(
                    'form_build_' . $inherited_form_name,
                    array(&$settings, &$storage)
                );
            }
        }
        // Call with the name of current form
        if (!empty($settings['#name'])) {
            $application->Action(
                'form_build_' . $settings['#name'],
                array(&$settings, &$storage)
            );
        }

        $form = new Sabai_Addon_Form_Form($application->getAddon('Form'), $settings, $storage, $errors);
        $form->build($values);

        if ($settings['#build_id'] !== false) {
            // Add built form to cache
            self::$_forms[$settings['#build_id']] = $form;
        }

        return $form;
    }
}