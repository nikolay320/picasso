<?php
class SabaiFramework_Criteria_Composite extends SabaiFramework_Criteria
{
    /**
     * Enter description here...
     *
     * @var array
     */
    protected $_elements = array();
    /**
     * Enter description here...
     *
     * @var array
     */
    protected $_conditions = array();

    /**
     * Constructor
     *
     * @param array $elements
     * @return SabaiFramework_Criteria_Composite
     */
    public function __construct(array $elements = array(), $condition = SabaiFramework_Criteria::CRITERIA_AND)
    {
        if (!empty($elements)) {
            if ($condition === SabaiFramework_Criteria::CRITERIA_OR) {
                foreach (array_keys($elements) as $i) {
                    $this->addOr($elements[$i]);
                }
            } else {
                foreach (array_keys($elements) as $i) {
                    $this->addAnd($elements[$i]);
                }
            }
        }
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getElements()
    {
        return $this->_elements;
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getConditions()
    {
        return $this->_conditions;
    }

    /**
     * Enter description here...
     *
     * @param SabaiFramework_Criteria $criteria
     */
    public function addAnd(SabaiFramework_Criteria $criteria)
    {
        $this->_elements[] = $criteria;
        $this->_conditions[] = SabaiFramework_Criteria::CRITERIA_AND;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param SabaiFramework_Criteria $criteria
     */
    public function addOr(SabaiFramework_Criteria $criteria)
    {
        $this->_elements[] = $criteria;
        $this->_conditions[] = SabaiFramework_Criteria::CRITERIA_OR;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->_elements);
    }

    /**
     * Accepts a Visitor object
     *
     * @param SabaiFramework_Criteria_Visitor $visitor
     * @param mixed $valuePassed
     */
    public function acceptVisitor(SabaiFramework_Criteria_Visitor $visitor, &$valuePassed)
    {
        $visitor->visitCriteriaComposite($this, $valuePassed);
    }
}
