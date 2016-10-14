<?php
abstract class Sabai_Addon_Entity_GuestAuthorFieldWidget extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected $_fieldTypes;

    public function __construct(Sabai_Addon $addon, $name, $fieldTypes)
    {
        parent::__construct($addon, $name);
        $this->_fieldTypes = (array)$fieldTypes;
    }

    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Guest author field', 'sabai'),
            'field_types' => $this->_fieldTypes,
            'default_settings' => array(
                'size' => 'large',
                'hide_website_field' => false,
                'hide_header_msg' => false,
                'header_msg' => __('Post as a guest by filling out the fields below or <a href="%s" class="sabai-login popup-login">login</a> if you already have an account.', 'sabai'),
                'checkmail' => true,
                'checkmx' => false,
            ),
            'requirable' => false,
            'disableable' => false,
            'preview_info' => __('visible to anonymous users only', 'sabai'),
            'is_fieldset' => true,
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        $ret = array(
            'size' => array(
                '#type' => 'select',
                '#title' => __('Field size', 'sabai'),
                '#options' => array(
                    'small' => __('Small', 'sabai'),
                    'medium' => __('Medium', 'sabai'),
                    'large' => __('Large (responsive)', 'sabai'),
                ),
                '#default_value' => $settings['size'],
            ),
            'hide_website_field' => array(
                '#type' => 'checkbox',
                '#title' => __('Hide the Website field', 'sabai'),
                '#default_value' => $settings['hide_website_field'],
            ),
            'hide_header_msg' => array(
                '#type' => 'checkbox',
                '#title' => __('Hide the header message', 'sabai'),
                '#default_value' => $settings['hide_header_msg'],
            ),
            'header_msg' => array(
                '#type' => 'textarea',
                '#rows' => 3,
                '#title' => __('Header message', 'sabai'),
                '#default_value' => $settings['header_msg'],
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[hide_header_msg][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => 0),
                    ),
                ),
            ),
            'checkmail' => array(
                '#type' => 'checkbox',
                '#title' => __('Do not allow e-mail address used by registered users', 'sabai'),
                '#default_value' => $settings['checkmail'],
            ),
        );
        if ($this->_canCheckMx()) {
            $ret['checkmx'] = array(
                '#type' => 'checkbox',
                '#title' => __('Check MX record of e-mail address', 'sabai'),
                '#default_value' => $settings['checkmx'],
            );
        }
        
        return $ret;
    }
    
    protected function _canCheckMx()
    {
        return function_exists('checkdnsrr')
            && version_compare(PHP_VERSION, '5.2.4', '>='); // 2nd parameter of checkdnsrr added in 5.2.4
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $is_guest = $this->_addon->getApplication()->getUser()->isAnonymous();
        $is_edit = isset($value);
        if (!$is_guest) {
            if (!$this->_addon->getApplication()->getUser()->isAdministrator()) {
                return;
            }
            if (!$is_edit) {
                // This field is for guest authors only unless editing an existing field
                return;
            }
        }
        if ($is_guest && $is_edit) {
            $ret = array(
                '#type' => 'fieldset',
                '_email' => array(
                    '#type' => 'item',
                    '#title' => __('E-mail', 'sabai'),
                    '#default_value' => isset($value['email']) ? $value['email'] : null,
                    '#weight' => 4,
                ),
                '_name' => array(
                    '#type' => 'item',
                    '#default_value' => isset($value['name']) ? $value['name'] : null,
                    '#title' => __('Name', 'sabai'),
                    '#weight' => 2,
                ),
                'email' => array(
                    '#type' => 'hidden',
                    '#default_value' => isset($value['email']) ? $value['email'] : null,
                ),
                'name' => array(
                    '#type' => 'hidden',
                    '#default_value' => isset($value['name']) ? $value['name'] : null,
                ),
            );
            if (!$settings['hide_website_field']) {
                $ret['_url'] = array(
                    '#type' => 'item',
                    '#title' => __('Website', 'sabai'),
                    '#default_value' => isset($value['url']) ? $value['url'] : null,
                    '#weight' => 10,
                );
                $ret['url'] = array(
                    '#type' => 'hidden',
                    '#default_value' => isset($value['url']) ? $value['url'] : null,
                );
            }
        } else {
            $ret = array(
                '#type' => 'fieldset',
                'email' => array(
                    '#type' => $is_guest && $is_edit ? 'hidden' : 'email',
                    '#title' => __('E-mail', 'sabai'),
                    '#default_value' => isset($value['email']) ? $value['email'] : null,
                    '#char_validation' => 'email',
                    '#size' => $this->_getSize($settings['size']),
                    '#weight' => 4,
                    '#required' => true,
                    '#element_validate' => array(array(array($this, 'validateGuestEmail'), array($settings['checkmail'], $settings['checkmx']))),
                ),
                'name' => array(
                    '#type' => $is_guest && $is_edit ? 'hidden' : 'textfield',
                    '#default_value' => isset($value['name']) ? $value['name'] : null,
                    '#title' => __('Name', 'sabai'),
                    '#required' => true,
                    '#size' => $this->_getSize($settings['size']),
                    '#weight' => 2,
                ),
            );
            if (!$settings['hide_website_field']) {
                $ret['url'] = array(
                    '#type' => $is_guest && $is_edit ? 'hidden' : 'url',
                    '#title' => __('Website', 'sabai'),
                    '#default_value' => isset($value['url']) ? $value['url'] : null,
                    '#char_validation' => 'url',
                    '#size' => $this->_getSize($settings['size']),
                    '#weight' => 10,
                );
            }
        }
        if (!$is_guest) {
            $ret += array(
                'ip' => array(
                    '#type' => 'textfield',
                    '#title' => __('IP Address', 'sabai'),
                    '#value' => $value['ip'],
                    '#weight' => 15,
                    '#disabled' => true,
                ),
                'user_agent' => array(
                    '#type' => 'textarea',
                    '#rows' => 2,
                    '#title' => __('User Agent', 'sabai'),
                    '#value' => $value['user_agent'],
                    '#weight' => 20,
                    '#disabled' => true,
                ),
            );
        } else {
            if (!$is_edit && !$settings['hide_header_msg']) {
                $ret['login_or_register'] = array(
                    '#type' => 'item',
                    '#weight' => 0,
                    '#description' => sprintf(
                        $settings['header_msg'], 
                        $this->_addon->getApplication()->LoginUrl(Sabai_Request::url())
                    ),
                );
            }
        }
        return $ret;
    }
    
    public function validateGuestEmail($form, &$value, $element, $checkMail, $checkMx)
    {
        if ($checkMail) {
            $identity = $this->_addon->getApplication()->UserIdentityByEmail($value);
            if (!$identity->isAnonymous()) {
                // There is already a registered user with that email address
                $form->setError(__('The email address may not be used.', 'sabai'), $element);
                return;
            }
        }
        if ($checkMx && $this->_canCheckMx()) {
            list(, $domain) = explode('@', $value);
            if (!$domain || !checkdnsrr($domain, 'MX')) {
                $form->setError(__('Invalid domain name.', 'sabai'), $element);
            }
        }
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        if (!$settings['hide_website_field']) {
            return sprintf(
                '<div>
  <label>%1$s<span class="sabai-fieldui-widget-required">*</span></label>
  <input type="text" disabled="disabled"%4$s />
</div>
<div>
  <label>%2$s<span class="sabai-fieldui-widget-required">*</span></label>
  <input type="text" disabled="disabled"%4$s />
</div>
<div>
  <label>%3$s</label>
  <input type="text" disabled="disabled"%4$s />
</div>',
                __('Name', 'sabai'),
                __('E-mail', 'sabai'),
                __('Website', 'sabai'),
                $this->_getSizeHtmlAttr($settings['size'])
           );
        }
        return sprintf(
            '<div>
  <label>%1$s<span class="sabai-fieldui-widget-required">*</span></label>
  <input type="text" disabled="disabled"%3$s />
</div>
<div>
  <label>%2$s<span class="sabai-fieldui-widget-required">*</span></label>
  <input type="text" disabled="disabled"%3$s />
</div>',
            __('Name', 'sabai'),
            __('E-mail', 'sabai'),
            $this->_getSizeHtmlAttr($settings['size'])
        );
    }
    
    private function _getSizeHtmlAttr($size, $default = ' style="width:100%;"')
    {
        return ($size = $this->_getSize($size)) ? sprintf(' size="%d"', $size) : $default;
    }
    
    private function _getSize($size)
    {
        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);
        return $size && isset($sizes[$size]) ? $sizes[$size] : null;
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $settings, array $parents = array())
    {

    }
}
