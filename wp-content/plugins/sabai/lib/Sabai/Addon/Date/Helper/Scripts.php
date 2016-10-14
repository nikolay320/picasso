<?php
class Sabai_Addon_Date_Helper_Scripts extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        $application->LoadJqueryUi(array('datepicker'));
        $application->LoadJs('jquery.ui.timepicker.min.js', 'jquery-ui-timepicker', 'jquery-ui-core', null);
        // Load datepicker i18N script?
        if (SABAI_LANG !== 'en_US') {
            if (strpos(SABAI_LANG, '_')) {
                $_lang_codes = explode('_', SABAI_LANG);
                $lang_codes = array($_lang_codes[0] . '-' . $_lang_codes[1], $_lang_codes[0]);
            } else {
                $lang_codes = array(SABAI_LANG);
            }
            foreach ($lang_codes as $lang_code) {
                $lang_file = 'jquery-ui-datepicker-' . $lang_code . '.min.js';
                if (file_exists($application->getPlatform()->getAssetsDir() . '/js/' . $lang_file)) {
                    $application->LoadJs($lang_file, 'jquery-ui-datepicker-' . $lang_code, 'jquery-ui-datepicker', null);
                    break;
                }
            }
        }
        // Load script to instantiate date/time pickers
        $application->LoadJs('sabai-date-datetimepicker.min.js', 'sabai-date-datetimepicker', array('jquery-ui-datepicker', 'sabai'), null);
    }
}