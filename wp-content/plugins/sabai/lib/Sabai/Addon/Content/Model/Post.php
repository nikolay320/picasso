<?php
class Sabai_Addon_Content_Model_Post extends Sabai_Addon_Content_Model_Base_Post
{
    public function toEntity()
    {
        return new Sabai_Addon_Content_Entity(
            $this->entity_bundle_name,
            $this->entity_bundle_type,
            $this->User,
            $this->published,
            $this->id,
            $this->title,
            $this->status,
            $this->views,
            $this->slug
        );
    }
}

class Sabai_Addon_Content_Model_PostRepository extends Sabai_Addon_Content_Model_Base_PostRepository
{
}