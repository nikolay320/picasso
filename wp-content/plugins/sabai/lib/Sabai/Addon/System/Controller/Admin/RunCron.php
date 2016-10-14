<?php
class Sabai_Addon_System_Controller_Admin_RunCron extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        $context->addTemplate('system_admin_runcron');
        $context->logs = $this->Cron(0);
    }
}