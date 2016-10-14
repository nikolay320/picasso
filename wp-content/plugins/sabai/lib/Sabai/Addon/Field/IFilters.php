<?php
interface Sabai_Addon_Field_IFilters
{
    public function fieldGetFilterNames();
    public function fieldGetFilter($filterName);
}