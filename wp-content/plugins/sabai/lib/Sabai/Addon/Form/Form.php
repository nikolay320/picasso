<?php
class Sabai_Addon_Form_Form
{
    public $settings, $values, $storage, $rebuild = false, $redirect, $redirectMessage;
    protected $_addon, $_htmlquickform, $_elements, $_errors, $_clickedButton, $_isSubmitted,
        $_submitSuccess = false, $_defaultElementType = 'markup', $_js = array(), $_renderer, $_rendered = array(),
        $_originalValues;
    protected static $_defaultElementSettings = array(
        '#type' => null,
        '#title' => '',
        '#description' => null,
        '#value' => null,
        '#attributes' => array(),
        '#weight' => 0,
        '#element_validate' => array(),
        '#required' => null,
        '#disabled' => null,
        '#tree' => null,
        '#tree_allow_override' => true,
        '#class' => null,
        '#template' => null,
        '#prefix' => null,
        '#suffix' => null,
        '#collapsible' => null,
        '#collapsed' => false,
        '#children' => array(),
        '#processed' => false,
    );

    public function __construct(Sabai_Addon $addon, array $settings, array $storage, array $errors = array())
    {
        $this->_addon = $addon;
        $this->settings = $settings;
        $this->storage = $storage;
        $this->_errors = $errors;
    }

    public function build(array $values = null)
    {
        $this->settings['#method'] = isset($this->settings['#method']) && 'get' === strtolower($this->settings['#method']) ? 'get' : 'post';
        $this->_htmlquickform = new SabaiFramework_HTMLQuickForm(
            $this->settings['#id'],
            $this->settings['#method'],
            !empty($this->settings['#action']) ? $this->settings['#action'] : '',
            !empty($this->settings['#target']) ? $this->settings['#target'] : '',
            !empty($this->settings['#attributes']) ? $this->settings['#attributes'] : null,
            false
        );
        if (!isset($this->settings['#states']) || !is_array($this->settings['#states'])) {
            $this->settings['#states'] = array();
        }
        $this->values = $values;
        //$this->values = isset($values) ? $values : ('get' === $this->settings['#method'] ? $_GET: $_POST);

        if (!isset($this->settings['#token']) || $this->settings['#token'] !== false) {
            // Add form token
            $this->settings[Sabai_Request::PARAM_TOKEN] = array(
                '#type' => 'token',
                '#token_id' => !empty($this->settings['#token_id'])
                    ? $this->settings['#token_id']
                    : (isset($this->settings['#name']) ? $this->settings['#name']: $this->settings['#id']),
                '#token_reuseable' => !empty($this->settings['#token_reuseable']),
                '#token_reobtainable' => !empty($this->settings['#token_reobtainable']),
                '#token_lifetime' => empty($this->settings['#token_lifetime']) ? 1800 : $this->settings['#token_lifetime'],
            );
        }

        // Add form elements
        $this->_elements = array();
        $this->_extractAndSortElementSettings($this->settings, $this->_elements, $this->values);
        foreach (array_keys($this->_elements['#children']) as $weight) {
            foreach (array_keys($this->_elements['#children'][$weight]) as $ele_key) {
                if ($element = $this->createElement($this->_elements['#children'][$weight][$ele_key]['#type'], $ele_key, $this->_elements['#children'][$weight][$ele_key])) {
                    $this->_htmlquickform->addElement($element);
                }
            }
        }

        return $this;
    }

    public function getFieldId($name)
    {
        return $this->settings['#id'] . '-' . strtr($name, array('[' => '-', ']' => '', '_' => '-'));
    }

    public function createFieldset($name, array &$data, array $children = null)
    {
        $data += $this->defaultElementSettings();
        if (!isset($data['#label'])) {
            $data['#label'] = array(Sabai::h($data['#title']), $data['#description']);
        }
        if (isset($children)) {
            $data['#children'][] = $children;
        }
        return $this->_addon->getApplication()->Form_FieldImpl('fieldset')->formFieldGetFormElement($name, $data, $this);
    }

