<?php
class Sabai_Addon_Taxonomy_Model_Term extends Sabai_Addon_Taxonomy_Model_Base_Term
{
    public function toEntity()
    {
        return new Sabai_Addon_Taxonomy_Entity(
            $this->entity_bundle_name,
            $this->entity_bundle_type,
            $this->user_id,
            $this->created,
            $this->id,
            $this->title,
            $this->name,
            $this->parent
        );
    }
}

class Sabai_Addon_Taxonomy_Model_TermRepository extends Sabai_Addon_Taxonomy_Model_Base_TermRepository
{
}