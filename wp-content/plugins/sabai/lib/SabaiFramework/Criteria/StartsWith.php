<?php
class SabaiFramework_Criteria_StartsWith extends SabaiFramework_Criteria_String
{
    /**
     * Accepts a Visitor object
     *
     * @param SabaiFramework_Criteria_Visitor $visitor
     * @param mixed $valuePassed
     */
    public function acceptVisitor(SabaiFramework_Criteria_Visitor $visitor, &$valuePassed)
    {
        $visitor->visitCriteriaStartsWith($this, $valuePassed);
    }
}
