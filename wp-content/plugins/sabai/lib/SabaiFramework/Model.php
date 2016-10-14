<?php
class SabaiFramework_Model
{
    const ERROR_NONE = 0, ERROR_COMMIT_NEW = 1, ERROR_COMMIT_DIRTY = 2, ERROR_COMMIT_REMOVED = 3;

    const KEY_TYPE_INT = 1, KEY_TYPE_INT_NULL = 2, KEY_TYPE_CHAR = 5, KEY_TYPE_VARCHAR = 7,
        KEY_TYPE_TEXT = 10, KEY_TYPE_FLOAT = 15, KEY_TYPE_DECIMAL = 16, KEY_TYPE_BOOL = 20, KEY_TYPE_BLOB = 25;

    /**
     * @var SabaiFramework_DB
     */
    protected $_db;
    /**
     * @var array
     */
    private $_repositories = array();
    /**
     * @var array
     */
    private $_gateways = array();
    /**
     * @var array
     */
    private $_entities = array();
    /**
     * @var array
     */
    private $_queries = array();
    /**
     * @var string
     */
    protected $_modelPrefix;
    /**
     * Path to directory where compiled/custom model files are located
     * @var string
     */
    protected $_modelDir;
    /**
     * Cached entities
     * @var array
     */
    private $_cache = array();
    /**
     * @var array
     */
    private $_externalModels = array();
    /**
     * *var array
     */
    private $_externalModelEntities = array();

    /**
     * Constructor
     *
     * @param SabaiFramework_DB $db
     * @param string $modelDir
     * @param string $modelPrefix
     * @return SabaiFramework_Model
     */
    public function __construct(SabaiFramework_DB $db, $modelDir, $modelPrefix = '')
    {
        $this->_db = $db;
        $this->_modelDir = $modelDir;
        $this->_modelPrefix = $modelPrefix;
    }

    /**
     * @return SabaiFramework_DB
     */
    public function getDB()
    {
        return $this->_db;
    }

    /**
     * @return string
     */
    public function getModelPrefix()
    {
        return $this->_modelPrefix;
    }

    /**
     * @return string
     */
    public function getModelDir()
    {
        return $this->_modelDir;
    }

    /**
     * @return mixed SabaiFramework_Model_Entity or false
     */
    public function isEntityCached($name, $id)
    {
        return isset($this->_cache[$name][$id]) ? $this->_cache[$name][$id] : false;
    }

    public function cacheEntity(SabaiFramework_Model_Entity $entity)
    {
        $this->_cache[$entity->getName()][$entity->id] = $entity;
    }

    public function clearEntityCache($name, $id = null)
    {
        if (isset($id)) {
            unset($this->_cache[$name][$id]);
        } else {
            unset($this->_cache[$name]);
        }
    }

    /**
     * PHP magic method
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getRepository($name);
    }

    /**
     * Gets an instance of SabaiFramework_Model_EntityRepository
     *
     * @param string $name
     * @return SabaiFramework_Model_EntityRepository
     */
    public function getRepository($name)
    {
        $name_lc = strtolower($name);
        if (!isset($this->_repositories[$name_lc])) {
            $this->_repositories[$name_lc] = $this->_getRepository($name);
        }

        return $this->_repositories[$name_lc];
    }

    /**
     * Gets an instance of SabaiFramework_Model_EntityRepository
     *
     * @param string $name
     * @return SabaiFramework_Model_EntityRepository
     */
    protected function _getRepository($name)
    {
        $class = $this->_modelPrefix . $name . 'Repository';
        if (!class_exists($class, false)) {
            $file = $name . '.php';
            require $this->_modelDir . '/Base/' . $file;
            require $this->_modelDir . '/' . $file;
        }

        return new $class($this);
    }

    /**
     * Gets an instance of SabaiFramework_Model_Gateway
     *
     * @param string $name
     * @return SabaiFramework_Model_Gateway
     */
    public function getGateway($name)
    {
        $name_lc = strtolower($name);
        if (!isset($this->_gateways[$name_lc])) {
            $this->_loadGateway($name, $name_lc);
        }

        return $this->_gateways[$name_lc];
    }

