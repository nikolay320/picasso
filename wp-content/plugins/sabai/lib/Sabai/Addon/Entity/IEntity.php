<?php
interface Sabai_Addon_Entity_IEntity extends Serializable
{
    public function getId();
    public function getType();
    public function getTimestamp();
    public function getBundleName();
    public function getBundleType();
    public function getTitle();
    public function getAuthorId();
    public function getFieldValue($name);
    public function getUrlPath(Sabai_Addon_Entity_Model_Bundle $bundle, $path);
    public function initFields(array $values, array $types);
    public function getFieldValues($withProperty = false);
    public function getContent();
    public function getGuestAuthorInfo();
    public function getActivity();
    public function isFeatured();
    public function setAuthor(SabaiFramework_User_Identity $author);
}