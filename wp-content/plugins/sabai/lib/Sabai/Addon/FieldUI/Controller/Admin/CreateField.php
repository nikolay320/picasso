<?php
class Sabai_Addon_FieldUI_Controller_Admin_CreateField extends Sabai_Addon_Form_Controller
{
    private $_fieldType, $_bundle, $_field;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_bundle = $context->child_bundle ? $context->child_bundle : ($context->taxonomy_bundle ? $context->taxonomy_bundle : $context->bundle);
        $this->_cancelWeight = -99;
        $this->_ajaxOnContent = 'function(response, target, trigger){target.focusFirstInput();}';
        $this->_submitButtons = array(array('#value' => __('Add Field', 'sabai'), '#btn_type' => 'primary'));

        // Define form
        $form = array(
            '#action' => $this->Url($context->getRoute()),
            '#token_reuseable' => true,
            '#enable_storage' => true,
            '#bundle' => $this->_bundle->name,
            '#header' => array(),
        );

        // Get available field types
        $field_types = $this->Field_Types();

        // Creating from an existing field?
        if (($field_name = $context->getRequest()->asStr('field_name'))
            && ($field = $this->getModel('FieldConfig', 'Entity')->name_is($field_name)->fetchOne())
        ) {
            $this->_fieldType = $field->type;
            if ($this->_fieldType !== 'text' // Text type is deprecated but should be able to create one from existing fields
                && !$field_types[$this->_fieldType]['creatable']
            ) {
                // The field is not reuseable.
                $context->setError(__('Invalid field type.', 'sabai'));
                return false;
            }
            if ($this->getModel('Field', 'Entity')->bundleId_is($this->_bundle->id)->fieldconfigId_is($field->id)->count()) {
                // The field is already added to the bundle.
                $context->setError(__('Invalid field type.', 'sabai'));
                return false;
            }
            $this->_field = $field;
        } else {
            // Make sure a valid field type is in the request
            if (!$this->_fieldType = $context->getRequest()->asStr('field_type')) {
                $context->setError(__('Invalid field type.', 'sabai'));
                return false;
            }
            if (!$field_types[$this->_fieldType]['creatable']) {
                $context->setError(__('Invalid field type.', 'sabai'));
                return false;
            }
        }

        if (empty($field_types[$this->_fieldType]['widgets'])) {
            $context->setError(__('Invalid field type.', 'sabai'));
            return false;
        }

        // Make sure a valid widget type is in the request
        if (!isset($formStorage['field_widget'])
            || !$this->Field_WidgetImpl($formStorage['field_widget'], true)
        ) {
            unset($formStorage['field_widget']);
            
            if (count($field_types[$this->_fieldType]['widgets']) > 1) {
                // Display widget selection form
                $this->_submitButtons = array(array('#value' => __('Next', 'sabai')));
                asort($field_types[$this->_fieldType]['widgets']);
                $form['field_widget'] = array(
                    '#type' => 'radios',
                    '#title' => __('Form element type', 'sabai'),
                    '#description' => __('Select the type of form element you would like to present to the user when editing this field.', 'sabai'),
                    '#options' => $field_types[$this->_fieldType]['widgets'],
                    '#default_value' => $field_types[$this->_fieldType]['default_widget'],
                    '#required' => true,
                );
                $form['field_type'] = array(
                    '#type' => 'hidden',
                    '#value' => $this->_fieldType,
                );
                if ($this->_field) {
                    $form['field_name'] = array(
                        '#type' => 'hidden',
                        '#value' => $this->_field->name,
                    );
                }

                return $form;
            }
            
            // Only 1 field widget available for this field, so try to select it and proceed
            $widget_names = array_keys($field_types[$this->_fieldType]['widgets']);
            $formStorage['field_widget'] = array_shift($widget_names);
            if (!$this->Field_WidgetImpl($formStorage['field_widget'], true)) {
                $context->setError(__('Invalid field type.', 'sabai'));
                return false;
            }
        }

