<?php
class Sabai_Helper_Carousel extends Sabai_Helper
{
    private static $_count = 0, $_jsLoaded;
    
    public function help(Sabai $application, array $items, array $options = array())
    {
        if (!self::$_jsLoaded) {
            $application->LoadJs('jquery.bxslider.min.js', 'jquery-bxslider', 'jquery', null, true);
            $application->LoadCss('jquery.bxslider.min.css', 'jquery-bxslider');
            if ($application->getPlatform()->isLanguageRTL()) {
                $application->LoadCss('jquery.bxslider-rtl.min.css', 'jquery-bxslider-rtl', 'jquery-bxslider');
            }
            self::$_jsLoaded = true;
        }

        // do not show controls if single item
        if (count($items) === 1) {
            $options['controls'] = $options['pager'] = false;
        }

        $ret = array(sprintf('<div class="sabai-carousel" id="sabai-carousel-%d" data-carousel-options=\'%s\'>', ++self::$_count, json_encode($options)));
        foreach ($items as $item) {
            $ret[] = '<div class="sabai-item">' . $item . '</div>';
        }
        $ret[] = '</div>';
        return implode(PHP_EOL, $ret);
    }
}
