<?php
if (!class_exists('Sabai_Addon_Idees', false)) {
    if (!class_exists('Sabai_Addon_Directory', false)) {
        if (!include $this->getAddonPath('Directory', false) . '.php') {
            return;
        }
    }
    class Sabai_Addon_Idees extends Sabai_Addon_Directory {}
}