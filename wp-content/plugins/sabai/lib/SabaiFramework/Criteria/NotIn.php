<?php
class SabaiFramework_Criteria_NotIn extends SabaiFramework_Criteria_In
{
    /**
     * Accepts a Visitor object
     *
     * @param SabaiFramework_Criteria_Visitor $visitor
     * @param mixed $valuePassed
     */
    public function acceptVisitor(SabaiFramework_Criteria_Visitor $visitor, &$valuePassed)
    {
        $visitor->visitCriteriaNotIn($this, $valuePassed);
    }
}