    public function createElement($type, $name, array &$data)
    {
        if (empty($type)) {
            $type = $this->_defaultElementType;
        }
        if (empty($data['#type'])) {
            $data['#type'] = $type;
        }

        // Convert the label data into array and add description/tip data there since
        // the HTML_QuickForm library only allows label data to be added as 1 variable.
        if (!isset($data['#label'])) {
            $data['#label'] = array(
                isset($data['#title']) ? (empty($data['#title_no_escape']) ? Sabai::h($data['#title']) : $data['#title']) : '',
                isset($data['#description']) ? $data['#description'] : null,
            );
            if (isset($data['#description2'])) { // for fieldset element
                $data['#label'][] = $data['#description2'];
            }
        }

        if (!empty($data['#disabled'])) {
            $data['#attributes']['disabled'] = 'disabled';
        }

        // Get the element
        if (!$element = $this->_addon->getApplication()->Form_FieldImpl($type)->formFieldGetFormElement($name, $data, $this)) return;

        // Has this element already been processed?
        if (!empty($data['#processed'])) return $element;

        $data['#processed'] = true; // mark as processed

        $data['#name'] = $element->getName();

        // Set default value
        if (isset($data['#default_value'])) {
            $this->_htmlquickform->setDefaults(array($data['#name'] => $data['#default_value']));
        }
        // Set constant value that cannot be modified by the user
        if (isset($data['#value'])) {
            $this->_htmlquickform->setConstants(array($data['#name'] => $data['#value']));
        }

        if (!empty($data['#states'])) {
            if (!isset($data['#states_selector'])) {
                if (!isset($data['#id'])) {
                    $data['#id'] = $this->getFieldId($data['#name']);
                }
                $dependent = '#' . $data['#id'];
            } else {
                $dependent = $data['#states_selector'];
            }
            
            if (!isset($this->settings['#states'][$dependent])) {
                $this->settings['#states'][$dependent] = array();
            }
            foreach ($data['#states'] as $action => $conditions) {
                if (empty($conditions)) continue;

                foreach ($conditions as $dependee => $condition) {
                    $this->settings['#states'][$dependent][$action]['conditions'][$dependee] = $condition;
                }
            }
        }
        
        // Required?
        if (!empty($data['#required'])) {
            if (!empty($data['#disabled'])) {
                $data['#required'] = false; // skip required validation, but show as required
                $data['#display_required'] = true;
            }
        }

        return $element;
    }

    public function createHTMLQuickformElement($type)
    {
        $args = func_get_args();

        // Creates a raw HTML_QuickForm element
        return SabaiFramework_HTMLQuickForm::createElement($type, array_slice($args, 1));
    }

    public function getHTMLQuickformElement($elementName)
    {
        return $this->_htmlquickform->getElement($elementName);
    }

    private function _extractAndSortElementSettings(array &$settings, array &$elements, $values = null, $parent = null)
    {
        foreach (array_keys($settings) as $key) {
            if (0 === strpos($key, '#')) continue;

            $settings[$key] = $settings[$key] + self::$_defaultElementSettings;
            if (is_array($values) && array_key_exists($key, $values)) {
                $settings[$key]['#default_value'] = $values[$key];
                $_values = !isset($settings[$key]['#tree']) || $settings[$key]['#tree'] ? $values[$key] : $values;
            } else {
                $_values = !isset($settings[$key]['#tree']) || $settings[$key]['#tree'] ? null : $values;
            }
            $weight = intval(@$settings[$key]['#weight']);
            $elements['#children'][$weight][$key] = $settings[$key];
            $this->_extractAndSortElementSettings($settings[$key], $elements['#children'][$weight][$key], $_values, $key);

            if (isset($parent)) {
                if (empty($elements['#type'])) $elements['#type'] = 'fieldset';
                if (is_null($elements['#collapsible'])) $elements['#collapsible'] = true;
                unset($elements[$key]); // remove redundant element data
            }
        }

        // Sort elements by the #weight setting
        ksort($elements['#children']);
    }

    public static function defaultElementSettings()
    {
        return self::$_defaultElementSettings;
    }

    public function setError($message, $element = '')
    {
        if (is_array($element)) {
            $this->_errors[$element['#name']][] = $message;
        } else {
            $this->_errors[$element][] = $message;
        }

        return $this;
    }

