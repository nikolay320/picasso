<?php
class SabaiFramework_User_IdentityPaginator extends SabaiFramework_Paginator
{
    protected $_identityFetcher;
    protected $_sort;
    protected $_order;

    public function __construct(SabaiFramework_User_IdentityFetcher $identityFetcher, $perpage, $sort, $order, $key = 0)
    {
        parent::__construct($perpage, $key);
        $this->_identityFetcher = $identityFetcher;
        $this->_sort = $sort;
        $this->_order = $order;
    }

    protected function _getElementCount()
    {
        return $this->_identityFetcher->count();
    }

    protected function _getElements($limit, $offset)
    {
        return $this->_identityFetcher->fetch($limit, $offset, $this->_sort, $this->_order);
    }
}