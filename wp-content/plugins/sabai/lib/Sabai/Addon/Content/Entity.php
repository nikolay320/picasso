<?php
class Sabai_Addon_Content_Entity extends Sabai_Addon_Entity_Entity
{    
    public function __construct($bundleName, $bundleType, $author, $timestamp, $id, $title, $status, $views, $slug)
    {
        parent::__construct($bundleName, $bundleType, array(
            'content_post_user_id' => $author,
            'content_post_published' => $timestamp,
            'content_post_id' => $id,
            'content_post_title' => $title,
            'content_post_status' => $status,
            'content_post_views' => $views,
            'content_post_slug' => $slug,
        ), 'content_body');
    }
    
    public function getType()
    {
        return 'content';
    }
    
    public function getAuthorId()
    {
        return $this->_properties['content_post_user_id']->id;
    }

    public function getAuthor()
    {
        return $this->_properties['content_post_user_id'];
    }
    
    public function setAuthor(SabaiFramework_User_Identity $author)
    {
        $this->_properties['content_post_user_id'] = $author;
    }
        
    public function getTimestamp()
    {
        return $this->_properties['content_post_published'];
    }
    
    public function getId()
    {
        return $this->_properties['content_post_id'];
    }

    public function getTitle()
    {
        return $this->_properties['content_post_title'];
    }
    
    public function getStatus()
    {
        return $this->_properties['content_post_status'];
    }
        
    public function getSlug()
    {
        return $this->_properties['content_post_slug'];
    }
    
    public function getViews()
    {
        return $this->_properties['content_post_views'];
    }
    
    public function getUrlPath(Sabai_Addon_Entity_Model_Bundle $bundle, $path)
    {
        return isset($bundle->info['permalink_path'])
            ? $bundle->info['permalink_path'] . '/' . (strlen($this->_properties['content_post_slug']) ? rawurlencode($this->_properties['content_post_slug']) : $this->_properties['content_post_id']) . $path
            : $bundle->getPath() . '/' . $this->_properties['content_post_id'] . $path;
    }
    
    public function isTrashed()
    {
        return $this->_properties['content_post_status'] == Sabai_Addon_Content::POST_STATUS_TRASHED;
    }
    
    public function isDraft()
    {
        return $this->_properties['content_post_status'] == Sabai_Addon_Content::POST_STATUS_DRAFT;
    }
    
    public function isPending()
    {
        return $this->_properties['content_post_status'] == Sabai_Addon_Content::POST_STATUS_PENDING;
    }
    
    public function isPublished()
    {
        return $this->_properties['content_post_status'] == Sabai_Addon_Content::POST_STATUS_PUBLISHED;
    }
    
    public function isFeatured()
    {
        return (int)$this->getSingleFieldValue('content_featured', 'value');
    }
    
    public function isPropertyModified($name, $value)
    {
        switch ($name) {
            case 'content_post_title':
                return $value != $this->_properties['content_post_title'];
            case 'content_post_published':
                return $value != $this->_properties['content_post_published'];
            case 'content_post_status':
                return $value != $this->_properties['content_post_status'];
            case 'content_post_user_id':
                return $value != $this->_properties['content_post_user_id']->id;
            case 'content_post_slug':
                return $value != $this->_properties['content_post_slug'];
        }
    }
    
    public function getActivity()
    {
        return ($value = $this->getFieldValue('content_activity')) ? $value[0] : null;
    }
    
    public function getGuestAuthorInfo()
    {
        return ($value = $this->getFieldValue('content_guest_author')) ? $value[0] : null;
    }
}