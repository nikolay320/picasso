<?php
class SabaiFramework_User_RegisteredIdentity extends SabaiFramework_User_Identity
{
    final public function isAnonymous()
    {
        return false;
    }
}