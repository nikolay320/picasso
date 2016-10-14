<?php
class Sabai_Model extends SabaiFramework_Model
{
    /**
     * @var Sabai_HelperBroker
     */
    private $_helperBroker;

    public function __construct(Sabai_Addon $addon)
    {
        parent::__construct(
            $addon->getApplication()->getDB(),
            $addon->getApplication()->getAddonPath($addon->getType()) . '/Model',
            'Sabai_Addon_' . $addon->getType() . '_Model_'
        );
        $this->_helperBroker = $addon->getApplication()->getHelperBroker();
    }

    public function __call($name, $args)
    {
        return $this->_helperBroker->callHelper($name, $args);
    }
}