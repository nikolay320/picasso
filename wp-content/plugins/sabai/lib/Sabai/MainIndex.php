<?php
class Sabai_MainIndex extends Sabai_Controller
{
    private static $_done = false;

    protected function _doExecute(Sabai_Context $context)
    {
        // Prevent recursive routing
        if (!self::$_done) {
            self::$_done = true;
        }
    }
}