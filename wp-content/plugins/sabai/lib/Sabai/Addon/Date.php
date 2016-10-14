<?php
class Sabai_Addon_Date extends Sabai_Addon
    implements Sabai_Addon_Form_IFields,
               Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets,
               Sabai_Addon_Field_IRenderers,
               Sabai_Addon_Field_IFilters
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';

    public function formGetFieldTypes()
    {
        return array('date_datepicker');
    }

    public function formGetField($type)
    {
        return new Sabai_Addon_Date_DatePickerFormField($this, $type);
    }

    public function fieldGetTypeNames()
    {
        return array('date_timestamp');
    }

    public function fieldGetType($name)
    {
        return new Sabai_Addon_Date_TimestampFieldType($this, $name);
    }

    public function fieldGetWidgetNames()
    {
        return array('date_datepicker');
    }

    public function fieldGetWidget($name)
    {
        return new Sabai_Addon_Date_DatePickerFieldWidget($this, $name);
    }
    
    public function fieldGetRendererNames()
    {
        return array('date_timestamp');
    }

    public function fieldGetRenderer($name)
    {
        return new Sabai_Addon_Date_FieldRenderer($this, $name);
    }
    
    public function fieldGetFilterNames()
    {
        return array('date_datepicker');
    }

    public function fieldGetFilter($name)
    {
        return new Sabai_Addon_Date_DatePickerFieldFilter($this, $name);
    }
}