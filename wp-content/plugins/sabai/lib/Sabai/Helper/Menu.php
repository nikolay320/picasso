<?php
class Sabai_Helper_Menu extends Sabai_Helper
{
    public function help(Sabai $application, array $menus)
    {
        return '<ul class="sabai-menu"><li>' . implode('</li><li>', $menus) . '</li></ul>';
    }
}