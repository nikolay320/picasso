<?php
class Sabai_Helper_TokenValidate extends Sabai_Helper
{
    public function help(Sabai $application, $tokenValue, $tokenId, $reuseable = false)
    {
        return SabaiFramework_Token::validate($tokenValue, $tokenId, $reuseable);
    }
}