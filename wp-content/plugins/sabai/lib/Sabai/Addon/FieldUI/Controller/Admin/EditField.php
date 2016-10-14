<?php
class Sabai_Addon_FieldUI_Controller_Admin_EditField extends Sabai_Addon_Form_Controller
{
    private $_field, $_bundle, $_views;

    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_bundle = $context->child_bundle ? $context->child_bundle : ($context->taxonomy_bundle ? $context->taxonomy_bundle : $context->bundle);
        $this->_cancelWeight = -99;
        $this->_ajaxOnContent = 'function(response, target, trigger){target.focusFirstInput();}';

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

        if ((!$field_id = $context->getRequest()->asInt('field_id'))
            || (!$this->_field = $this->getModel('Field', 'Entity')->fetchById($field_id))
        ) {
            return false;
        } else {
            if (empty($field_types[$this->_field->getFieldType()]['widgets'])) {
                // no supported widgets for this field type
                $context->setError(__('Invalid field type.', 'sabai'));
                return false;
            }
        }

        // Set options
        $this->_submitButtons = array(array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary'));
        $this->_ajaxOnSuccess = 'function(result, target, trigger) {
            $(SABAI).trigger("fieldui_field_updated.sabai", {trigger: trigger, result: result, target: target});
        }';
        $this->_ajaxOnError = 'function(error, target, trigger) {
            target.hide();
            alert(error.message);
        }';
        $this->_ajaxOnCancel = 'function(target) {
            $(SABAI).trigger("fieldui_field_cancelled.sabai", {trigger: $(this), target: target});
        }';

        $ifieldtype = $this->Field_TypeImpl($this->_field->getFieldType());
        try {
            $ifieldwidget = $this->Field_WidgetImpl($this->_field->getFieldWidget());
        } catch (Sabai_IException $e) {
            $default_widget = $field_types[$this->_field->getFieldType()]['default_widget'];
            if ($default_widget == $this->_field->getFieldWidget()) {
                // the default widget is the one that does not exist
                $this->_field->setFieldWidget(null)->setFieldWidgetSettings(array())->commit();
                throw $e;
            }
            // Change widget to the default widget
            $this->_field->setFieldWidget($default_widget)->commit();
            $ifieldwidget = $this->Field_WidgetImpl($default_widget);
        }
        $settings = $this->_field->getFieldSettings() + (array)$ifieldtype->fieldTypeGetInfo('default_settings');
        $widget_info = $ifieldwidget->fieldWidgetGetInfo();
        $widget_settings = $this->_field->getFieldWidgetSettings() + (array)@$widget_info['default_settings'];
        
        $form['field_id'] = array(
            '#type' => 'hidden',
            '#value' => $this->_field->id,
        );
        
        
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
            '#weight' => 30,
            '#collapsed' => true,
        );
        $form['advanced'] = array(
            '#type' => 'fieldset',
            '#title' => __('Advanced Settings', 'sabai'),
            '#tree' => false,
            '#weight' => 50,
            '#collapsed' => true,
        );
        
        // Display field type and link to switch to another widget
        $edit_widget_url = $this->Url($this->_bundle->getAdminPath() . '/fields/edit_widget', array('field_id' => $this->_field->id, 'ele_id' => $context->getRequest()->asStr('ele_id')));
        $form['field'] = array(
            '#type' => 'item',
            '#field_prefix' => __('Form element type:', 'sabai'),
            '#value' => count($field_types[$this->_field->getFieldType()]['widgets']) > 1
                ? $this->LinkToRemote($widget_info['label'], $context->getContainer(), $edit_widget_url, array('scroll' => true, 'width' => 600))
                : $widget_info['label'],
            '#weight' => 1,
        );
        
        if (empty($widget_info['disable_edit_label'])) {
            $form['basic']['label'] = array(
                '#title' => __('Label', 'sabai'),
                '#type' => 'textfield',
                '#max_length' => 255,
                '#required' => true,
                '#default_value' => $this->_field->getFieldLabel(),
                '#weight' => 4,
                '#class' => 'sabai-form-field-less-margin',
            );
            $form['basic']['hide_label'] = array(
                '#type' => 'checkbox',
                '#title' => __('Hide label', 'sabai'),
                '#default_value' => (bool)$this->_field->getFieldData('hide_label'),
                '#weight' => 5,
            );
        } else {
            // Add has hidden field so the original value is not lost
            $form['basic']['label'] = array(
                '#type' => 'hidden',
                '#default_value' => $this->_field->getFieldLabel(),
            );
            $form['basic']['hide_label'] = array(
                '#type' => 'hidden',
                '#default_value' => (bool)$this->_field->getFieldData('hide_label'),
            );
        }
        
        if (empty($widget_info['disable_edit_description'])) {
            $form['basic']['description'] = array(
                '#type' => 'textarea',
                '#title' => __('Description', 'sabai'),
                '#description' => __('Enter a short description of the field displayed to the user.', 'sabai'),
                '#rows' => 3,
                '#default_value' => $this->_field->getFieldDescription(),
                '#weight' => 6,
            );
        } else {
            // Add has hidden field so the original value is not lost
            $form['basic']['description'] = array(
                '#type' => 'hidden',
                '#default_value' => $this->_field->getFieldDescription(),
            );
        }
        if (false !== @$widget_info['requirable']) {
            $form['basic']['required'] = array(
                '#type' => 'checkbox',
                '#title' => __('Required', 'sabai'),
                '#default_value' => $this->_field->getFieldRequired(),
                '#description' => @$widget_info['requirable']['description'],
                '#weight' => 8,
            );
        } else {
            $form['basic']['required'] = array(
                '#type' => 'hidden',
                '#value' => 0,
            );
        }
        if (false !== @$widget_info['disableable']) {
            $form['basic']['disabled'] = array(
                '#type' => 'checkbox',
                '#title' => __('Disabled', 'sabai'),
                '#default_value' => $this->_field->getFieldDisabled(),
                '#weight' => 9,
            );
        } else {
            $form['basic']['disabled'] = array(
                '#type' => 'hidden',
                '#value' => 0,
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
                        '#default_value' => $this->_field->id ? $this->_field->getFieldMaxNumItems() : 1,
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
                    '#default_value' => $this->_field->id ? $this->_field->getFieldMaxNumItems() : 0,
                    '#weight' => 60,
                );
            } else {
                $form['basic']['max_num_items'] = array(
                    '#type' => 'hidden',
                    '#value' => 0,
                );
            }
        }

        if ($settings_form = (array)@$ifieldtype->fieldTypeGetSettingsForm($settings, array('settings'))) {
            if (isset($settings_form['#header'])) {
                $form['#header'] = array_merge($form['#header'], $settings_form['#header']);
                unset($settings_form['#header']);
            }
            $form['basic']['settings'] = array(
                '#type' => 'fieldset',
                '#tree' => true,
                '#tree_allow_override' => false,
                '#weight' => 40,
            );
            $form['basic']['settings'] += $settings_form;
        }

        if ($this->_field->isPropertyField()) {
            unset($form['basic']['required'], $form['basic']['disabled'], $form['basic']['settings']);
            $form['basic']['max_num_items'] = array(
                '#type' => 'hidden',
                '#value' => 1,
            );
        } else {
            if ($this->_field->fieldconfig_id) {
                //$form['basic']['settings']['#disabled'] = true;
            }
        }
        if ($settings_form = (array)@$ifieldwidget->fieldWidgetGetSettingsForm($this->_field->getFieldType(), $widget_settings, array('widget_settings'))) {
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

        // Add a field for setting the default value if the widget supports default values
        if ($default_value_form = $ifieldwidget->fieldWidgetGetEditDefaultValueForm($this->_field, $widget_settings, array('default_value'))) {
            $form['advanced']['default_value'] = array(
                '#title' => __('Default value', 'sabai'),
                '#weight' => 2,
                '#collapsible' => false,
                '#tree' => true,
            ) + $default_value_form;
            if (!isset($form['advanced']['default_value']['#default_value'])) {
                $default_value = $this->_field->getFieldDefaultValue();
                $form['advanced']['default_value']['#default_value'] = !empty($widget_info['accept_multiple']) ? $default_value : $default_value[0];
            }
        }
        
        $form['ele_id'] = array(
            '#type' => 'hidden',
            '#value' => $context->getRequest()->asStr('ele_id'),
        );
        
        
        if (($views = $this->_getViews())
            && ($renderers = @$field_types[$this->_field->getFieldType()]['renderers'])
        ) {
            $default_view = null;
            foreach (array_keys($renderers) as $renderer) {
                if (!$this->Field_RendererImpl($renderer, true)) {
                    unset($renderers[$renderer]);
                }
            }
            if (!empty($renderers)) {
                foreach ($views as $view => $view_title) {
                    
                    if (!$view_renderers = $this->Filter('fieldui_field_view_renderers', $renderers, array($this->_field->getFieldType(), $this->_bundle, $view, $this->_field->isCustomField()))) {
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
                            '#default_value' => null !== ($_view = $this->_field->getFieldView($view)) ? (bool)$_view : $view === 'default' || (in_array($view, array('summary', 'grid')) && !$this->_field->isCustomField()),
                            '#weight' => -4,
                        ),
                        'renderer' => array(
                            '#type' => 'select',
                            '#title' => __('Field renderer', 'sabai'),
                            '#description' => __('A field renderer determines how the value of a field will be displayed.', 'sabai'),
                            '#options' => $view_renderers,
                            '#weight' => -1,
                            '#default_value' => ($_renderer = $this->_field->getFieldRenderer($view))
                                ? $_renderer
                                : (isset($field_types[$this->_field->getFieldType()]['default_renderer']) ? $field_types[$this->_field->getFieldType()]['default_renderer'] : $this->_field->getFieldType()),
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
                    if ($this->_field->isCustomField()
                        && (!is_array($view_title) || !array_key_exists('no_field_title', $view_title) || empty($view_title['no_field_title']))
                    ) {
                        $form['view_' . $view]['settings']['title'] = array(
                            '#title' => __('Field title', 'sabai'),
                            '#class' => 'sabai-form-group',
                            '#weight' => -3,
                            '#collapsible' => false,
                            'type' => array(
                                '#type' => 'radios',
                                '#options' => array('form' => __('Use the form label', 'sabai'), 'custom' => __('Custom', 'sabai'), 'none' => __('None', 'sabai')),
                                '#default_value' => ($_title_type = $this->_field->getFieldTitleType($view)) ? $_title_type : 'form',
                                '#class' => 'sabai-form-inline',
                            ),
                            'custom' => array(
                                '#type' => 'textfield',
                                '#default_value' => $_title_type === 'custom' ? $this->_field->getFieldTitle($view) : null,
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
                        $renderer_settings = $this->_field->getFieldRendererSettings($view, $renderer);
                        $renderer_settings += (array)$ifieldrenderer->fieldRendererGetInfo('default_settings');
                        if ($renderer_settings_form = $ifieldrenderer->fieldRendererGetSettingsForm($this->_field->getFieldType(), $renderer_settings, $view, array('view_' . $view, 'settings', 'renderer_settings', $renderer))) {          
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
                                '#default_value' => $default_view === $_view,
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
            '#description' => __('Select user roles that can edit this field.', 'sabai'),
            '#options' => $roles,
            '#default_value' => $this->_field->hasFieldData('user_roles') ? $this->_field->getFieldData('user_roles') : array_keys($roles),
            '#weight' => 1,
            '#class' => 'sabai-form-inline',
        );
        if ($this->_field->isCustomField()) {
            if (false !== $ifieldtype->fieldTypeGetInfo('viewable')) {
                $super_user_roles = array_keys($this->AdministratorRoles());
                $form['visibility']['view_roles'] = array(
                    '#type' => 'checkboxes',
                    '#description' => __('Select user roles that can view this field.', 'sabai'),
                    '#options' => $roles,
                    '#options_disabled' => $super_user_roles,
                    '#default_value' => $this->_field->hasFieldData('view_roles') ? array_merge($super_user_roles, $this->_field->getFieldData('view_roles')) : array_keys($roles),
                    '#weight' => 2,
                    '#class' => 'sabai-form-inline',
                );
            }
        }
 
        $form['#inherits'] = 'fieldui_admin_edit_field_' . strtolower($this->_field->getFieldType());
        $form['#inherits'] = array('fieldui_admin_edit_field_widget_' . strtolower($this->_field->getFieldWidget()));
        
        $form['#field'] = $this->_field;
        
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
        if (!isset($this->_field)) {
            // Widget selection form was submitted

            if (isset($form->values['field_widget'])) {
                $form->storage['field_widget'] = $form->values['field_widget'];
            }
            $form->rebuild = true;
            $form->settings = $this->_getFormSettings($context, $form->settings['#build_id'], $form->storage);

            return;
        }

        // Field edit form submitted
        
        $field_is_new = $this->_field->id ? false : true;
        $field_types = $this->Field_Types();
        $field_data = array('type' => $this->_field->getFieldType(), 'data' => array());
        $field_data['label'] = isset($form->settings['basic']['label']) ? $form->values['label'] : $field_types[$this->_field->getFieldType()]['label'];
        if (isset($form->settings['basic']['hide_label'])) {
            $field_data['hide_label'] = !empty($form->values['hide_label']);
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
        if (isset($form->settings['basic']['settings']) && empty($form->settings['basic']['settings']['#disabled'])) {
            $field_data['settings'] = $form->values['settings'];
        }
        $field_data['widget'] = $this->_field->getFieldWidget();
        if (isset($form->settings['basic']['widget_settings'])) {
            $field_data['widget_settings'] = (array)@$form->values['widget_settings'];
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
        $field_data['weight'] = $this->_field->getFieldWeight();
        
        // Allow add-ons to modify field data
        $field_data = $this->Filter('fieldui_field_data', $field_data, array($this->_bundle, $this->_field, $form->values));
        
        if ($this->_field->isPropertyField()) {
            $field = $this->getAddon('Entity')->createEntityPropertyField(
                $this->_bundle,
                $this->_field->FieldConfig,
                $field_data,
                true, // commit
                true // overwrite
            );
        } else {
            $field = $this->getAddon('Entity')->createEntityField(
                $this->_bundle,
                $this->_field->FieldConfig,
                $field_data,
                Sabai_Addon_Entity::FIELD_REALM_ALL,
                true // overwrite
            );
        }

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
                'is_new' => $field_is_new,
                'ele_id' => $context->getRequest()->asStr('ele_id'),
                'preview' => $this->FieldUI_PreviewWidget($field),
            ));
        
        $this->Action('fieldui_submit_field_success', array($field, /*$isEdit*/ !$field_is_new));
        
        $this->getPlatform()->clearCache(); // some add-on configs may have been modified, so clear cache
    }
    
    protected function _getViews()
    {
        if (!isset($this->_views)) {
            $this->_views = $this->_application->Filter('fieldui_field_views', array(), array($this->_field->getFieldType(), $this->_bundle, $this->_field->isCustomField()));
        }
        return $this->_views;
    }
}