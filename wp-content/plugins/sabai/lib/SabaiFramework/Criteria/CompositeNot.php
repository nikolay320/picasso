<?php
class SabaiFramework_Criteria_CompositeNot extends SabaiFramework_Criteria_Composite
{
    /**
     * Accepts a Visitor object
     *
     * @param SabaiFramework_Criteria_Visitor $visitor
     * @param mixed $valuePassed
     */
    public function acceptVisitor(SabaiFramework_Criteria_Visitor $visitor, &$valuePassed)
    {
        $visitor->visitCriteriaCompositeNot($this, $valuePassed);
    }
}