    public function hasError($elementName = null)
    {
        return isset($elementName) ? !empty($this->_errors[$elementName]) : !empty($this->_errors);
    }
    
    public function getError($elementName)
    {
        return $this->_errors[$elementName];
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getClickedButton()
    {
        return $this->_clickedButton;
    }
    
    public function getClickedButtonName()
    {
        if (!$this->getClickedButton()
            || empty($this->values[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME])
        ) return false;
        
        $keys = array_keys($this->values[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]);
        return array_shift($keys);
    }

    public function setClickedButton(array $elementData)
    {
        $this->_clickedButton = $elementData;

        return $this;
    }

    public function isSubmitted()
    {
        if (!isset($this->_isSubmitted)) {
            if (isset($this->settings['#submitted'])) {
                $this->_isSubmitted = $this->settings['#submitted'];
            } else {
                $this->_isSubmitted = strcasecmp($_SERVER['REQUEST_METHOD'], $this->settings['#method']) === 0
                    && ($this->settings['#build_id'] === false || isset($this->values[Sabai_Addon_Form::FORM_BUILD_ID_NAME]));
            }
        }
        return $this->_isSubmitted;
    }

    public function isSubmitSuccess()
    {
        return $this->_submitSuccess;
    }

    public function getValue()
    {
        $args = func_get_args();
        if (is_array($args[0])) {
            $args = $args[0];
        }
        $value = $this->values;
        foreach ($args as $arg) {
            if (strpos($arg, ']')) {
                if ($_args = explode('[', str_replace(']', '', $arg))) {
                    foreach ($_args as $_arg) {
                        if (isset($value[$_arg])) {
                            $value = $value[$_arg];
                        } else {
                            // non-existant key, return null
                            $value = null;
                            break;
                        }
                    }
                }
                continue;
            }
            
            if (isset($value[$arg])) {
                $value = $value[$arg];
            } else {
                // non-existant key, return null
                $value = null;
                break;
            }
        }

        return $value;
    }

    public function getName()
    {
        $args = func_get_args();
        if (is_array($args[0])) $args = $args[0];
        $name = (string)array_shift($args);
        foreach ($args as $arg) {
            $name .= '[' . $arg . ']';
        }

        return $name;
    }

    public function submit($values = array(), $force = false)
    {
        $this->values = $values;
        
        // Has form been submitted?
        if (!$force && !$this->isSubmitted()) {
            return false;
        }

        $this->_originalValues = $this->values;
        
        // Process submit
        if ($this->_doSubmit()) {
            $this->_submitSuccess = true;
        } else {
            // Rebuild form to reflect changes made to values during submit
            $this->rebuild = true;
            // Restore original submit values
            $this->values = $this->_originalValues;
            unset($this->_originalValues);
        }

        if (!empty($this->settings['#enable_storage'])) {
            // Save form storage data so that it can be retrieved in subsequent steps
            $this->_addon->setFormStorage($this->settings['#build_id'], $this->storage);
        }

        // Allow form elements to cleanup things
        $this->cleanup();

        return $this->isSubmitSuccess();
    }

    private function _doSubmit()
    {
        $result = $this->_doSubmitForm();

        if (!empty($this->settings['#skip_validate'])) {
            // Skip validation and clear errors if forcing submit
            $this->settings['#validate'] = array();
            $this->_errors = array();
        } elseif (!$result) {
            // We're not forcing submit, so return false if the result so far has not been success
            return false;
        }

        // Call form level validation callbacks
        if (!empty($this->settings['#validate'])) {
            ksort($this->settings['#validate']);
            foreach ($this->settings['#validate'] as $callback) {
                // Catch errors that might occur and show them as form error
                try {
                    $this->_addon->getApplication()->CallUserFuncArray($callback, array($this));
                } catch (Sabai_IException $e) {
                    $this->setError($e->getMessage());
                }
            }

            if ($this->hasError()) return false;
        }

        // Call submit callbacks
        if (!empty($this->settings['#submit'])) {
            ksort($this->settings['#submit']);
            while (is_array(@$this->settings['#submit']) && ($callbacks = array_shift($this->settings['#submit']))) {
                foreach ($callbacks as $callback) {
                    // Catch errors that might occur and show them as form error
                    try {
                        if (false === $this->_addon->getApplication()->CallUserFuncArray($callback, array($this))) {
                            break 2;
                        }
                    } catch (Sabai_IException $e) {
                        $this->setError($e->getMessage());
                    }

                    // Abort immediately on any error
                    if ($this->hasError()) return false;
                }
            }
        }

        return !$this->hasError();
    }


    public function _doSubmitForm()
    {
        // Allow each field to work on its submitted value before being processed by the submit callbacks
        foreach (array_keys($this->_elements['#children']) as $weight) {
            foreach (array_keys($this->_elements['#children'][$weight]) as $ele_name) {
                $ele_data =& $this->_elements['#children'][$weight][$ele_name];
                if (!isset($this->values[$ele_name])) {
                    $this->values[$ele_name] = null;
                }

                // Catch any application level exception that might occur and display it as a form element error.
                try {
                    // Send form submit notification to the element
                    $this->_addon->getApplication()->Form_FieldImpl($ele_data['#type'])
                        ->formFieldOnSubmitForm($ele_name, $this->values[$ele_name], $ele_data, $this);
                } catch (Sabai_IException $e) {
                    $this->setError($e->getMessage(), $ele_data);
                } catch (Exception $e) {
                    // Do not display system error messages to the user
                    $this->_addon->getApplication()->LogError($e);
                    $this->setError(__('An error occurred while processing the form.', 'sabai'), $ele_data);
                }

                // Any error?
                if ($this->hasError($ele_name)) continue;

                // Process element level validations if any
                foreach ($ele_data['#element_validate'] as $callback) {
                    // Catch any application level exception that might occur and display it as a form element error.
                    try {
                        $this->_addon->getApplication()
                            ->CallUserFuncArray($callback, array($this, &$this->values[$ele_name], $ele_data));
                    } catch (Sabai_IException $e) {
                        $this->setError($e->getMessage(), $ele_data);
                    } catch (Exception $e) {
                        // Do not display system error messages to the user
                        $this->_addon->getApplication()->LogError($e);
                        $this->setError(__('An error occurred while processing the form.', 'sabai'), $ele_data);
                    }
                }
                // Unset value if null. This may happen when fieldset #tree is false.
                if (is_null($this->values[$ele_name])) unset($this->values[$ele_name]);
            }
        }

        return !$this->hasError();
    }

    public function cleanup()
    {
        foreach (array_keys($this->_elements['#children']) as $weight) {
            foreach (array_keys($this->_elements['#children'][$weight]) as $ele_name) {
                $ele_data =& $this->_elements['#children'][$weight][$ele_name];
                // Process cleanup.
                try {
                    $this->_addon->getApplication()->Form_FieldImpl($ele_data['#type'])
                        ->formFieldOnCleanupForm($ele_name, $ele_data, $this);
                } catch (Sabai_IException $e) {
                    // Catch any exception that might be thrown so that all elements are cleaned up properly.
                    if ($this->isSubmitted()) {
                        $this->_addon->getApplication()->LogError($e);
                    } else {
                        // Form submit did not success, so append form cleanup error to the list of form error messages
                        $this->setError($e->getMessage());
                    }
                } catch (Exception $e) {
                    // Do not display system error messages to the user
                    $this->_addon->getApplication()->LogError($e);
                    if (!$this->isSubmitted()) {
                        $this->setError(__('An error occurred while processing the form.', 'sabai'));
                    }
                }
            }
        }

        return $this;
    }

    public function render($elementsOnly = false)
    {
        // Call pre-render callbacks
        if (!empty($this->settings['#pre_render'])) {
            ksort($this->settings['#pre_render']);
            foreach ($this->settings['#pre_render'] as $callback) {
                $this->_addon->getApplication()->CallUserFuncArray($callback, array($this));
            }
        }

        $this->_renderer = $this->_htmlquickform->getRenderer();
        $this->_doRender();
        $html = $elementsOnly ? $this->_htmlquickform->renderElements($this->_renderer) : $this->_htmlquickform->render($this->_renderer);

        return array($html, implode(PHP_EOL, $this->_js));
    }
    
    public function renderArray()
    {
        // Call pre-render callbacks
        if (!empty($this->settings['#pre_render'])) {
            ksort($this->settings['#pre_render']);
            foreach ($this->settings['#pre_render'] as $callback) {
                $this->_addon->getApplication()->CallUserFuncArray($callback, array($this));
            }
        }

        $this->_renderer = new SabaiFramework_HTMLQuickForm_Renderer_Array();
        $this->_doRender();
        $this->_htmlquickform->accept($this->_renderer);
        
        return array($this->_renderer->toArray(), $this->_js);
    }

    private function _doRender()
    {
        // Notify all elements that the form is being rendered
        $this->_renderElements();

        // Add form header if any
        if (!empty($this->settings['#header'])) {
            foreach ((array)$this->settings['#header'] as $header) {
                $this->_htmlquickform->addHeader($header);
            }
        }
        // Add form JS if any
        if (!empty($this->settings['#js'])) {
            foreach ((array)$this->settings['#js'] as $js) {
                $this->addJs(str_replace(Sabai_Addon_Form::FORM_ID_PLACEHOLDER, $this->settings['#id'], $js));
            }
        }
        // Add states
        if (!empty($this->settings['#states'])) {
            $this->addJs(sprintf(
                'jQuery(document).ready(function($){
                     var states = %s;
                     SABAI.states(states, "#%s");
                     $(SABAI).bind("clonefield.sabai", function (e, data) {
                         SABAI.states(states, data.clone, true);
                     });
                 });',
                json_encode($this->settings['#states']),
                $this->settings['#id']
            ));
        }

