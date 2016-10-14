<?php
abstract class Sabai_Addon_Entity_Model_WithEntity extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_entityType, $_entities, $_entityIdVar, $_entityObjectVarName;

    public function __construct(SabaiFramework_Model_EntityCollection $collection, $entityType = 'content', $entityIdVar = 'entity_id', $entityObjectVarName = 'Entity')
    {
        parent::__construct($collection);
        $this->_entityType = $entityType;
        $this->_entityIdVar = $entityIdVar;
        $this->_entityObjectVarName = $entityObjectVarName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_entities)) {
            $this->_entities = array();
            if ($this->_collection->count() > 0) {
                $entity_ids = array();
                while ($this->_collection->valid()) {
                    if ($entity_id = $this->_collection->current()->{$this->_entityIdVar}) {
                        $entity_ids[] = $entity_id;
                    }
                    $this->_collection->next();
                }
                if (!empty($entity_ids)) {
                    foreach ($this->_model->Entity_Entities($this->_entityType, array_unique($entity_ids), false) as $entity) {
                        $this->_entities[$entity->getId()] = $entity;
                    }
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        if (($entity_id = $current->{$this->_entityIdVar})
            && isset($this->_entities[$entity_id])
        ) {
            $current->assignObject($this->_entityObjectVarName, $this->_entities[$entity_id]);
        } else {
            $current->assignObject($this->_entityObjectVarName);
        }

        return $current;
    }
}