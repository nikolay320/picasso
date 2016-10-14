<?php
class Sabai_Addon_Time extends Sabai_Addon
    implements Sabai_Addon_Form_IFields,
               Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets,
               Sabai_Addon_Field_IRenderers,
               Sabai_Addon_Field_IFilters
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';

    public function formGetFieldTypes()
    {
        return array('time_time');
    }

    public function formGetField($type)
    {
        return new Sabai_Addon_Time_FormField($this, $type);
    }

    public function fieldGetTypeNames()
    {
        return array('time_time');
    }

    public function fieldGetType($name)
    {
        return new Sabai_Addon_Time_FieldType($this, $name);
    }

    public function fieldGetWidgetNames()
    {
        return array('time_time');
    }

    public function fieldGetWidget($name)
    {
        return new Sabai_Addon_Time_FieldWidget($this, $name);
    }
   
    public function fieldGetRendererNames()
    {
        return array('time_time', 'time_opening_hours');
    }

    public function fieldGetRenderer($name)
    {
        switch ($name) {
            case 'time_opening_hours':
                return new Sabai_Addon_Time_OpeningHoursFieldRenderer($this, $name);
            default:
                return new Sabai_Addon_Time_FieldRenderer($this, $name);
        }
    }
    
    public function fieldGetFilterNames()
    {
        return array('time_time');
    }

    public function fieldGetFilter($name)
    {
        return new Sabai_Addon_Time_FieldFilter($this, $name);
    }

}