        // Add form classes if any
        $classes = isset($this->settings['#name']) ? array('sabai-' . str_replace('_', '-', $this->settings['#name'])) : array();
        if (!empty($this->settings['#attributes']['class'])) {
            $classes[] = $this->settings['#attributes']['class'];
            $this->_htmlquickform->removeAttribute('class');
        }
        if (!empty($this->settings['#class'])) {
            $classes[] = $this->settings['#class'];
        }
        $this->_renderer->setFormClass(implode(' ', $classes));

        // Assign errors if any
        if (!empty($this->_errors)) {
            $header_added = false;
            foreach ($this->_errors as $element_name => $messages) {
                if (strlen($element_name) === 0) {
                    // Form level error
                    foreach ($messages as $message) {
                        $this->_htmlquickform->addHeader(sprintf('<div class="sabai-alert sabai-alert-danger">%s</div>', $message));
                    }
                    $header_added = true;
                } else {
                    // Mark element as error
                    $this->_renderer->setElementError($element_name, array_shift($messages));
                }
            }
            if (!$header_added) {
                $this->_htmlquickform->addHeader(sprintf(
                    '<div class="sabai-alert sabai-alert-danger"><i class="fa fa-exclamation-triangle"></i> %s</div>',
                    __('Please correct the error(s) below.', 'sabai')
                ));
            }
        }
    }

    private function _renderElements()
    {
        foreach (array_keys($this->_elements['#children']) as $weight) {
            foreach (array_keys($this->_elements['#children'][$weight]) as $ele_key) {
                $ele_data =& $this->_elements['#children'][$weight][$ele_key];
                try {
                    $this->_addon->getApplication()->Form_FieldImpl($ele_data['#type'])
                        ->formFieldOnRenderForm($ele_key, $ele_data, $this);
                } catch (Sabai_IException $e) {
                    $this->setError($e->getMessage());
                } catch (Exception $e) {
                    // Do not display system error messages to the user
                    $this->_addon->getApplication()->LogError($e);
                    $this->setError(__('An error occurred while processing the form.', 'sabai'));
                }
            }
        }
    }
        
    public function renderChildElements($name, &$data)
    {
        // Process child elements
        foreach (array_keys($data['#children']) as $weight) {
            if (!is_int($weight)) continue;

            foreach (array_keys($data['#children'][$weight]) as $ele_key) {
                $ele_data =& $data['#children'][$weight][$ele_key];
                if (is_int($ele_key) && !empty($data['#nowrap'])) {
                    try {
                        $this->_addon->getApplication()->Form_FieldImpl($ele_data['#type'])->formFieldOnRenderForm($name, $ele_data, $this);
                    } catch (Sabai_IException $e) {
                        $this->setError($e->getMessage(), $name);
                    }
                    break;
                }
                $ele_name = empty($data['#tree']) ? $ele_key : sprintf('%s[%s]', $name, $ele_key);
                try {
                    $this->_addon->getApplication()->Form_FieldImpl($ele_data['#type'])->formFieldOnRenderForm($ele_name, $ele_data, $this);
                } catch (Sabai_IException $e) {
                    $this->setError($e->getMessage(), $ele_name);
                }
            }
        }
    }
    
    public function renderElement(&$data)
    {        
        if (!isset($data['#name']) || isset($this->_rendered[$data['#name']])) return;
        
        $this->_rendered[$data['#name']] = true;
        
        if (isset($data['#id'])) {
            $this->_renderer->setElementId($data['#name'], $data['#id']);
        }

        // Define classes
        $classes = array();
        // Make it collapsible?
        if (!empty($data['#collapsible']) && strlen($data['#label'][0])) {
            $classes[] = 'sabai-form-collapsible';
            if ($data['#collapsed']) {
                $classes[] = 'sabai-form-collapsed';
            }
        }
        // Add a special css class if no label
        if (isset($data['#label']) && strlen($data['#label'][0]) === 0) {
            $classes[] = 'sabai-form-nolabel';
        }
        // Add field specific classes
        $classes[] = 'sabai-form-type-' . str_replace('_', '-', $data['#type']);
        // Hidden?
        if (!empty($data['#hidden'])) {
            $classes[] = 'sabai-hidden';
        }
        // Set class
        if (!isset($data['#class'])) {
            $data['#class'] = implode(' ', $classes);
        } else {
            $data['#class'] .= ' ' . implode(' ', $classes);
        }
        
        // Call pre-render callbacks
        if (!empty($data['#pre_render'])) {
            ksort($data['#pre_render']);
            foreach ($data['#pre_render'] as $callback) {
                $this->_addon->getApplication()->CallUserFuncArray($callback, array($this, &$data));
            }
        }
        
        $this->_renderer->setElementClass($data['#name'], $data['#class']);

        // Required?
        if ((!empty($data['#required']) && empty($data['#display_unrequired'])) || !empty($data['#display_required'])) {
            $this->_renderer->setElementRequired($data['#name']);
        }

        // Element prefix/suffix
        if (isset($data['#prefix'])) {
            $this->_renderer->setElementPrefix($data['#name'], $data['#prefix']);
        }
        if (isset($data['#suffix'])) {
            $this->_renderer->setElementSuffix($data['#name'], $data['#suffix']);
        }
        if (isset($data['#field_prefix'])) {
            $this->_renderer->setElementFieldPrefix($data['#name'], $data['#field_prefix']);
        }
        if (isset($data['#field_suffix'])) {
            $this->_renderer->setElementFieldSuffix($data['#name'], $data['#field_suffix']);
        }

        // Custom template for this field defined?
        if (isset($data['#template'])) {
            if (false === $data['#template']) {
                $this->_renderer->setElementTemplate('{element}<!-- BEGIN error_msg --><span class="sabai-form-field-error">{error}</span><!-- END error_msg -->', $data['#name']);
            } else {
                $this->_renderer->setElementTemplate($data['#template'], $data['#name']);
            }
        }
        
        // Add custom JS if any
        if (isset($data['#js'])) {
            foreach ((array)$data['#js'] as $js) {
                $this->addJs(str_replace(Sabai_Addon_Form::FORM_ID_PLACEHOLDER, $this->settings['#id'], $js));
            }
        }
    }

    public function addJs($js)
    {
        $this->_js[] = $js;

        return $this;
    }

    public function isFieldRequired(array $data)
    {        
        if (empty($data['#required'])) return false;

        if ($data['#required'] === true) return true;
        // Use callback function to determine whether the field is required at run time
        return $this->_addon->getApplication()->CallUserFuncArray($data['#required'], array($this));
    }
}