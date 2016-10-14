<?php
interface Sabai_Addon_Field_IQuery
{
    public function startCriteriaGroup($inGroupOperator = 'AND');
    public function finishCriteriaGroup($operator = null);
    public function addIsCriteria(Sabai_Addon_Field_IField $field, $column, $value);
    public function addIsNotCriteria(Sabai_Addon_Field_IField $field, $column, $value);
    public function addIsNullCriteria(Sabai_Addon_Field_IField $field, $column);
    public function addIsNotNullCriteria(Sabai_Addon_Field_IField $field, $column);
    public function addInCriteria(Sabai_Addon_Field_IField $field, $column, array $values);
    public function addNotInCriteria(Sabai_Addon_Field_IField $field, $column, array $values);
    public function addIsOrGreaterThanCriteria(Sabai_Addon_Field_IField $field, $column, $value);
    public function addIsOrSmallerThanCriteria(Sabai_Addon_Field_IField $field, $column, $value);
    public function addIsGreaterThanCriteria(Sabai_Addon_Field_IField $field, $column, $value);
    public function addIsSmallerThanCriteria(Sabai_Addon_Field_IField $field, $column, $value);
    public function addStartsWithCriteria(Sabai_Addon_Field_IField $field, $column, $value);
    public function addEndsWithCriteria(Sabai_Addon_Field_IField $field, $column, $value);
    public function addContainsCriteria(Sabai_Addon_Field_IField $field, $column, $value);
    public function addSort(Sabai_Addon_Field_IField $field, $column, $order = 'ASC');
    public function setGroup(Sabai_Addon_Field_IField $field, $column, $order = null, $alias = null);
}