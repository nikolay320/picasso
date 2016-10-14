<?php
class Sabai_Helper_AnonymousUserIdentity extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        return $application->getPlatform()->getUserIdentityFetcher()->getAnonymous();
    }
}