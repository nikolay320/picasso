<?php
class Sabai_Helper_LoadJqueryUi extends Sabai_Helper
{
    private $_loaded = false, $_loadedCss = false;

    public function help(Sabai $application, array $components = null, $loadCss = false)
    {
        if (!$this->_loaded) {
            $application->LoadJs('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js', 'jquery-ui', 'jquery', false);
            $this->_loaded = true;
        }
        if ($loadCss && $this->_loadedCss) {
            $application->LoadCss('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/ui-lightness/jquery-ui.min.css', 'jquery-ui', null, false);
            $this->_loadedCss = true;
        }
    }
}
