<?php
abstract class SabaiFramework_Model_Paginator extends SabaiFramework_Paginator
{
    protected $_repository;
    protected $_sort;
    protected $_order;

    public function __construct(SabaiFramework_Model_EntityRepository $repository, $perpage, $sort, $order, $key = 0)
    {
        parent::__construct($perpage, $key);
        $this->_repository = $repository;
        $this->_sort = $sort;
        $this->_order = $order;
    }

    protected function _getEmptyElements()
    {
        return $this->_repository->createCollection();
    }
}