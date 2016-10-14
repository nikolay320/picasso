<?php
abstract class SabaiFramework_DB_Rowset implements IteratorAggregate, Countable
{
    protected $_rs;

    const FETCH_MODE_NUM = 1, FETCH_MODE_ASSOC = 2;

    /**
     * Constructor
     *
     */
    public function __construct($rs)
    {
        $this->_rs = $rs;
    }

    /**
     * @return SabaiFramework_Model_GatewayRecordsetIterator
     */
    public function getIterator()
    {
        return new SabaiFramework_DB_RowsetIterator($this);
    }

    /**
     * Implementation of the Countable interface
     *
     * @return int
     */
    public function count()
    {
        return $this->rowCount();
    }

    /**
     * @param int $index
     * @return string
     */
    abstract public function fetchColumn($index = 0);
    /**
     * @param int $index
     * @return array
     */
    abstract public function fetchAllColumns($index = 0);
    /**
     * @return string
     */
    abstract public function fetchSingle();
    /**
     * @return array
     */
    abstract public function fetchAssoc();
    /**
     * @return array
     */
    abstract public function fetchRow();
    /**
     * @return array
     */
    abstract public function fetchAll($mode = SabaiFramework_DB_Rowset::FETCH_MODE_ASSOC);
    /**
     * @param int $rowNum
     * @return bool
     */
    abstract public function seek($rowNum = 0);
    /**
     * @return int
     */
    abstract public function columnCount();
    /**
     * @return int
     */
    abstract public function rowCount();
}