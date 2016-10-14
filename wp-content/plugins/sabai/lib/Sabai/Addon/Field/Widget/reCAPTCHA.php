<?php
class Sabai_Addon_Field_Widget_reCAPTCHA extends Sabai_Addon_Field_Widget_AbstractWidget
{
    static private $_jsLoaded;
    
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => 'reCAPTCHA',
            'field_types' => array('captcha'),
            'default_settings' => array(
                'theme' => 'light',
                'type' => 'image',
            ),
            'requirable' => false,
            'default_user_roles' => array('_guest_'),
        );
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        $form = array(
            'theme' => array(
                '#type' => 'select',
                '#title' => __('reCAPTCHA theme', 'sabai'),
                '#options' => array(
                    'light' => _x('Light', 'reCAPTCHA theme', 'sabai'),
                    'dark' => _x('Dark', 'reCAPTCHA theme', 'sabai'),
                ),
                '#default_value' => $settings['theme'],
            ),
            'type' => array(
                '#type' => 'select',
                '#title' => __('reCAPTCHA type', 'sabai'),
                '#options' => array(
                    'image' => _x('Image', 'reCAPTCHA type', 'sabai'),
                    'audio' => _x('Audio', 'reCAPTCHA type', 'sabai'),
                ),
                '#default_value' => $settings['type'],
            ),
        );
        
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $form['#header'] = array(
                '<div class="sabai-alert sabai-alert-danger">reCAPTCHA requires PHP version 5.3.x or later.</div>',
            );
        }
        
        if (!$this->_addon->getConfig('recaptcha', 'sitekey')
            || !$this->_addon->getConfig('recaptcha', 'secret')
        ) {
            $form['#header'] = array(
                '<div class="sabai-alert sabai-alert-danger">reCAPTCHA site/secret keys must be obtained from Google and configured in Field add-on settings.</div>',
            );
        }
        
        return $form;
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        if (!self::$_jsLoaded) {
            $this->_addon->getApplication()->LoadJs('https://www.google.com/recaptcha/api.js', 'recaptcha', null, false);
            self::$_jsLoaded = true;
        }
        
        $settings += $this->_addon->getConfig('recaptcha');
        return array(
            '#type' => 'item',
            '#element_validate' => array(array($this, 'validateCaptcha')),
            '#markup' => '<div class="g-recaptcha" data-sitekey="' . $settings['sitekey'] . '" data-theme="' . $settings['theme'] . '" data-type="' . $settings['type'] . '" ></div>',
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        return '<img style="vertical-align:middle;" src="' . $this->_addon->getApplication()->ImageUrl('recaptcha.jpg') . '">';
    }
    
    public function validateCaptcha($form, &$value, $element)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $form->setError('reCAPTCHA requires PHP version 5.3.x or later.');
            return;
        }
        
        if (!$this->_addon->getConfig('recaptcha', 'sitekey')
            || (!$secret = $this->_addon->getConfig('recaptcha', 'secret'))
        ) {
            $form->setError('reCAPTCHA site/secret keys must be obtained from Google and configured in Field add-on settings.');
            return;
        }
        
        if (!isset($_POST['g-recaptcha-response'])
            || !strlen($_POST['g-recaptcha-response'])
        ) {
            $form->setError(__('Please fill out this field.', 'sabai'), $element);
            return;
        }
        
        if (!class_exists('ReCaptcha', false)) {
            $path = $this->_addon->getApplication()->getAddonPath('Field') . '/lib/ReCaptcha';
            require $path . '/ReCaptcha.php';
            require $path . '/RequestMethod.php';
            require $path . '/RequestParameters.php';
            require $path . '/Response.php';
            require $path . '/RequestMethod/Post.php';
            require $path . '/RequestMethod/Socket.php';
            require $path . '/RequestMethod/SocketPost.php';
        }   
        $recaptcha = new \ReCaptcha\ReCaptcha($secret, new \ReCaptcha\RequestMethod\SocketPost());
        $response = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if (!$response->isSuccess()) {
            $form->setError(sprintf(__('Failed verifying reCAPTCHA input: %s', 'sabai'), implode(', ', $response->getErrorCodes())), $element);
        }
    }
}