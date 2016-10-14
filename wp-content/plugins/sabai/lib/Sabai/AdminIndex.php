<?php
class Sabai_AdminIndex extends Sabai_Controller
{
    private static $_done = false;

    protected function _doExecute(Sabai_Context $context)
    {
        // Prevent recursive routing
        if (!self::$_done) {
            $this->_parent->forward('/settings', $context);
            self::$_done = true;
        }
    }
}