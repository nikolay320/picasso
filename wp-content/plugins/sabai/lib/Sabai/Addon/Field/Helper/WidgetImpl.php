<?php
class Sabai_Addon_Field_Helper_WidgetImpl extends Sabai_Helper
{
    private $_impls = array();

    /**
     * Gets an implementation of Sabai_Addon_Field_IWidget interface for a given widget type
     * @param Sabai $application
     * @param string $widget
     */
    public function help(Sabai $application, $widget, $returnFalse = false)
    {
        if (!isset($this->_impls[$widget])) {
            $widgets = $application->Field_Widgets();
            // Valid widget type?
            if (!isset($widgets[$widget])
                || (!$application->isAddonLoaded($widgets[$widget]))
            ) {
                if ($returnFalse) return false;
                throw new Sabai_UnexpectedValueException(sprintf('Invalid widget type: %s', $widget));
            }
            $this->_impls[$widget] = $application->getAddon($widgets[$widget])->fieldGetWidget($widget);
        }

        return $this->_impls[$widget];
    }
}