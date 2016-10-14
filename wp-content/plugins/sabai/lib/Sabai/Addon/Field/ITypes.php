<?php
interface Sabai_Addon_Field_ITypes
{
    public function fieldGetTypeNames();
    public function fieldGetType($name);
}