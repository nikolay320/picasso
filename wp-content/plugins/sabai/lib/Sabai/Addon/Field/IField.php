<?php
interface Sabai_Addon_Field_IField
{
    public function getFieldId();
    public function getFieldType();
    public function getFieldName();
    public function getFieldTitle();
    public function getFieldDescription();
    public function getFieldSettings();
    public function getFieldDefaultValue();
    public function getFieldWidget();
    public function getFieldWidgetSettings();
    public function getFieldRenderer();
    public function getFieldRendererSettings();
}