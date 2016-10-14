<?php
require_once 'HTML/QuickForm/ElementGrid.php';

class SabaiFramework_HTMLQuickForm_Element_Grid extends HTML_QuickForm_ElementGrid
{
    protected $_emptyText, $_rowAttributes, $_columnAttributes, $_footerAttributes;

    public function __construct($name = null, $label = null, $options = null)
    {
        parent::HTML_QuickForm_ElementGrid($name, $label, $options);
    }

    public function addColumnName($columnName, array $columnAttributes = null)
    {
        $this->_columnNames[] = $columnName;
        $this->_columnAttributes[] = $columnAttributes;
    }

    /**
     * Sets the rows
     *
     * @param array array of HTML_QuickForm elements
     */
    public function setRows($rows)
    {
        foreach (array_keys($rows) as $key) {
            $this->addRow($rows[$key]);
        }
    }

    /**
     * Adds a row to the grid
     *
     * @param array array of HTML_QuickForm elements
     */
    public function addRow($row, $attributes = null)
    {
        $key = sizeof($this->_rows);
        $this->_rows[$key] = $row;

        //if updateValue has been called make sure to update the values of each added element
        foreach (array_keys($this->_rows[$key]) as $key2) {
            if (isset($this->_form)) {
                $this->_rows[$key][$key2]->onQuickFormEvent('updateValue', null, $this->_form);
            }
        }

        if (!empty($attributes)) $this->_rowAttributes[$key] = $attributes;
    }

    /**
     * Returns Html for the element
     *
     * @access      public
     * @return      string
     */
    public function toHtml()
    {
        require_once 'HTML/Table.php';
        $table = new HTML_Table(null, 0, true);
        $table->updateAttributes($this->getAttributes());

        $tbody = $table->getBody();
        $tbody->setAutoGrow(true);
        $tbody->setAutoFill('');

        $thead = $table->getHeader();
        $thead->setAutoGrow(true);
        $thead->setAutoFill('');

        $col = 0;
        if ($this->_columnNames) {
            foreach ($this->_columnNames as $key => $value) {
                $thead->setHeaderContents(0, $col, $value, $this->_columnAttributes[$key]);
                ++$col;
            }
        }
        if (!empty($this->_rows)) {
            $row = 0;
            foreach (array_keys($this->_rows) as $key) {
                $col = 0;
                foreach (array_keys($this->_rows[$key]) as $key2) {
                    $tbody->setCellContents($row, $col, $this->_rows[$key][$key2] ? $this->_rows[$key][$key2]->toHTML() : '');
                    $attributes = isset($this->_rowAttributes[$key]['@all']) ? $this->_rowAttributes[$key]['@all'] : array();
                    if (isset($this->_rowAttributes[$key][$key2])) $attributes = $this->_rowAttributes[$key][$key2] + $attributes;
                    if (!empty($attributes)) $tbody->setCellAttributes($row, $col, $attributes);
                    ++$col;
                }
                if (isset($this->_rowAttributes[$key]['@row'])) $tbody->setRowAttributes($row, $this->_rowAttributes[$key]['@row'], true);
                ++$row;
            }
        } elseif (isset($this->_emptyText)) {
            $tbody->setCellContents(0, 0, $this->_emptyText);
            $tbody->setCellAttributes(0, 0, array('align' => 'center', 'colspan' => count($this->_columnNames)));
        }

        return $table->toHTML();
    }

    public function setEmptyText($emptyText)
    {
        $this->_emptyText = $emptyText;
    }

    public function getRowCount()
    {
        return count($this->_rows);
    }
}