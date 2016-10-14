<?php
class SabaiFramework_Criteria_IsOrGreaterThan extends SabaiFramework_Criteria_Value
{
    /**
     * Accepts a Visitor object
     *
     * @param SabaiFramework_Criteria_Visitor $visitor
     * @param mixed $valuePassed
     */
    public function acceptVisitor(SabaiFramework_Criteria_Visitor $visitor, &$valuePassed)
    {
        $visitor->visitCriteriaIsOrGreaterThan($this, $valuePassed);
    }
}
