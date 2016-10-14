<?php
class SabaiFramework_User_AnonymousIdentity extends SabaiFramework_User_Identity
{
    final public function isAnonymous()
    {
        return true;
    }
}