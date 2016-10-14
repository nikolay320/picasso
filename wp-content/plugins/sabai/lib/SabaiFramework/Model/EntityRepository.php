<?php
abstract class SabaiFramework_Model_EntityRepository
{
    /**
     * @var string
     */
    protected $_name;
    /**
     * @var string
     */
    private $_fieldPrefix;
    /**
     * @var SabaiFramework_Model
     */
    protected $_model;
    /**
     * @var array
     */
    private $_criteria;

    /**
     * Constructor
     */
    protected function __construct($name, SabaiFramework_Model $model)
    {
        $this->_name = $name;
        $this->_model = $model;
        $this->_fieldPrefix = strtolower($name) . '_';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    public function create()
    {
        return $this->_model->create($this->_name);
    }

    /**
     * Creates a criteria element and returns self for chainability
     *
     * @return SabaiFramework_Model_EntityRepository
     */
    public function criteria()
    {
        $this->_criteria = $this->_model->createCriteria($this->_name);
        return $this;
    }

    /**
     * Calls a method defined in the criteria element and returns self for chainability
     *
     * @return SabaiFramework_Model_EntityRepository
     */
    public function __call($method, $args)
    {
        if (!isset($this->_criteria)) $this->_criteria = $this->_model->createCriteria($this->_name);
        $this->_criteria = call_user_func_array(array($this->_criteria, $method), $args);

        return $this;
    }

    /**
     * @param int $id
     * @param bool $returnCollection
     * @return SabaiFramework_Model_Entity
     */
    public function fetchById($id, $returnCollection = false, $useCache = true)
    {
        if ($useCache
            && ($entity = $this->_model->isEntityCached($this->_name, $id))
        ) {
            return $returnCollection ? $this->createCollection(array($entity)) : $entity;
        }

        $collection = $this->_getCollection($this->_model->getGateway($this->_name)->selectById($id));
        if (!$returnCollection) {
            return $collection->getFirst();
        }
        $collection->rewind();
        return $collection;
    }

    /**
     * @param array $ids
     * @return SabaiFramework_Model_EntityCollection
     */
    public function fetchByIds($ids)
    {
        foreach ($ids as $id) {
            
        }
        return $this->_getCollection($this->_model->getGateway($this->_name)->selectByIds($ids));
    }

    /**
     * @param SabaiFramework_Criteria
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return SabaiFramework_Model_EntityCollection_Rowset
     */
    public function fetchByCriteria(SabaiFramework_Criteria $criteria, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        return $this->_getCollection(
            $this->_model->getGateway($this->getName())
                ->selectByCriteria($criteria, array(), $limit, $offset, array_map(array($this, '_prefixSort'), (array)$sort), (array)$order)
        );
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return SabaiFramework_Model_EntityCollection_Rowset
     */
    public function fetch($limit = 0, $offset = 0, $sort = null, $order = null)
    {
        $criteria = !isset($this->_criteria) ? new SabaiFramework_Criteria_Empty() : $this->_criteria;
        unset($this->_criteria);
        return $this->fetchByCriteria($criteria, $limit, $offset, $sort, $order);
    }

    /**
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @param int $offset
     * @return SabaiFramework_Model_EntityCollection_Rowset or false
     */
    public function fetchOne($sort = null, $order = null, $offset = 0)
    {
        return $this->fetch(1, $offset, $sort, $order)->getNext();
    }

    /**
     * @param SabaiFramework_Criteria
     * @return mixed Integer if no grouping, array otherwise
     */
    public function countByCriteria(SabaiFramework_Criteria $criteria)
    {
        return $this->_model->getGateway($this->getName())->countByCriteria($criteria);
    }

    /**
     * @return int
     */
    public function count()
    {
        $criteria = !isset($this->_criteria) ? new SabaiFramework_Criteria_Empty() : $this->_criteria;
        unset($this->_criteria);
        return $this->countByCriteria($criteria);
    }
    
    public function delete()
    {
        $criteria = !isset($this->_criteria) ? new SabaiFramework_Criteria_Empty() : $this->_criteria;
        unset($this->_criteria);
        return $this->_model->getGateway($this->getName())->deleteByCriteria($criteria);
    }

    /**
     * @param SabaiFramework_Criteria $criteria
     * @param int $perpage
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return SabaiFramework_Model_Paginator_Criteria
     */
    public function paginateByCriteria(SabaiFramework_Criteria $criteria, $perpage = 10, $sort = null, $order = null)
    {
        return new SabaiFramework_Model_Paginator_Criteria($this, $criteria, $perpage, $sort, $order);
    }

    /**
     * @param SabaiFramework_Criteria $criteria
     * @param int $perpage
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return SabaiFramework_Model_Paginator_Criteria
     */
    public function paginate($perpage = 10, $sort = null, $order = null)
    {
        $criteria = !isset($this->_criteria) ? new SabaiFramework_Criteria_Empty() : $this->_criteria;
        unset($this->_criteria);
        return $this->paginateByCriteria($criteria, $perpage, $sort, $order);
    }

    /**
     * Helper method for fetching entitie pages by foreign key relationship
     *
     * @param string $entityName
     * @param string $id
     * @param int $perpage
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return SabaiFramework_Model_Paginator_Entity
     */
    protected function _paginateByEntity($entityName, $id, $perpage = 10, $sort = null, $order = null)
    {
        if ($criteria = @$this->_criteria) {
            unset($this->_criteria);
            return $this->_paginateByEntityAndCriteria($entityName, $id, $criteria, $perpage, $sort, $order);
        }

        return new SabaiFramework_Model_Paginator_Entity($this, $entityName, $id, $perpage, $sort, $order);
    }

    /**
     * Helper method for fetching entitie pages by entitiy id and criteria
     *
     * @param string $entityName
     * @param string $id
     * @param SabaiFramework_Criteria
     * @param int $perpage
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return SabaiFramework_Model_Paginator_Entity
     */
    protected function _paginateByEntityAndCriteria($entityName, $id, SabaiFramework_Criteria $criteria, $perpage = 10, $sort = null, $order = null)
    {
        return new SabaiFramework_Model_Paginator_EntityCriteria($this, $entityName, $id, $criteria, $perpage, $sort, $order);
    }

    /**
     * Helper method for fetching entities by foreign key relationship
     *
     * @param string $foreignKey
     * @param string $id
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return SabaiFramework_Model_Paginator_Entity
     */
    protected function _fetchByForeign($foreignKey, $id, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        if ($criteria = @$this->_criteria) {
            unset($this->_criteria);
            return $this->_fetchByForeignAndCriteria($foreignKey, $id, $criteria, $limit, $offset, $sort, $order);
        }

        $criteria = is_array($id) ? new SabaiFramework_Criteria_In($foreignKey, $id) : new SabaiFramework_Criteria_Is($foreignKey, $id);
        return $this->fetchByCriteria($criteria, $limit, $offset, $sort, $order);
    }

    /**
     * Helper method for counting entities by foreign key relationship
     *
     * @param string $foreignKey
     * @param string $id
     * @return int
     */
    protected function _countByForeign($foreignKey, $id)
    {
        if ($criteria = @$this->_criteria) {
            unset($this->_criteria);
            return $this->_countByForeignAndCriteria($foreignKey, $id, $criteria);
        }

        $criteria = is_array($id) ? new SabaiFramework_Criteria_In($foreignKey, $id) : new SabaiFramework_Criteria_Is($foreignKey, $id);
        return $this->countByCriteria($criteria);
    }

    /**
     * Helper method for fetching entities by foreign key relationship
     *
     * @param string $foreignKey
     * @param string $id
     * @param SabaiFramework_Criteria $criteria
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return SabaiFramework_Model_Paginator_Entity
     */
    protected function _fetchByForeignAndCriteria($foreignKey, $id, SabaiFramework_Criteria $criteria, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        $criterion = new SabaiFramework_Criteria_Composite(array($criteria));
        if (is_array($id)) {
            $criterion->addAnd(new SabaiFramework_Criteria_In($foreignKey, $id));
        } else {
            $criterion->addAnd(new SabaiFramework_Criteria_Is($foreignKey, $id));
        }

        return $this->fetchByCriteria($criterion, $limit, $offset, $sort, $order);
    }

    /**
     * Helper method for counting entities by foreign key relationship
     *
     * @param string $foreignKey
     * @param string $id
     * @param SabaiFramework_Criteria
     * @return int
     */
    protected function _countByForeignAndCriteria($foreignKey, $id, SabaiFramework_Criteria $criteria)
    {
        $criterion = new SabaiFramework_Criteria_Composite(array($criteria));
        if (is_array($id)) {
            $criterion->addAnd(new SabaiFramework_Criteria_In($foreignKey, $id));
        } else {
            $criterion->addAnd(new SabaiFramework_Criteria_Is($foreignKey, $id));
        }

        return $this->countByCriteria($criterion);
    }

    /**
     * Helper method for fetching entities by association table relationship
     *
     * @param string $selfTable
     * @param string $assocEntity
     * @param string $assocTargetKey
     * @param string $id
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return SabaiFramework_Model_EntityCollection_Rowset
     */
    protected function _fetchByAssoc($selfTable, $assocEntity, $assocTargetKey, $id, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        if ($criteria = @$this->_criteria) {
            unset($this->_criteria);
            return $this->_fetchByAssocAndCriteria($selfTable, $assocEntity, $assocTargetKey, $id, $criteria, $limit, $offset, $sort, $order);
        }

        $criteria = is_array($id) ? new SabaiFramework_Criteria_In($assocTargetKey, $id) : new SabaiFramework_Criteria_Is($assocTargetKey, $id);
        $fields = array('DISTINCT ' . $selfTable . '.*');

        return $this->_getCollection(
            $this->_model->getGateway($assocEntity)
                ->selectByCriteria($criteria, $fields, $limit, $offset, array_map(array($this, '_prefixSort'), (array)$sort), $order)
        );
    }

    /**
     * Helper method for counting entities by association table relationship
     *
     * @param string $selfTableId
     * @param string $assocEntity
     * @param string $assocTargetKey
     * @param string $id
     * @return int
     */
    protected function _countByAssoc($selfTableId, $assocEntity, $assocTargetKey, $id)
    {
        if ($criteria = @$this->_criteria) {
            unset($this->_criteria);
            return $this->_countByAssocAndCriteria($selfTableId, $assocEntity, $assocTargetKey, $id, $criteria);
        }

        $criteria = is_array($id) ? new SabaiFramework_Criteria_In($assocTargetKey, $id) : new SabaiFramework_Criteria_Is($assocTargetKey, $id);

        return $this->_model->getGateway($assocEntity)->selectByCriteria($criteria, array('COUNT(DISTINCT '. $selfTableId .')'))->fetchSingle();
    }

    /**
     * Helper method for fetching entities by association table relationship
     * and additional criteria
     *
     * @param string $selfTable
     * @param string $assocEntity
     * @param string $assocTargetKey
     * @param string $id
     * @param SabaiFramework_Criteria $criteria
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return SabaiFramework_Model_EntityCollection_Rowset
     */
    protected function _fetchByAssocAndCriteria($selfTable, $assocEntity, $assocTargetKey, $id, SabaiFramework_Criteria $criteria, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        $criterion = new SabaiFramework_Criteria_Composite(array($criteria));
        if (is_array($id)) {
            $criterion->addAnd(new SabaiFramework_Criteria_In($assocTargetKey, $id));
        } else {
            $criterion->addAnd(new SabaiFramework_Criteria_Is($assocTargetKey, $id));
        }
        $fields = array('DISTINCT ' . $selfTable . '.*');

        return $this->_getCollection(
            $this->_model->getGateway($assocEntity)
                ->selectByCriteria($criterion, $fields, $limit, $offset, array_map(array($this, '_prefixSort'), (array)$sort), (array)$order)
        );
    }

    /**
     * Helper method for counting entities by association table relationship
     * and additional criteria
     *
     * @param string $selfTableId
     * @param string $assocEntity
     * @param string $id
     * @param SabaiFramework_Criteria $criteria
     * @return SabaiFramework_Model_EntityCollection_Rowset
     */
    protected function _countByAssocAndCriteria($selfTableId, $assocEntity, $assocTargetKey, $id, SabaiFramework_Criteria $criteria)
    {
        $criterion = new SabaiFramework_Criteria_Composite(array($criteria));
        if (is_array($id)) {
            $criterion->addAnd(new SabaiFramework_Criteria_In($assocTargetKey, $id));
        } else {
            $criterion->addAnd(new SabaiFramework_Criteria_Is($assocTargetKey, $id));
        }

        return $this->_model->getGateway($assocEntity)->selectByCriteria($criterion, array('COUNT(DISTINCT '. $selfTableId .')'))->fetchSingle();
    }

    /**
     * Prefix the requested sort value get the actual field name
     *
     * @param string $sort
     * @return array
     */
    private function _prefixSort($sort)
    {
        return $this->_fieldPrefix . $sort;
    }

    /**
     * Turns a rowset object into an entity collection object
     *
     * @param mixed SabaiFramework_DB_Rowset
     * @return SabaiFramework_Model_EntityCollection
     */
    protected function _getCollection(SabaiFramework_DB_Rowset $rs)
    {
        return $this->_getCollectionByRowset($rs);
    }

    /**
     * @param SabaiFramework_DB_Rowset $rs
     * @return SabaiFramework_Model_EntityCollection
     */
    abstract protected function _getCollectionByRowset(SabaiFramework_DB_Rowset $rs);
    /**
     * @param array $entities
     * @return SabaiFramework_Model_EntityCollection
     */
    abstract public function createCollection(array $entities = array());
}