    /**
     * Loads an instance of SabaiFramework_Model_Gateway
     *
     * @param string $name
     * @param string $as
     * @return SabaiFramework_Model_Gateway
     */
    protected function _loadGateway($name, $as)
    {
        $class = $this->_modelPrefix . $name . 'Gateway';
        if (!class_exists($class, false)) {
            $file = $name . 'Gateway.php';
            require $this->_modelDir . '/Base/' . $file;
            require $this->_modelDir . '/' . $file;
        }
        $this->_gateways[$as] = new $class();
        $this->_gateways[$as]->setDB($this->_db);
    }

    /**
     * Creates a collection of entity objects
     *
     * @param string $name
     * @param array $entities
     * @return SabaiFramework_Model_EntityCollection
     */
    public function createCollection($name, array $entities = array())
    {
        return $this->getRepository($name)->createCollection($entities);
    }

    /**
     * Decorates a collection of entity objects
     *
     * @param SabaiFramework_Model_EntityCollection $collection
     * @param string $with
     * @return SabaiFramework_Model_EntityCollection
     */
    public function decorate($collection, $with)
    {
        $with = (array)$with;
        $_with = array_shift($with);
        $class = $this->_modelPrefix . $collection->getName() . 'With' . $_with;
        if (!class_exists($class, false)) {
            require $this->_modelDir . '/' . $collection->getName() . 'With' . $_with . '.php';
        }
        $ret = new $class($collection);

        $arr = $ret->getArray();

        while ($__with = array_shift($with)) {
            $collection_next = array();
            foreach ($arr as $entity) {
                if ($obj = $entity->fetchObject($_with)) {
                    if ($obj instanceof SabaiFramework_Model_EntityCollection) {
                        foreach ($obj as $_obj) {
                            $collection_next[] = $_obj;
                        }
                    } else {
                        $collection_next[] = $obj;
                    }
                }
            }
            if (empty($collection_next)) break;

            // need to retrieve entity name like this since not all decoration names are an entity name (e.g. LastXxxxx)
            $entity_name = $collection_next[0]->getName();
            // Fetch model object of target entities
            $model = $collection_next[0]->getModel();
            // Decorate the target entities
            $_collection = $model->createCollection($entity_name, $collection_next);

            $__with = (array)$__with;
            foreach ($__with as $___with) {
                $_decorated = $model->decorate($_collection, $___with);
            }

            $_with = $__with[0];
            $arr = $collection_next;
        }

        return $ret;
    }

    /**
     * Gets an instance of SabaiFramework_Model_EntityCriteria
     *
     * @param string $name
     * @return SabaiFramework_Model_EntityCriteria
     */
    public function createCriteria($name)
    {
        if (!class_exists('SabaiFramework_Model_EntityCriteria', false)) {
            require 'SabaiFramework/Model/EntityCriteria.php';
        }
        $class = $this->_modelPrefix . 'Base_' . $name . 'Criteria';
        if (!class_exists($class, false)) {
            require $this->_modelDir . '/Base/' . $name . 'Criteria.php';
        }

        return new $class($name);
    }

    /**
     * Creates a new entity
     *
     * @param string $entityName
     * @return SabaiFramework_Model_Entity
     */
    public function create($entityName)
    {
        $this->getRepository($entityName);
        $class = $this->_modelPrefix . $entityName;

        return new $class($this);
    }

    /**
     * Registers a new instance of SabaiFramework_Model_Entity
     *
     * @param SabaiFramework_Model_Entity $entity
     */
    public function registerNew(SabaiFramework_Model_Entity $entity)
    {
        if (!$entity->id && !$entity->getTempId()) {
            $name = $entity->getName();
            if (!isset($this->_entities['new'][$name])) {
                $this->_entities['new'][$name] = array();
                $temp_id = 1;
            } else {
                $temp_id = count($this->_entities['new'][$name]) + 1;
            }
            $entity->setTempId($temp_id);
            $this->_entities['new'][$name][$temp_id] = $entity;
        }

        return $this;
    }

