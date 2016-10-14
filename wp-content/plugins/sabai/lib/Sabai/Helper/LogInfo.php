<?php
class Sabai_Helper_LogInfo extends Sabai_Helper
{
    /**
     * @param Sabai $application
     * @param string $message Message string
     */
    public function help(Sabai $application, $message)
    {
        $application->getPlatform()->logInfo($message);
    }
}