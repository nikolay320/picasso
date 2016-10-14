<?php
class Sabai_Helper_Token extends Sabai_Helper
{
    public function help(Sabai $application, $tokenId, $tokenLifetime = 1800, $reobtainable = false)
    {
        return SabaiFramework_Token::create($tokenId, $tokenLifetime, $reobtainable)->getValue();
    }
}