    /**
     * Registers a modified(drity) instance of SabaiFramework_Model_Entity
     *
     * @param SabaiFramework_Model_Entity $entity
     */
    public function registerDirty(SabaiFramework_Model_Entity $entity)
    {
        if ($id = $entity->id) {
            $name = $entity->getName();
            if (!isset($this->_entities['removed'][$name][$id])) {
                 $this->_entities['dirty'][$name][$id] = $entity;
            }
        }

        return $this;
    }

    /**
     * Registers a deleted instance of SabaiFramework_Model_Entity
     *
     * @param SabaiFramework_Model_Entity $entity
     */
    public function registerRemoved(SabaiFramework_Model_Entity $entity)
    {
        $name = $entity->getName();
        if ($temp_id = $entity->getTempId()) {
            // registered as new, so just remove it from there
            unset($this->_entities['new'][$name][$temp_id]);
            return $this;
        }
        if ($id = $entity->id) {
            if (isset($this->_entities['dirty'][$name][$id])) {
                unset($this->_entities['dirty'][$name][$id]);
            }
            $this->_entities['removed'][$name][$id] = $entity;
        }

        return $this;
    }

    /**
     * Registers an SQL query
     * @param string $sql
     */
    public function registerQuery($sql)
    {
        $this->_queries[] = $sql;

        return $this;
    }

    /**
     * Registers a bulk UPDATE query
     * @param SabaiFramework_Model_EntityCriteria $criteria
     * @param array $values
     */
    public function registerUpdateQuery(SabaiFramework_Model_EntityCriteria $criteria, array $values)
    {
        $this->_queries[] = array($criteria->getName(), $criteria, $values);

        return $this;
    }

    /**
     * Registers a bulk DELETE query
     * @param SabaiFramework_Model_EntityCriteria $criteria
     */
    public function registerDeleteQuery(SabaiFramework_Model_EntityCriteria $criteria)
    {
        $this->_queries[] = array($criteria->getName(), $criteria);

        return $this;
    }

    /**
     * Registers another model for commit within the transaction of this model
     * @param SabaiFramework_Model $model
     */
    public function registerExternalModel(SabaiFramework_Model $model)
    {
        $this->_externalModels[$model->getModelDir()] = $model;

        return $this;
    }

    /**
     * Registers another model's entity for commit within the transaction of this model
     * @param SabaiFramework_Model_Entity $entity
     */
    public function registerExternalModelEntity(SabaiFramework_Model_Entity $entity)
    {
        $this->_externalModelEntities[] = $entity;

        return $this;
    }

    /**
     * Commits pending SabaiFramework_Model_Entity instances to the datasource
     *
     * @throws SabaiFramework_DB_Exception
     * @return int Number of entities/rows affected
     */
    public function commit()
    {
        try {
            $this->_db->begin();
            // new entities should be commited first to properly create foreign key mappings
            $count = $this->_commitNew()
                + $this->_commitRemoved()
                + $this->_commitDirty()
                + $this->_commitQueries()
                + $this->_commitExternalModels()
                + $this->_commitExternalModelEntities();
            $this->_db->commit();
        } catch (SabaiFramework_Exception $e) {
            $this->_db->rollback();
            throw $e;
        }

        $this->_entities = $this->_queries = $this->_cache = $this->_externalModels = $this->_externalModelEntities = array();

        return $count;
    }

    /**
     * Commits changes without using transactions, useful if comitting from within other model's transaction.
     *
     * @throws SabaiFramework_DB_Exception
     * @return int Number of entities/rows affected
     */
    public function commitWithoutTransaction()
    {
        // new entities should be commited first to properly create foreign key mappings
        $count = $this->_commitNew()
            + $this->_commitRemoved()
            + $this->_commitDirty()
            + $this->_commitQueries()
            + $this->_commitExternalModels()
            + $this->_commitExternalModelEntities();

        $this->_entities = $this->_queries = $this->_cache = $this->_externalModels = $this->_externalModelEntities = array();

        return $count;
    }

