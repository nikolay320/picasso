<?php
class Sabai_Addon_Taxonomy_Entity extends Sabai_Addon_Entity_Entity
{    
    public function __construct($bundleName, $bundleType, $userId, $timestamp, $id, $title, $name, $parent)
    {
        parent::__construct($bundleName, $bundleType, array(
            'taxonomy_term_user_id' => $userId,
            'taxonomy_term_created' => $timestamp,
            'taxonomy_term_id' => $id,
            'taxonomy_term_title' => $title,
            'taxonomy_term_name' => $name,
            'taxonomy_term_parent' => $parent,
        ), 'taxonomy_body');
    }
    
    public function getType()
    {
        return 'taxonomy';
    }
    
    public function getAuthorId()
    {
        return $this->_properties['taxonomy_term_user_id'];
    }
    
    public function setAuthor(SabaiFramework_User_Identity $author)
    {
        $this->_properties['taxonomy_term_user_id'] = $author->id;
    }
        
    public function getTimestamp()
    {
        return $this->_properties['taxonomy_term_created'];
    }
    
    public function getId()
    {
        return $this->_properties['taxonomy_term_id'];
    }

    public function getTitle()
    {
        return $this->_properties['taxonomy_term_title'];
    }
    
    public function getSlug()
    {
        return $this->_properties['taxonomy_term_name'];
    }
    
    public function getUrlPath(Sabai_Addon_Entity_Model_Bundle $bundle, $path)
    {
        return $bundle->getPath() . '/' . rawurlencode($this->_properties['taxonomy_term_name']) . $path;
    }
    
    public function isPropertyModified($name, $value)
    {
        switch ($name) {
            case 'taxonomy_term_title':
                return $value != $this->_properties['taxonomy_term_title'];
            case 'taxonomy_term_parent':
                return $value != $this->_properties['taxonomy_term_parent'];
            case 'taxonomy_term_name':
                return $value != $this->_properties['taxonomy_term_name'];
        }
    }
        
    public function getParentId()
    {
        return $this->_properties['taxonomy_term_parent'];
    }
    
    public function getGuestAuthorInfo(){}
    
    public function getActivity(){}
    
    public function isFeatured()
    {
        return false;
    }
}