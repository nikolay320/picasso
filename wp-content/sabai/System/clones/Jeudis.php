<?php
if (!class_exists('Sabai_Addon_Jeudis', false)) {
    if (!class_exists('Sabai_Addon_Directory', false)) {
        if (!include $this->getAddonPath('Directory', false) . '.php') {
            return;
        }
    }
    class Sabai_Addon_Jeudis extends Sabai_Addon_Directory {}
}