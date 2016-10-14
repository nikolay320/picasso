<?php
class SabaiFramework_DB_RowsetIterator implements Iterator, Countable
{
    protected $_rs, $_key;

    public function __construct(SabaiFramework_DB_Rowset $rs)
    {
        $this->_rs = $rs;
        $this->_key = 0;
    }

    public function rewind()
    {
        $this->_key = 0;
    }

    public function valid()
    {
        return $this->_rs->seek($this->_key);
    }

    public function next()
    {
        ++$this->_key;
    }

    public function current()
    {
        return $this->_rs->fetchAssoc();
    }

    public function key()
    {
        return $this->_key;
    }
    
    public function count()
    {
        return $this->_rs->rowCount();
    }
}