<?php
class SabaiFramework_Criteria_In extends SabaiFramework_Criteria_Array
{
    /**
     * Accepts a Visitor object
     *
     * @param SabaiFramework_Criteria_Visitor $visitor
     * @param mixed $valuePassed
     */
    public function acceptVisitor(SabaiFramework_Criteria_Visitor $visitor, &$valuePassed)
    {
        $visitor->visitCriteriaIn($this, $valuePassed);
    }
}
