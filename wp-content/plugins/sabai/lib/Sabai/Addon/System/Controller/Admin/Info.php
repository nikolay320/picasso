<?php
class Sabai_Addon_System_Controller_Admin_Info extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = null;
        $this->_submitable = false;
        
        // Init variables
        $info = array(
            'php_version' => array('name' => 'PHP Version', 'value' => phpversion()),
            'php_mbstring' => array('name' => 'PHP Mbstring Extension', 'value' => function_exists('mb_detect_encoding') ? 'On' : 'Off'),
            'php_memory_limit' => array('name' => 'PHP Memory Limit', 'value' => ini_get('memory_limit')),
            'php_upload_max_filesize' => array('name' => 'PHP Upload Maximum File Size', 'value' => ini_get('upload_max_filesize')),
            'php_post_max_size' => array('name' => 'PHP POST Maximum Size', 'value' => ini_get('post_max_size')),
            'php_self' => array('name' => '$_SERVER[\'PHP_SELF\']', 'value' => isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : 'N/A'),
            'php_request_uri' => array('name' => '$_SERVER[\'REQUEST_URI\']', 'value' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'N/A'),
            'php_orig_request_uri' => array('name' => '$_SERVER[\'ORIG_REQUEST_URI\']', 'value' => isset($_SERVER['ORIG_REQUEST_URI']) ? $_SERVER['ORIG_REQUEST_URI'] : 'N/A'),
            'php_query_string' => array('name' => '$_SERVER[\'QUERY_STRING\']', 'value' => isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : 'N/A'),
            'php_script_name' => array('name' => '$_SERVER[\'SCRIPT_NAME\']', 'value' => isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'N/A'),
            'sabai_install_log' => array('name' => 'Sabai Install Log', 'value' => $this->getPlatform()->getOption('install_log')),
            'sabai_addon_local_log' => array('name' => 'Sabai Load Add-on Log', 'value' => $this->getPlatform()->getOption('addons_local_log')),
            'site_url' => array('name' => 'Site URL', 'value' => $this->getPlatform()->getSiteUrl()),
            'home_url' => array('name' => 'Home URL', 'value' => $this->getPlatform()->getHomeUrl()),
            'site_admin_url' => array('name' => 'Site Admin URL', 'value' => $this->getPlatform()->getSiteAdminUrl()),
            'package_path' => array('name' => 'Package Path', 'value' => $this->PackagePath()),
        );
        
        // Init form
        $form = array(
            'info' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'name' => 'Name',
                    'value' => 'Value',
                ),
                '#options' => array(),
                '#disabled' => true,
            ),
        );

        foreach ($this->Filter('system_admin_info', $info) as $info_key => $info_data) {
            $form['info']['#options'][$info_key] = array(
                'name' => $info_data['name'],
                'value' => Sabai::h($info_data['value']),
            );
        }
        
        return $form;
    }
}