    /**
     * Commits a pending SabaiFramework_Model_Entity instance to the datasource
     *
     * @param SabaiFramework_Model_Entity $entity
     * @throws SabaiFramework_DB_Exception
     */
    public function commitOne(SabaiFramework_Model_Entity $entity)
    {
        $name = $entity->getName();
        try {
            if ($temp_id = $entity->getTempId()) {
                if (isset($this->_entities['new'][$name][$temp_id])) {
                    unset($this->_entities['new'][$name][$temp_id]);
                    $this->_db->begin();
                    $this->_commitOneNew($this->getGateway($name), $entity);
                    $this->_db->commit();
                }
            } elseif ($id = $entity->id) {
                if (isset($this->_entities['dirty'][$name][$id])) {
                    unset($this->_entities['dirty'][$name][$id]);
                    $this->_db->begin();
                    $this->_commitOneDirty($this->getGateway($name), $entity);
                    $this->_db->commit();
                } elseif (isset($this->_entities['removed'][$name][$id])) {
                    unset($this->_entities['removed'][$name][$id]);
                    $this->_db->begin();
                    $this->_commitOneRemoved($this->getGateway($name), $entity);
                    $this->_db->commit();
                }
            }
        } catch (SabaiFramework_Exception $e) {
            $this->_db->rollback();
            throw $e;
        }
    }

    /**
     * Commits a pending SabaiFramework_Model_Entity instance without transaction.
     *
     * @param SabaiFramework_Model_Entity $entity
     * @throws SabaiFramework_DB_Exception
     */
    public function commitOneWithoutTransaction(SabaiFramework_Model_Entity $entity)
    {
        $name = $entity->getName();
        if ($temp_id = $entity->getTempId()) {
            if (isset($this->_entities['new'][$name][$temp_id])) {
                unset($this->_entities['new'][$name][$temp_id]);
                $this->_commitOneNew($this->getGateway($name), $entity);
            }
        } elseif ($id = $entity->id) {
            if (isset($this->_entities['dirty'][$name][$id])) {
                unset($this->_entities['dirty'][$name][$id]);
                $this->_commitOneDirty($this->getGateway($name), $entity);
            } elseif (isset($this->_entities['removed'][$name][$id])) {
                unset($this->_entities['removed'][$name][$id]);
                $this->_commitOneRemoved($this->getGateway($name), $entity);
            }
        }
    }

    /**
     * Commits new entities to the datasource
     *
     * @return integer if success, false otherwise
     */
    protected function _commitNew()
    {
        if (empty($this->_entities['new'])) return 0;

        $count = 0;
        foreach (array_keys($this->_entities['new']) as $name) {
            $gateway = $this->getGateway($name);
            foreach (array_keys($this->_entities['new'][$name]) as $id) {
                $entity = $this->_entities['new'][$name][$id];

                // Make sure that the foreign entities are already commited
                $this->_commitEntitiesToBeAssigned($entity);

                $this->_commitOneNew($gateway, $entity);
                unset($entity, $this->_entities['new'][$name][$id]);

                ++$count;
            }
        }

        return $count;
    }

    /**
     * Commits modified entities to the datasource
     *
     * @return integer if success, false otherwise
     */
    protected function _commitDirty()
    {
        if (empty($this->_entities['dirty'])) return 0;

        $count = 0;
        foreach (array_keys($this->_entities['dirty']) as $name) {
            $gateway = $this->getGateway($name);
            foreach (array_keys($this->_entities['dirty'][$name]) as $id) {
                $entity = $this->_entities['dirty'][$name][$id];

                // Make sure that the foreign entities are already commited
                $this->_commitEntitiesToBeAssigned($entity);

                $this->_commitOneDirty($gateway, $entity);
                unset($entity, $this->_entities['dirty'][$name][$id]);

                ++$count;
            }
        }

        return $count;
    }

