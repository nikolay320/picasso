<?php
abstract class SabaiFramework_Model_EntityCriteria extends SabaiFramework_Criteria_Composite
{
    protected $_name;
    private $_andOr;
    protected $_keys = array();
    
    public function __construct($name)
    {
        $this->_name = $name;
    }
    
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Appends a new criteria
     *
     * @param SabaiFramework_Criteria $criteria
     */
    public function add(SabaiFramework_Criteria $criteria)
    {
        switch ($this->_andOr) {
            case SabaiFramework_Criteria::CRITERIA_OR:
                $this->addOr($criteria);
                $this->_andOr = SabaiFramework_Criteria::CRITERIA_AND;
                break;
            case SabaiFramework_Criteria::CRITERIA_AND:
            default:
                $this->addAnd($criteria);
                break;
        }
        return $this;
    }

    /**
     * Adds an AND condition to the criteria
     * @return SabaiFramework_Model_EntityCriteria
     */
    public function and_()
    {
        $this->_andOr = SabaiFramework_Criteria::CRITERIA_AND;
        return $this;
    }

    /**
     * Adds an OR condition to the criteria
     * @return SabaiFramework_Model_EntityCriteria
     */
    public function or_()
    {
        $this->_andOr = SabaiFramework_Criteria::CRITERIA_OR;
        return $this;
    }

    /**
     * Magically adds a new criteria
     * @param string $method
     * @param array $args
     * @return SabaiFramework_Model_EntityCriteria
     */
    public function __call($method, $args)
    {
        @list($key, $type, $key2) = explode('_', $method);
        if ($field = @$this->_keys[$key]) {
            // If second key is set, check if it has a valid field
            if (isset($key2) && ($field2 = @$this->_keys[$key2])) {
                switch ($type) {
                    case 'is':
                        return $this->add(new SabaiFramework_Criteria_IsField($field, $field2));
                    case 'isNot':
                        return $this->add(new SabaiFramework_Criteria_IsNotField($field, $field2));
                    case 'isGreaterThan':
                        return $this->add(new SabaiFramework_Criteria_IsGreaterThanField($field));
                    case 'isSmallerThan':
                        return $this->add(new SabaiFramework_Criteria_IsSmallerThanField($field, $field2));
                    case 'isOrGreaterThan':
                        return $this->add(new SabaiFramework_Criteria_IsOrGreaterThanField($field, $field2));
                    case 'isOrSmallerThan':
                        return $this->add(new SabaiFramework_Criteria_IsOrSmallerThanField($field, $field2));
                }
            } else {
                switch ($type) {
                    case 'is':
                        return $this->add(new SabaiFramework_Criteria_Is($field, $args[0]));
                    case 'isNot':
                        return $this->add(new SabaiFramework_Criteria_IsNot($field, $args[0]));
                    case 'isGreaterThan':
                        return $this->add(new SabaiFramework_Criteria_IsGreaterThan($field, $args[0]));
                    case 'isSmallerThan':
                        return $this->add(new SabaiFramework_Criteria_IsSmallerThan($field, $args[0]));
                    case 'isOrGreaterThan':
                        return $this->add(new SabaiFramework_Criteria_IsOrGreaterThan($field, $args[0]));
                    case 'isOrSmallerThan':
                        return $this->add(new SabaiFramework_Criteria_IsOrSmallerThan($field, $args[0]));
                    case 'in':
                        return $this->add(new SabaiFramework_Criteria_In($field, $args[0]));
                    case 'notIn':
                        return $this->add(new SabaiFramework_Criteria_NotIn($field, $args[0]));
                    case 'startsWith':
                        return $this->add(new SabaiFramework_Criteria_StartsWith($field, $args[0]));
                    case 'endsWith':
                        return $this->add(new SabaiFramework_Criteria_EndsWith($field, $args[0]));
                    case 'contains':
                        return $this->add(new SabaiFramework_Criteria_Contains($field, $args[0]));
                    case 'isNull':
                        return $this->add(new SabaiFramework_Criteria_IsNull($field));
                }
            }
        }

        throw new SabaiFramework_Exception(sprintf('Call to undefined method %s', $method));
    }
}