<?php
class Sabai_Addon_Content_FeaturedFieldFilter extends Sabai_Addon_Entity_FeaturedFieldFilter
{    
    public function __construct(Sabai_Addon $addon, $name)
    {
        parent::__construct($addon, $name, 'content_featured');
    }
}