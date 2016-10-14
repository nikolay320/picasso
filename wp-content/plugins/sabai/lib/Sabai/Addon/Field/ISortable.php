<?php
interface Sabai_Addon_Field_ISortable
{
    public function fieldSortableDoSort(Sabai_Addon_Field_IQuery $query, $fieldName, array $args = null);
}