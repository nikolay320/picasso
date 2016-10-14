<?php
if (!class_exists('Sabai_Addon_OUTILS', false)) {
    if (!class_exists('Sabai_Addon_Directory', false)) {
        if (!include $this->getAddonPath('Directory', false) . '.php') {
            return;
        }
    }
    class Sabai_Addon_OUTILS extends Sabai_Addon_Directory {}
}