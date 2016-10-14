<?php
class Sabai_Helper_LogError extends Sabai_Helper
{
    /**
     * @param Sabai $application
     * @param string $error Message string or an Exception object
     */
    public function help(Sabai $application, $error)
    {
        $application->getPlatform()->logError($error);
    }
}