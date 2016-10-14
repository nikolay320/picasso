<?php
class Sabai_Addon_Taxonomy_EntityType implements Sabai_Addon_Entity_IType
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Taxonomy $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function entityTypeGetInfo($key = null)
    {
        $info = array(
            'reserved_bundle_names' => array('term'),
            'table_name' => $this->_addon->getApplication()->getDB()->getResourcePrefix() . 'taxonomy_term',
            'table_id_key' => 'term_id',
            'properties' => array(
                'term_title' => array(
                    'type' => 'taxonomy_term_title',
                    'title' => __('Title', 'sabai'),
                    'required' => true,
                    'column_type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR, 
                ),
                'term_id' => array(
                    'type' => 'taxonomy_term_id',
                    'title' => __('ID', 'sabai'),
                    'column_type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER, 
                ),
                'term_name' => array(
                    'type' => 'taxonomy_term_name',
                    'title' => __('Slug', 'sabai'),
                    'column_type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR, 
                ),
                'term_created' => array(
                    'type' => 'taxonomy_term_created',
                    'title' => __('Creation Date', 'sabai'),
                    'column_type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER, 
                ),
                'term_user_id' => array(
                    'type' => 'taxonomy_term_user_id',
                    'title' => __('Author', 'sabai'),
                    'column_type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER, 
                ),
                'term_entity_bundle_name' => array(
                    'type' => 'taxonomy_term_entity_bundle_name',
                    'title' => __('Taxonomy', 'sabai'),
                    'column_type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                ),
                'term_entity_bundle_type' => array(
                    'type' => 'taxonomy_term_entity_bundle_type',
                    'title' => __('Taxonomy', 'sabai'),
                    'column_type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                ),
                'term_parent' => array(
                    'type' => 'taxonomy_term_parent',
                    'title' => __('Parent Term', 'sabai'),
                    'column_type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER, 
                ),
            ),
        );

        return isset($key) ? @$info[$key] : $info;
    }

    public function entityTypeGetEntityById($entityId)
    {
        $term = $this->_addon->getModel('Term')->fetchById($entityId);
        return $term ? $term->toEntity() : false;
    }

    public function entityTypeGetEntitiesByIds(array $entityIds)
    {
        $entities = array();
        foreach ($this->_addon->getModel('Term')
            ->fetchByIds($entityIds)
        as $term) {
            $entities[] = $term->toEntity();
        }
        return $entities;
    }
    
    public function entityTypeCreateEntity(Sabai_Addon_Entity_Model_Bundle $bundle, array $properties, SabaiFramework_User_Identity $identity)
    {
        $term = $this->_addon->getModel()->create('Term')->markNew();
        $term->entity_bundle_name = $bundle->name;
        $term->entity_bundle_type = $bundle->type;
        $term->User = $identity;
        $term->title = $properties['taxonomy_term_title'];
        if (!empty($bundle->info['taxonomy_hierarchical'])) {
            $term->parent = (int)@$properties['taxonomy_term_parent'];
        }
        $term->name = $this->_getUniqueSlug($bundle, $this->_addon->getApplication()->Slugify(isset($properties['taxonomy_term_name']) ? $properties['taxonomy_term_name'] : $properties['taxonomy_term_title']));
        if (!empty($properties['taxonomy_term_id'])) {
            $term->id = $properties['taxonomy_term_id'];
        }
        $this->_addon->getModel()->commit();
        return $term->toEntity();
    }

    public function entityTypeUpdateEntity(Sabai_Addon_Entity_IEntity $entity, Sabai_Addon_Entity_Model_Bundle $bundle, array $properties)
    {
        if (!$term = $this->_addon->getModel('Term')->fetchById($entity->getId())) {
            throw new Sabai_RuntimeException('Cannot save non existent entity.');
        }
        
        foreach ($properties as $property => $value) {
            switch ($property) {
                case 'taxonomy_term_title':
                    $term->title = $value;
                    break;
                case 'taxonomy_term_name':
                    $term->name = $this->_getUniqueSlug($bundle, $this->_addon->getApplication()->Slugify($value), $term->id);
                    break;
                case 'taxonomy_term_parent':
                    if ($bundle->info['taxonomy_hierarchical'] && isset($properties['taxonomy_term_parent'])) {
                        $term->parent = $value;
                    }
                    break;
            }
        }
        if (!$term->name) {
            $term->name = $this->_getUniqueSlug($bundle, $this->_addon->getApplication()->Slugify($term->title), $term->id);
        }
        $this->_addon->getModel()->commit();
        return $term->toEntity();
    }

    public function entityTypeDeleteEntities(array $entities)
    {
        $entity_ids = array();
        foreach ($entities as $entity) {
            $entity_ids[] = $entity->getId();
        }
        foreach ($this->_addon->getModel('Term')->fetchByIds($entity_ids) as $term) {
            $term->markRemoved();
        }
        $this->_addon->getModel()->commit();
    }

    public function entityTypeSearchEntitiesByBundle($keyword, $bundle, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        $entities = array();
        foreach ($this->_addon->getModel('Term')
            ->entityBundleName_is($bundle->name)
            ->title_contains($keyword)
            ->fetch($limit, $offset, $sort, $order)
        as $term) {
            $entities[] = $term->toEntity();
        }
        return $entities;
    }
    
    private function _getUniqueSlug($bundle, $slug, $termId = null)
    {
        $gateway = $this->_addon->getModel()->getGateway('Term');
        if (!$gateway->slugExists($bundle, $slug, $termId)) {
            return $slug;
        }
        
        $count = 0;
        do {
            ++$count;
            $slug_with_number = $slug . '-' . $count;
        } while ($gateway->slugExists($bundle, $slug_with_number, $termId));
        
        return $slug_with_number;
    }
}