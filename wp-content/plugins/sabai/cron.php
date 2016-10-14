<?php
require_once dirname(__FILE__) . '/../../../wp-load.php';

// Make sure the Sabai plugin is activated
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if (!is_plugin_active('sabai/sabai.php')) {
    exit;
}

// Define the SABAI_CRON_PHP_SAPI_NAME constant with the name of command line interface
// of your system if it is other than the default "cli"
if (!defined('SABAI_CRON_PHP_SAPI_NAME')) {
    define('SABAI_CRON_PHP_SAPI_NAME', 'cli');
}

// Make sure the request is coming from the command line
if (php_sapi_name() !== SABAI_CRON_PHP_SAPI_NAME) exit;

$log = get_sabai()->Cron();

// Print out logs
echo implode(PHP_EOL, (array)$log) . PHP_EOL;