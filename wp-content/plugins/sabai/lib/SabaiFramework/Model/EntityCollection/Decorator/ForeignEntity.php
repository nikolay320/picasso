<?php
class SabaiFramework_Model_EntityCollection_Decorator_ForeignEntity extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_foreignKeyVar;
    protected $_foreignEntityName;
    protected $_foreignEntities;
    protected $_foreitnEntityObjectVarName;

    public function __construct($foreignKeyVar, $foreignEntityName, SabaiFramework_Model_EntityCollection $collection, $foreignEntityObjectVarName = null)
    {
        parent::__construct($collection);
        $this->_foreignKeyVar = $foreignKeyVar;
        $this->_foreignEntityName = $foreignEntityName;
        $this->_foreitnEntityObjectVarName = isset($foreignEntityObjectVarName) ? $foreignEntityObjectVarName : $foreignEntityName;
    }

    public function rewind()
    {
        if (!isset($this->_foreignEntities)) {
            $this->_foreignEntities = array();
            if ($this->_collection->count() > 0) {
                // Fetch all foreign entity IDs and call array_filter to filter out empty values
                $foreign_ids = array_filter($this->_collection->getArray($this->_foreignKeyVar, $this->_foreignKeyVar));
                if (!empty($foreign_ids)) {
                    $this->_foreignEntities = $this->_model->getRepository($this->_foreignEntityName)
                        ->fetchByIds($foreign_ids)
                        ->getArray();
                }
            }
        }
        $this->_collection->rewind();
    }

    public function current()
    {
        $current = $this->_collection->current();
        $foreign_id = $current->{$this->_foreignKeyVar};
        if (isset($this->_foreignEntities[$foreign_id])) {
            $current->assignObject($this->_foreitnEntityObjectVarName, $this->_foreignEntities[$foreign_id]);
        } else {
            $current->assignObject($this->_foreitnEntityObjectVarName);
        }

        return $current;
    }
}