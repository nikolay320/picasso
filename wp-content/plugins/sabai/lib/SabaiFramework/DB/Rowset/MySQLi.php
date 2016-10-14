<?php
class SabaiFramework_DB_Rowset_MySQLi extends SabaiFramework_DB_Rowset
{
    public function fetchColumn($index = 0)
    {
        return ($row = mysqli_fetch_row($this->_rs)) ? $row[$index] : false;
    }

    public function fetchAllColumns($index = 0)
    {
        $ret = array();
        while ($row = mysqli_fetch_row($this->_rs)) $ret[] = $row[$index];

        return $ret;
    }
    
    public function fetchSingle()
    {
        return $this->fetchColumn(0);
    }

    public function fetchRow()
    {
        return mysqli_fetch_row($this->_rs);
    }

    public function fetchAssoc()
    {
        return mysqli_fetch_assoc($this->_rs);
    }

    public function fetchAll($mode = parent::FETCH_MODE_ASSOC)
    {
        $ret = array();
        $func = $mode === parent::FETCH_MODE_NUM ? 'mysqli_fetch_row' : 'mysqli_fetch_assoc';
        while ($row = $func($this->_rs)) $ret[] = $row;
        
        return $ret;
    }

    public function seek($rowNum = 0)
    {
        // mysqli_data_seek() returns null on success, false otherwise according to php.net
        return false !== mysqli_data_seek($this->_rs, $rowNum);
    }

    public function columnCount()
    {
        return mysqli_num_fields($this->_rs);
    }

    public function rowCount()
    {
        return mysqli_num_rows($this->_rs);
    }
}