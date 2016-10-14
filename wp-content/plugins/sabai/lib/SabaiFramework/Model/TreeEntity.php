<?php
abstract class SabaiFramework_Model_TreeEntity extends SabaiFramework_Model_Entity
{
    protected $_parentsCount, $_descendantsCount, $_childrenCount;
 
    /**
     * Retrieves all parent entities of this entity
     *
     * @return SabaiFramework_Model_EntityCollection
     */
    public function getParents()
    {
        if (!isset($this->_objects['Parents'])) {
            $this->_objects['Parents'] = $this->_getRepository()->fetchParents($this->id);
        }

        return $this->_objects['Parents'];
    }

    protected function _fetchChildren()
    {
        if (!isset($this->_objects['Children'])) {
            $this->_objects['Children'] = $this->_getRepository()->fetchByParent($this->id);
        }

        return $this->_objects['Children'];
    }

    /**
     * Retrieves all child entities of this entity
     *
     * @return SabaiFramework_Model_EntityCollection
     */
    public function getDescendants()
    {
        if (!isset($this->_objects['Descendants'])) {
            $this->_objects['Descendants'] = $this->_getRepository()->fetchDescendantsByParent($this->id);
        }

        return $this->_objects['Descendants'];
    }

    public function setParentsCount($count)
    {
        $this->_parentsCount = $count;
    }

    public function setChildrenCount($count)
    {
        $this->_childrenCount = $count;
    }

    public function setDescendantsCount($count)
    {
        $this->_descendantsCount = $count;
    }

    /**
     * Gets the number of all first-level child entities
     *
     * @return int
     */
    public function getChildrenCount()
    {
        if (!isset($this->_childrenCount)) {
            $this->_childrenCount = $this->_getRepository()->countByParent($this->id);
        }

        return $this->_childrenCount;
    }

    /**
     * Gets the number of all (or first-level) child entities
     *
     * @return int
     */
    public function getDescendantsCount()
    {
        if (!isset($this->_descendantsCount)) {
            $this->_descendantsCount = $this->_getRepository()->countDescendantsByParent($this->id);
        }

        return $this->_descendantsCount;
    }

    /**
     * Gets the number of all parent entities for this entity
     *
     * @return int
     */
    public function getParentsCount()
    {
        if (!isset($this->_parentsCount)) {
            $this->_parentsCount = $this->_getRepository()->countParents($this->id);
        }

        return $this->_parentsCount;
    }

    /**
     * Creates a new child entity
     *
     * @return mixed SabaiFramework_Model_TreeEntity on success, false on failure
     */
    public function createChild()
    {
        if (!$this->id) {
            throw new SabaiFramework_Exception(sprintf('Cannot create a new child entity for a non-existent %s entity', $this->getName()));
        }
        $child = $this->_model->create($this->getName());
        $child->Parent = $this;

        return $child;
    }
}