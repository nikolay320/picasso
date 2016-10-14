<?php
interface Sabai_Addon_Field_IWidgets
{
    public function fieldGetWidgetNames();
    public function fieldGetWidget($name);
}