    /**
     * Commits deleted entities to the datasource
     *
     * @return mixed integer if success, false otherwise
     */
    protected function _commitRemoved()
    {
        if (empty($this->_entities['removed'])) return 0;

        $count = 0;
        foreach (array_keys($this->_entities['removed']) as $name) {
            foreach (array_keys($this->_entities['removed'][$name]) as $id) {
                $this->_commitOneRemoved($this->getGateway($name), $this->_entities['removed'][$name][$id]);
                unset($this->_entities['removed'][$name][$id]);
                ++$count;
            }
        }

        return $count;
    }

    protected function _commitQueries()
    {
        $count = 0;
        foreach ($this->_queries as $query) {
            if (is_array($query)) {
                if (isset($query[2])) {
                    $count += $this->getGateway($query[0])->updateByCriteria($query[1], $query[2]);
                } else {
                    $count += $this->getGateway($query[0])->deleteByCriteria($query[1]);
                }
            } else {
                $count += $this->_db->exec($query);
            }
        }

        return $count;
    }

    protected function _commitExternalModels()
    {
        $count = 0;
        foreach ($this->_externalModels as $model) {
            $count += $model->commitWithoutTransaction();
        }

        return $count;
    }

    protected function _commitExternalModelEntities()
    {
        $count = 0;
        foreach ($this->_externalModelEntities as $entity) {
            $entity->getModel()->commitOneWithoutTransaction($entity);
            ++$count;
        }

        return $count;
    }

    protected function _commitOneNew(SabaiFramework_Model_Gateway $gateway, SabaiFramework_Model_Entity $entity)
    {
        $entity->onCommit();
        $insert_id = $gateway->insert($entity->getVars());
        $entity->set('id', $insert_id)->setTempId(false);
        unset($this->_entities['dirty'][$entity->getName()][$insert_id]); // do not mark as dirty
        $this->_commitNewEntityAssign($entity);
    }

    protected function _commitOneDirty(SabaiFramework_Model_Gateway $gateway, SabaiFramework_Model_Entity $entity)
    {
        $entity->onCommit();
        $gateway->updateById($entity->id, $entity->getVars());
    }

    protected function _commitOneRemoved(SabaiFramework_Model_Gateway $gateway, SabaiFramework_Model_Entity $entity)
    {
        $entity->onCommit();
        $gateway->deleteById($entity->id, $entity->getVars());
    }

    /**
     * Commits new entities that are to be assigned so that foreign keys are set properly
     *
     * @param SabaiFramework_Model_Entity $entity
     * @return bool;
     */
    protected function _commitEntitiesToBeAssigned(SabaiFramework_Model_Entity $entity)
    {
        $entities_to_be_assigned = $entity->fetchEntitiesToBeAssigned();
        foreach (array_keys($entities_to_be_assigned) as $entity_name) {
            foreach ($entities_to_be_assigned[$entity_name] as $entity_temp_id) {
                // Commit the entity if not already committed
                if ($entity_to_be_assigned = @$this->_entities['new'][$entity_name][$entity_temp_id]) {
                    $this->_commitOneNew($this->getGateway($entity_name), $entity_to_be_assigned);
                    unset($entity_to_be_assigned, $this->_entities['new'][$entity_name][$entity_temp_id]);
                }
            }
        }
    }

    /**
     * Assigns an entity to entities that reference this entity so that the new ID
     * is propagated properly to the referencing entities
     *
     * @param SabaiFramework_Model_Entity $entity
     */
    protected function _commitNewEntityAssign(SabaiFramework_Model_Entity $entity)
    {
        if (!$entities_to_assign = $entity->fetchEntitiesToAssign()) return;

        foreach (array_keys($entities_to_assign) as $i) {
            if (is_array($entities_to_assign[$i])) {
                foreach ($entities_to_assign[$i] as $entity_to_assign) {
                    $entity_to_assign->$i($entity);
                }
            } else {
                $entity_name = $entities_to_assign[$i]->getName() === $entity->getName() ? 'Parent' : $entity->getName();
                $entities_to_assign[$i]->$entity_name = $entity;
            }
        }
        $entity->clearEntitiesToAssign();
    }
}