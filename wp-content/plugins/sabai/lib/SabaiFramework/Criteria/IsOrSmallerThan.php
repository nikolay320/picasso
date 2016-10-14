<?php
class SabaiFramework_Criteria_IsOrSmallerThan extends SabaiFramework_Criteria_Value
{
    /**
     * Accepts a Visitor object
     *
     * @param SabaiFramework_Criteria_Visitor $visitor
     * @param mixed $valuePassed
     */
    public function acceptVisitor(SabaiFramework_Criteria_Visitor $visitor, &$valuePassed)
    {
        $visitor->visitCriteriaIsOrSmallerThan($this, $valuePassed);
    }
}
