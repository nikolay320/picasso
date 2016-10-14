<?php
class Sabai_Addon_Entity_Query
{
    protected $_addon, $_entityType, $_fieldQuery;
    
    public function __construct(Sabai_Addon_Entity $addon, $entityType, $operator)
    {
        $this->_addon = $addon;
        $this->_entityType = $entityType;
        $this->_fieldQuery = new Sabai_Addon_Entity_FieldQuery($operator);
    }
    
    public function getFieldQuery()
    {
        return $this->_fieldQuery;
    }
    
    public function __call($name, $args)
    {
        call_user_func_array(array($this->_fieldQuery, $name), $args);
        
        return $this;
    }
    
    public function fetch($limit = 0, $offset = 0, $loadEntityFields = true)
    {
        return $this->_addon->fetchEntities($this->_entityType, $this->_fieldQuery, $limit, $offset, $loadEntityFields);
    }
    
    public function delete(array $extraEventArgs = array())
    {
        $entities = $this->fetch(0, 0, false);
        if (!empty($entities)) {
            $this->_addon->deleteEntities($this->_entityType, $entities, $extraEventArgs);
        }
    }
    
    public function count($limit = 0, $offset = 0)
    {
        return $this->_addon->countEntities($this->_entityType, $this->_fieldQuery, $limit, $offset);
    }

    public function paginate($limit = 20, $loadEntityFields = true)
    {
        return $this->_addon->paginateEntities($this->_entityType, $this->_fieldQuery, $limit, $loadEntityFields);
    }
    
    public function deleteCache()
    {
        $entities = $this->fetch(0, 0, false);
        if (!empty($entities)) {
            $this->_addon->entityFieldCacheRemove($this->_entityType, array_keys($entities));
        }
    }
}