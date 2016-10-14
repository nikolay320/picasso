<?php
interface SabaiFramework_Application_Route
{
    public function __toString();
    public function isForward();
    public function getParams();
    public function getController();
    public function getControllerArgs();
    public function getControllerFile();
}