<?php
if (!class_exists('Sabai_Addon_Societes', false)) {
    if (!class_exists('Sabai_Addon_Directory', false)) {
        if (!include $this->getAddonPath('Directory', false) . '.php') {
            return;
        }
    }
    class Sabai_Addon_Societes extends Sabai_Addon_Directory {}
}