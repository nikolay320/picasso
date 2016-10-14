<?php
class SabaiFramework_Criteria_IsOrGreaterThanField extends SabaiFramework_Criteria_Field
{
    /**
     * Accepts a Visitor object
     *
     * @param SabaiFramework_Criteria_Visitor $visitor
     * @param mixed $valuePassed
     */
    public function acceptVisitor(SabaiFramework_Criteria_Visitor $visitor, &$valuePassed)
    {
        $visitor->visitCriteriaIsOrGreaterThanField($this, $valuePassed);
    }
}
