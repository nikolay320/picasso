<?php
abstract class Sabai_Addon_Entity_Model_WithBundle extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_bundles, $_bundleNameVar, $_bundleObjectVarName;

    public function __construct(SabaiFramework_Model_EntityCollection $collection, $bundleNameVar = 'entity_bundle_name', $bundleObjectVarName = 'EntityBundle')
    {
        parent::__construct($collection);
        $this->_bundleNameVar = $bundleNameVar;
        $this->_bundleObjectVarName = $bundleObjectVarName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_bundles)) {
            $this->_bundles = array();
            if ($this->_collection->count() > 0) {
                $bundle_names = array();
                while ($this->_collection->valid()) {
                    if ($bundle_name = $this->_collection->current()->{$this->_bundleNameVar}) {
                        $bundle_names[] = $bundle_name;
                    }
                    $this->_collection->next();
                }
                if (!empty($bundle_names)) {
                    $this->_bundles = $this->_model->Entity_Bundles(array_unique($bundle_names));
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        if (($bundle_name = $current->{$this->_bundleNameVar})
            && isset($this->_bundles[$bundle_name])
        ) {
            $current->assignObject($this->_bundleObjectVarName, $this->_bundles[$bundle_name]);
        } else {
            $current->assignObject($this->_bundleObjectVarName);
        }

        return $current;
    }
}