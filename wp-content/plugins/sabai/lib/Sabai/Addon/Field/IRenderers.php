<?php
interface Sabai_Addon_Field_IRenderers
{
    public function fieldGetRendererNames();
    public function fieldGetRenderer($name);
}