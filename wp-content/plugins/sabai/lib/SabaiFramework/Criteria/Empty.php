<?php
class SabaiFramework_Criteria_Empty extends SabaiFramework_Criteria
{
    public function isEmpty()
    {
        return true;
    }

    /**
     * Accepts a Visitor object
     *
     * @param SabaiFramework_Criteria_Visitor $visitor
     * @param mixed $valuePassed
     */
    public function acceptVisitor(SabaiFramework_Criteria_Visitor $visitor, &$valuePassed)
    {
        $visitor->visitCriteriaEmpty($this, $valuePassed);
    }
}