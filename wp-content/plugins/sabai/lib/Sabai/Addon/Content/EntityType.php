<?php
class Sabai_Addon_Content_EntityType implements Sabai_Addon_Entity_IType
{
    private $_addon, $_name, $_info;

    public function __construct(Sabai_Addon_Content $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function entityTypeGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            $this->_info = array(
                'reserved_bundle_names' => array('post'),
                'table_name' => $this->_addon->getApplication()->getDB()->getResourcePrefix() . 'content_post',
                'table_id_key' => 'post_id',
                'properties' => array(
                    'post_title' => array(
                        'type' => 'content_post_title',
                        'title' => __('Title', 'sabai'),
                        'column_type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR, 
                    ),
                    'post_status' => array(
                        'type' => 'content_post_status',
                        'title' => __('Status', 'sabai'),
                        'column_type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR, 
                    ),
                    'post_published' => array(
                        'type' => 'content_post_published',
                        'title' => __('Published date', 'sabai'),
                        'column_type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    ),
                    'post_views' => array(
                        'type' => 'content_post_views',
                        'title' => __('Views', 'sabai'),
                        'column_type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER, 
                    ),
                    'post_id' => array(
                        'type' => 'content_post_id',
                        'title' => __('ID', 'sabai'),
                        'column_type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    ),
                    'post_user_id' => array(
                        'type' => 'content_post_user_id',
                        'title' => __('Author', 'sabai'),
                        'column_type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    ),
                    'post_entity_bundle_name' => array(
                        'type' => 'content_post_entity_bundle_name',
                        'title' => __('Content type', 'sabai'),
                        'column_type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    ),
                    'post_entity_bundle_type' => array(
                        'type' => 'content_post_entity_bundle_type',
                        'title' => __('Content type', 'sabai'),
                        'column_type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    ),
                    'post_slug' => array(
                        'type' => 'content_post_slug',
                        'title' => __('Slug', 'sabai'),
                        'column_type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR, 
                    ),
                ),
            );
        }

        return isset($key) ? @$this->_info[$key] : $this->_info;
    }

    public function entityTypeGetEntityById($entityId)
    {
        $post = $this->_addon->getModel('Post')->fetchById($entityId);
        return $post ? $post->toEntity() : false;
    }

    public function entityTypeGetEntitiesByIds(array $entityIds)
    {
        $entities = array();
        foreach ($this->_addon->getModel('Post')
            ->fetchByIds($entityIds)
            ->with('User')
        as $post) {
            $entities[] = $post->toEntity();
        }
        return $entities;
    }

    public function entityTypeCreateEntity(Sabai_Addon_Entity_Model_Bundle $bundle, array $properties, SabaiFramework_User_Identity $identity)
    {
        $post = $this->_addon->getModel()->create('Post')->markNew();
        $post->entity_bundle_name = $bundle->name;
        $post->entity_bundle_type = $bundle->type;
        if (!empty($properties['content_post_published'])) {
            $post->published = $properties['content_post_published'];
        } else {
            $post->published = time();
        }
        if (!empty($properties['content_post_user_id'])) {
            $post->user_id = $properties['content_post_user_id'];
        } else {
            $post->User = $identity;
        }
        $post->views = isset($properties['content_post_views']) ? $properties['content_post_views'] : 0;
        if (isset($properties['content_post_title'])) {
            $post->title = $properties['content_post_title'];
            // Cerate slug only if bundle has permalink enabled
            if (isset($bundle->info['permalink_path'])) {
                $post->slug = $this->_getUniqueSlug($bundle, $this->_addon->getApplication()->Slugify($properties['content_post_title']));
            }
        }
        $post->status = isset($properties['content_post_status']) ? $properties['content_post_status'] : Sabai_Addon_Content::POST_STATUS_PUBLISHED;
        if (!empty($properties['content_post_id'])) {
            $post->id = $properties['content_post_id'];
        }
        $this->_addon->getModel()->commit();
        return $post->toEntity();
    }
    
    public function entityTypeUpdateEntity(Sabai_Addon_Entity_IEntity $entity, Sabai_Addon_Entity_Model_Bundle $bundle, array $properties)
    {
        if (!$post = $this->_addon->getModel('Post')->fetchById($entity->getId())) {
            throw new Sabai_RuntimeException(sprintf('Cannot save non existent entity (Bundle: %s, ID: %d).', $bundle->name, $entity->getId()));
        }
        $post->entity_bundle_name = $bundle->name;
        foreach ($properties as $property => $value) {
            switch ($property) {
                case 'content_post_title':
                    $post->title = $value;
                    break;
                case 'content_post_slug':
                    // Update slug only if bundle has permalink enabled
                    if (isset($bundle->info['permalink_path'])) {
                        $post->slug = $this->_getUniqueSlug($bundle, $this->_addon->getApplication()->Slugify($value), $post->id);
                    }
                    break;
                case 'content_post_status':
                    $post->status = $value;
                    break;
                case 'content_post_user_id':
                    $post->user_id = $value;
                    break;
                case 'content_post_published':
                    $post->published = $value;
                    break;
            }
        }
        $this->_addon->getModel()->commit();
        return $post->toEntity();
    }

    public function entityTypeDeleteEntities(array $entities)
    {
        $entity_ids = array();
        foreach ($entities as $entity) {
            $entity_ids[] = $entity->getId();
        }
        foreach ($this->_addon->getModel('Post')->fetchByIds($entity_ids) as $post) {
            $post->markRemoved();
        }
        $this->_addon->getModel()->commit();
    }

    public function entityTypeSearchEntitiesByBundle($keyword, $bundle, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        $entities = array();
        foreach ($this->_addon->getModel('Post')
            ->entityBundleName_is($bundle->name)
            ->status_is(Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->title_contains($keyword)
            ->fetch($limit, $offset, $sort, $order)
            ->with('User')
        as $post) {
            $entities[] = $post->toEntity();
        }
        return $entities;
    }
    
    private function _getUniqueSlug($bundle, $slug, $postId = null)
    {
        $gateway = $this->_addon->getModel()->getGateway('Post');
        if (!$gateway->slugExists($bundle, $slug, $postId)) {
            return $slug;
        }
        
        $count = 0;
        do {
            ++$count;
            $slug_with_number = $slug . '-' . $count;
        } while ($gateway->slugExists($bundle, $slug_with_number, $postId));
        
        return $slug_with_number;
    }
}