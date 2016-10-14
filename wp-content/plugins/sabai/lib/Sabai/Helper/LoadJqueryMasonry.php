<?php
class Sabai_Helper_LoadJqueryMasonry extends Sabai_Helper
{
    public function help(Sabai $application)
    {
        $application->LoadJs('//cdnjs.cloudflare.com/ajax/libs/masonry/3.1.5/masonry.pkgd.min.js', 'jquery-masonry', 'jquery', false);
    }
}
