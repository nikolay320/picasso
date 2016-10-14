<?php
class Sabai_Web extends Sabai
{
    protected function _createResponse()
    {
        return new Sabai_WebResponse();
    }
}