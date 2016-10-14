<?php
class Sabai_Helper_Platform extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        return $application->getPlatform();
    }
}