        // Set options
        $this->_ajaxOnSuccess = 'function(result, target, trigger) {
            $(SABAI).trigger("fieldui_field_created.sabai", {trigger: trigger, result: result, target: target});
        }';
        $this->_ajaxOnError = 'function(error, target, trigger) {
            target.hide();
            alert(error.message);
        }';
        $this->_ajaxOnCancel = 'function(target) {
            target.hide();
        }';

        $ifieldtype = $this->Field_TypeImpl($this->_fieldType);
        $ifieldwidget = $this->Field_WidgetImpl($formStorage['field_widget']);
        $settings = (array)$ifieldtype->fieldTypeGetInfo('default_settings');
        $widget_info = $ifieldwidget->fieldWidgetGetInfo();
        $widget_settings = (array)@$widget_info['default_settings'];
        
        if ($this->_field) {
            $form['name'] = array(
                '#type' => 'item',
                '#title' => __('Field name', 'sabai'),
                '#markup' => $this->_field->name,
            );
            $form['field_name'] = array(
                '#type' => 'hidden',
                '#value' => $this->_field->name,
            );
            $form['#field_name'] = $this->_field->name; // let other add-ons access this field on build form filter event
        } else {
            $form['name'] = array(
                '#type' => 'textfield',
                '#title' => __('Field name', 'sabai'),
                '#description' => __('Enter a machine readable name for this form field. Only lowercase alphanumeric characters and underscores are allowed.', 'sabai'),
                '#max_length' => 255,
                '#required' => true,
                '#weight' => 2,
                '#size' => 20,
                '#regex' => '/^[a-z0-9_]+$/',
                '#element_validate' => array(array($this, 'validateName')),
                '#field_prefix' => 'field_',
            );
        }
        
        // Define fieldsets
        $form['basic'] = array(
            '#type' => 'fieldset',
            '#title' => __('Field Settings', 'sabai'),
            '#tree' => false,
            '#weight' => 10,
            '#collapsed' => false,
        );
        $form['display'] = array(
            '#type' => 'fieldset',
            '#title' => __('Display Settings', 'sabai'),
            '#tree' => false,
            '#weight' => 15,
            '#collapsed' => false,
        );   
        $form['visibility'] = array(
            '#type' => 'fieldset',
            '#title' => __('Access Control Settings', 'sabai'),
            '#tree' => false,
            '#weight' => 20,
            '#collapsed' => true,
        );
        $form['advanced'] = array(
            '#type' => 'fieldset',
            '#title' => __('Advanced Settings', 'sabai'),
            '#tree' => false,
            '#weight' => 30,
            '#collapsed' => true,
        );
        
        if (empty($widget_info['disable_edit_label'])) {
            $form['basic']['label'] = array(
                '#title' => __('Label', 'sabai'),
                '#type' => 'textfield',
                '#max_length' => 255,
                '#required' => true,
                '#weight' => 4,
                '#class' => 'sabai-form-field-less-margin',
            );
            $form['basic']['hide_label'] = array(
                '#type' => 'checkbox',
                '#title' => __('Hide label', 'sabai'),
                '#weight' => 5,
            );
        }
        if (empty($widget_info['disable_edit_description'])) {
            $form['basic']['description'] = array(
                '#type' => 'textarea',
                '#title' => __('Description', 'sabai'),
                '#description' => __('Enter a short description of the field displayed to the user.', 'sabai'),
                '#rows' => 3,
                '#default_value' => null,
                '#weight' => 6,
            );
        }
        if (false !== @$widget_info['requirable']) {
            $form['basic']['required'] = array(
                '#type' => 'checkbox',
                '#title' => __('Required', 'sabai'),
                '#default_value' => !empty($widget_info['default_required']) ? true : null,
                '#description' => @$widget_info['requirable']['description'],
                '#weight' => 8,
            );
        }
        if (false !== @$widget_info['disableable']) {
            $form['basic']['disabled'] = array(
                '#type' => 'checkbox',
                '#title' => __('Disabled', 'sabai'),
                '#default_value' => !empty($widget_info['default_disabled']) ? true : null,
                '#weight' => 9,
            );
        }

        // Add an option to make this field repeatable if the selected widget supports the feature
        if (empty($widget_info['accept_multiple'])) {
            if (!empty($widget_info['repeatable'])) {
                if (empty($widget_info['disable_edit_max_num_items'])) {
                    $form['basic']['max_num_items'] = array(
                        '#type' => 'select',
                        '#options' => $this->_getMaxNumItemsOptions($ifieldtype),
                        '#title' => __('Maximum number of values', 'sabai'),
                        '#description' => __('Maximum number of values users can enter for this field.', 'sabai'),
                        '#default_value' => 1,
                        '#weight' => 60,
                    );
                } else {
                    $form['basic']['max_num_items'] = array(
                        '#type' => 'hidden',
                        '#value' => 1,
                    );
                }
            } else {
                $form['basic']['max_num_items'] = array(
                    '#type' => 'hidden',
                    '#value' => 1,
                );
            }
        } else {
            if (empty($widget_info['disable_edit_max_num_items'])) {
                $form['basic']['max_num_items'] = array(
                    '#type' => 'select',
                    '#options' => $this->_getMaxNumItemsOptions($ifieldtype),
                    '#title' => __('Maximum number of values', 'sabai'),
                    '#description' => __('Maximum number of values users can enter for this field.', 'sabai'),
                    '#default_value' => 0,
                    '#weight' => 60,
                );
            } else {
                $form['basic']['max_num_items'] = array(
                    '#type' => 'hidden',
                    '#value' => 0,
                );
            }
        }

        if (!$this->_field) {
            if ($settings_form = (array)@$ifieldtype->fieldTypeGetSettingsForm($settings, array('settings'))) {
                if (isset($settings_form['#header'])) {
                    $form['#header'] = array_merge($form['#header'], $settings_form['#header']);
                    unset($settings_form['#header']);
                }
                // Add field specific settings form
                $form['basic']['settings'] = array(
                    '#type' => 'fieldset',
                    '#tree' => true,
                    '#tree_allow_override' => false,
                    '#weight' => 40,
                );
                $form['basic']['settings'] += $settings_form;
            }
        }

        if ($settings_form = (array)@$ifieldwidget->fieldWidgetGetSettingsForm($this->_fieldType, $widget_settings, array('widget_settings'))) {
            if (isset($settings_form['#header'])) {
                $form['#header'] = array_merge($form['#header'], $settings_form['#header']);
                unset($settings_form['#header']);
            }
            $form['basic']['widget_settings'] = array(
                '#type' => 'fieldset',
                '#tree' => true,
                '#tree_allow_override' => false,
                '#weight' => 50,
            ) + $settings_form;
        }

        if (($views = $this->_getViews())
            && ($renderers = @$field_types[$this->_fieldType]['renderers'])
        ) {
            $default_view = null;
            foreach (array_keys($renderers) as $renderer) {
                if (!$this->Field_RendererImpl($renderer, true)) {
                    unset($renderers[$renderer]);
                }
            }
            if (!empty($renderers)) {
                foreach ($views as $view => $view_title) {
                    
                    if (!$view_renderers = $this->Filter('fieldui_field_view_renderers', $renderers, array($this->_fieldType, $this->_bundle, $view, true))) {
                        continue;
                    }
                    
                    $form['view_' . $view] = array(
                        '#title' => sprintf(__('Display Settings (%s)', 'sabai'), is_array($view_title) ? $view_title['title'] : $view_title),
                        '#tree' => true,
                        '#weight' => 15,
                    );
                    $form['view_' . $view]['settings'] = array(
                        'display' => array(
                            '#title' => __('Display this field', 'sabai'),
                            '#type' => 'checkbox',
                            '#default_value' => $view === 'default' ? (!is_array($view_title) || false !== @$view_title['display']) : (is_array($view_title) && @$view_title['display']),
                            '#weight' => -4,
                        ),
                        'renderer' => array(
                            '#type' => 'select',
                            '#title' => __('Field renderer', 'sabai'),
                            '#description' => __('A field renderer determines how the value of a field will be displayed.', 'sabai'),
                            '#options' => $view_renderers,
                            '#weight' => -1,
                            '#default_value' => isset($field_types[$this->_fieldType]['default_renderer']) ? $field_types[$this->_fieldType]['default_renderer'] : $this->_fieldType,
                            '#states' => array(
                                'visible' => array('input[name^="view_'. $view .'[settings][display]"]' => array('type' => 'checked', 'value' => true)),
                            ),
                        ),
                        'renderer_settings' => array(
                            '#tree' => true,
                            '#states' => array(
                                'visible' => array('input[name^="view_'. $view .'[settings][display]"]' => array('type' => 'checked', 'value' => true)),
                            ),
                        ),
                    );
                    if (!is_array($view_title) || !array_key_exists('no_field_title', $view_title) || empty($view_title['no_field_title'])) {
                        $form['view_' . $view]['settings']['title'] = array(
                            '#title' => __('Field title', 'sabai'),
                            '#class' => 'sabai-form-group',
                            '#weight' => -3,
                            '#collapsible' => false,
                            'type' => array(
                                '#type' => 'radios',
                                '#options' => array('form' => __('Use the form label', 'sabai'), 'custom' => __('Custom', 'sabai'), 'none' => __('None', 'sabai')),
                                '#default_value' => 'form',
                                '#class' => 'sabai-form-inline',
                            ),
                            'custom' => array(
                                '#type' => 'textfield',
                                '#default_value' => null,
                                '#states' => array(
                                    'visible' => array('input[name="view_'. $view .'[settings][title][type]"]' => array('value' => 'custom')),
                                ),
                                '#required' => array(array($this, 'isCustomFieldTitleRequired'), array($view)),
                            ),
                            '#states' => array(
                                'visible' => array('input[name="view_'. $view .'[settings][display][]"]' => array('type' => 'checked', 'value' => true)),
                            ),
                        );
                    }
                    foreach (array_keys($view_renderers) as $renderer) {
                        $ifieldrenderer = $this->Field_RendererImpl($renderer);
                        $renderer_settings = (array)$ifieldrenderer->fieldRendererGetInfo('default_settings');
                        if ($renderer_settings_form = $ifieldrenderer->fieldRendererGetSettingsForm($this->_fieldType, $renderer_settings, $view, array('view_' . $view, 'settings', 'renderer_settings', $renderer))) {          
                            $form['view_' . $view]['settings']['renderer_settings'][$renderer] = $renderer_settings_form;
                        }
                        if (false !== $ifieldrenderer->fieldRendererGetInfo('separatable', $view)) {
                            if (!empty($widget_info['accept_multiple']) || !empty($widget_info['repeatable'])) {
                                if (!isset($form['view_' . $view]['settings']['renderer_settings'][$renderer])) {
                                    $form['view_' . $view]['settings']['renderer_settings'][$renderer] = array();
                                }
                                $form['view_' . $view]['settings']['renderer_settings'][$renderer]['separator'] = array(
                                    '#type' => 'textfield',
                                    '#title' => __('Field value separator', 'sabai'),
                                    '#default_value' => @$renderer_settings['separator'],
                                    '#weight' => 100,
                                    '#size' => 10,
                                    '#no_trim' => true,
                                    '#states' => array(
                                        'invisible' => array('[name="max_num_items"]' => array('value' => 1)),
                                    ),
                                );
                            }
                        }
                        if (!empty($form['view_' . $view]['settings']['renderer_settings'][$renderer])) {
                            $form['view_' . $view]['settings']['renderer_settings'][$renderer]['#states']['visible'] = array(
                                '[name="view_'. $view .'[settings][renderer]"]' => array('value' => $renderer),
                            );
                        }
                    }
                    if (isset($default_view)) {
                        if (false !== @$view_title['inherit']) {
                            $form['view_' . $view]['inherit_default'] = array(
                                '#type' => 'checkbox',
                                '#title' => sprintf(__('Inherit display settings from %s', 'sabai'), is_array($views[$default_view]) ? $views[$default_view]['title'] : $views[$default_view]),
                                '#weight' => -5,
                                '#default_value' => false,
                            );
                            $form['view_' . $view]['settings']['#states']['invisible'] = array(
                                'input[name="view_'. $view .'[inherit_default][]"]' => array('type' => 'checked', 'value' => true),
                            );
                        }
                    } else {
                        $default_view = $view;
                        $form['default_view'] = array('#type' => 'hidden', '#value' => $default_view);
                    }
                }
            }
        }

        $roles = $this->System_Roles(
            'title',
            $this->_bundle->entitytype_name !== 'content' // no guest role if the bundle is not the content entity type
                || @$this->_bundle->info['content_guest_author'] === false // no guest role if the bundle does not support guest authors
        );
        $form['visibility']['user_roles'] = array(
            '#type' => 'checkboxes',
            '#description' => __('Select user roles to which this field is visible when submitting the form.', 'sabai'),
            '#options' => $roles,
            '#default_value' => isset($widget_info['default_user_roles']) ? $widget_info['default_user_roles'] : array_keys($roles),
            '#weight' => 1,
            '#class' => 'sabai-form-inline',
        );
        if (false !== $ifieldtype->fieldTypeGetInfo('viewable')) {
            $super_user_roles = array_keys($this->AdministratorRoles());
            $form['visibility']['view_roles'] = array(
                '#type' => 'checkboxes',
                '#description' => __('Select user roles that can view this field.', 'sabai'),
                '#options' => $roles,
                '#options_disabled' => $super_user_roles,
                '#default_value' => isset($widget_info['default_view_roles']) ? $widget_info['default_view_roles'] : array_keys($roles),
                '#weight' => 2,
                '#class' => 'sabai-form-inline',
            );
        }

        // Add a field for setting the default value if the widget supports default values
        if ($default_value_form = $ifieldwidget->fieldWidgetGetEditDefaultValueForm($this->_fieldType, $widget_settings, array('default_value'))) {
            $form['advanced']['default_value'] = array(
                '#title' => __('Default value', 'sabai'),
                '#weight' => 70,
                '#collapsible' => false,
                '#tree' => true,
            ) + $default_value_form;
        }

        $form['field_type'] = array(
            '#type' => 'hidden',
            '#value' => $this->_fieldType,
        );
        
        $form['ele_id'] = array(
            '#type' => 'hidden',
            '#value' => $context->getRequest()->asStr('ele_id'),
        );

        $form['#inherits'] = 'fieldui_admin_create_field_' . strtolower($this->_fieldType);
        $form['#inherits'] = array('fieldui_admin_create_field_widget_' . strtolower($formStorage['field_widget']));

        return $form;
    }
    
    public function isCustomFieldTitleRequired($form, $view)
    {
        return $form->values['view_' . $view]['settings']['title']['type'] == 'custom';
    }
    
    private function _getMaxNumItemsOptions($ifieldtype)
    {
        if ($max_num_items_options = $ifieldtype->fieldTypeGetInfo('max_num_items_options')) {
            return array_combine($max_num_items_options, $max_num_items_options);
        }
        return array(__('Unlimited', 'sabai')) + array_combine(range(1, 10), range(1, 10));
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!isset($form->storage['field_widget'])) {
            // Widget selection form was submitted

            if (isset($form->values['field_widget'])) {
                $form->storage['field_widget'] = $form->values['field_widget'];
            }
            $form->rebuild = true;
            $form->settings = $this->_getFormSettings($context, $form->settings['#build_id'], $form->storage);

            return;
        }
        
        // Get available field types
        $field_types = $this->Field_Types();
        $field_data = array('type' => $this->_fieldType, 'data' => array());
        // Is it an existing field?
        if ($this->_field) {
            $field_name = $this->_field->name;
            $field_data['settings'] = $this->_field->settings;
        } else {
            $field_name = 'field_' . $form->values['name'];
            if (isset($form->settings['basic']['settings']) && empty($form->settings['basic']['settings']['#disabled'])) {
                $field_data['settings'] = $form->values['settings'];
            }
        }
        
        $field_data['label'] = isset($form->settings['basic']['label']) ? $form->values['label'] : $field_types[$this->_fieldType]['label'];
        if (isset($form->settings['basic']['hide_label'])) {
            $field_data['hide_label'] = !empty($form->values['hide_label']);
        }
        if (isset($form->settings['basic']['description'])) {
            $field_data['description'] = $form->values['description'];
        }
        if (isset($form->settings['basic']['required'])) {
            $field_data['required'] = !empty($form->values['required']);
        }
        if (isset($form->settings['basic']['disabled'])) {
            $field_data['disabled'] = !empty($form->values['disabled']);
        }
        if (isset($form->settings['basic']['max_num_items'])) {
            $field_data['max_num_items'] = $form->values['max_num_items'];
        }
        $field_data['widget'] = $form->storage['field_widget'];
        if (isset($form->settings['basic']['widget_settings'])) {
            $field_data['widget_settings'] = (array)@$form->values['widget_settings'];
        }
        foreach (array_keys($this->_getViews()) as $view) {
            if (!empty($form->values['view_' . $view]['inherit_default'])
                && !empty($form->values['default_view'])
            ) {
                $field_data['view'][$view] = empty($field_data['view'][$form->values['default_view']]) ? false : $form->values['default_view'];
                continue;
            }
            if (empty($form->values['view_' . $view]['settings']['display'])) {
                $field_data['view'][$view] = false;
                continue;
            }
            $field_data['view'][$view] = $view;
            if (isset($form->settings['view_' . $view]['settings']['renderer'])) {
                $field_data['renderer'][$view] = $form->values['view_' . $view]['settings']['renderer'];
                $field_data['renderer_settings'][$view] = (array)@$form->values['view_' . $view]['settings']['renderer_settings'];
            }
            if (isset($form->settings['view_' . $view]['settings']['title']['type'])) {
                switch ($form->values['view_' . $view]['settings']['title']['type']) {
                    case 'custom':
                        $field_data['title'][$view] = $form->values['view_' . $view]['settings']['title']['custom'];
                        $field_data['title_type'][$view] = 'custom';
                        break;
                    case 'none':
                        $field_data['title'][$view] = '';
                        $field_data['title_type'][$view] = 'none';
                        break;
                    default:
                        $field_data['title'][$view] = $field_data['label'];
                }
            }
        }
        if (isset($form->settings['advanced']['default_value']) && isset($form->values['default_value'])) {
            $field_data['default_value'] = is_array($form->values['default_value']) && isset($form->values['default_value'][0])
                ? $form->values['default_value']
                : array($form->values['default_value']);
        }
        if (isset($form->settings['visibility']['user_roles'])) {
            $field_data['data']['user_roles'] = (array)@$form->values['user_roles'];
        }
        if (isset($form->settings['visibility']['view_roles'])) {
            $field_data['data']['view_roles'] = (array)@$form->values['view_roles'];
        }
        $field_data['weight'] = 99;
        
        // Allow add-ons to modify field data
        $field_data = $this->Filter('fieldui_field_data', $field_data, array($this->_bundle, $field_name, $form->values));
        
        $field = $this->getAddon('Entity')->createEntityField($this->_bundle, $field_name, $field_data, Sabai_Addon_Entity::FIELD_REALM_ALL);
        
        $context->setSuccess($this->_bundle->getAdminPath() . '/fields')
            ->setSuccessAttributes(array(
                'id' => $field->id,
                'label' => Sabai::h($field->getFieldLabel()),
                'hide_label' => (int)$field->getFieldData('hide_label'),
                'title' => Sabai::h($field->getFieldTitle()),
                'description' => $field->getFieldDescription(),
                'type' => $field->getFieldType(),
                'type_normalized' => str_replace('_', '-', $field->getFieldType()),
                'name' => $field->getFieldName(),
                'required' => $field->getFieldRequired() ? 1 : 0,
                'disabled' => $field->getFieldDisabled() ? 1 : 0,
                'ele_id' => $context->getRequest()->asStr('ele_id'),
                'preview' => $this->FieldUI_PreviewWidget($field),
            ));
        
        $this->Action('fieldui_submit_field_success', array($field, /*$isEdit*/ false));
        
        $this->getPlatform()->clearCache(); // some add-on configs may have been modified, so clear cache
    }

    public function validateName(Sabai_Addon_Form_Form $form, &$value, $element)
    {
        // Make sure the field name is unique
        $field_name = 'field_' . $value;
        $repository = $this->getModel('FieldConfig', 'Entity')->name_is($field_name);
        if ($repository->count() > 0) {
            $form->setError(__('The name is already in use by another field.', 'sabai'), $element);
        }
    }
    
    protected function _getViews()
    {
        if (!isset($this->_views)) {
            $this->_views = $this->_application->Filter('fieldui_field_views', array(), array($this->_fieldType, $this->_bundle, true));
        }
        return $this->_views;
    }
}