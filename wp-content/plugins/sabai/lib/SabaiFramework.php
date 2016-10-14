<?php
/**
 * Short description for class
 *
 * @package    SabaiFramework
 * @copyright  Copyright (c) 2006-2013 Kazumi Ono
 * @author     Kazumi Ono <onokazu@gmail.com>
 */
class SabaiFramework
{
    const VERSION = '1.3.28';
    private static $_started = false;

    /**
     * Initializes session and other required libraries
     *
     * @param string $charset
     * @param string $lang
     * @param bool $startSession
     * @static
     */
    public static function start($charset = 'UTF-8', $lang = 'en', $startSession = true)
    {
        // Some startup initializations
        define('SABAI_CHARSET', $charset);
        define('SABAI_LANG', $lang);

        // Start session if required
        if ($startSession) {
            self::startSession();
        }

        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding(SABAI_CHARSET);
            if (function_exists('mb_regex_encoding')) {
                mb_regex_encoding(SABAI_CHARSET);
            }
            @ini_set('mbstring.http_input', 'pass');
            @ini_set('mbstring.http_output', 'pass');
            @ini_set('mbstring.substitute_character', 'none');
        }

        self::$_started = true;
    }

    public static function started()
    {
        return self::$_started;
    }
    
    public static function startSession()
    {
        if (session_id()) return;
        
        @ini_set('session.use_only_cookies', 1);
        @ini_set('session.use_trans_sid', 0);
        @ini_set('session.hash_function', 1);
        @ini_set('session.cookie_httponly', 1);
        session_start();
    }
    
    public static function autoload($className)
    {
        if (!class_exists($className)) {
            require str_replace('_', '/', $className) . '.php';
        }
    }
}