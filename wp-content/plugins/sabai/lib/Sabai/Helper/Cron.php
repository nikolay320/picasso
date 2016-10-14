<?php
class Sabai_Helper_Cron extends Sabai_Helper
{
    public function help(Sabai $application, $lastRunTimestamp = null)
    {
        // Get cached last run timestamp if not speficied
        if (!isset($lastRunTimestamp)) {
            $lastRunTimestamp = $application->getPlatform()->getCache('sabai_cron_lastrun');
        }
        // Init logs
        $logs = new ArrayObject();
        if (!empty($lastRunTimestamp)) { // 0 forces all cron hooks to run
            $logs[] = 'Cron last run - ' . $application->DateTime($lastRunTimestamp);
        }        
        $logs[] = 'Cron started - ' . $application->DateTime(time());
        
        $application->Action('sabai_run_cron', array(intval($lastRunTimestamp), &$logs));
        
        $application->getPlatform()->setCache(time(), 'sabai_cron_lastrun', 0);
        
        $logs[] = 'Cron complete - ' . $application->DateTime(time());
        
        $application->getPlatform()->updateSabaiOption('cron_log', implode(PHP_EOL, (array)$logs));

        return $logs;
    }
}
