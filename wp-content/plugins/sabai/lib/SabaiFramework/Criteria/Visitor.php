<?php
interface SabaiFramework_Criteria_Visitor
{
    public function visitCriteriaComposite(SabaiFramework_Criteria_Composite $criteria, &$valuePassed);
    public function visitCriteriaCompositeNot(SabaiFramework_Criteria_CompositeNot $criteria, &$valuePassed);
    public function visitCriteriaEmpty(SabaiFramework_Criteria_Empty $criteria, &$valuePassed);
    public function visitCriteriaContains(SabaiFramework_Criteria_Contains $criteria, &$valuePassed);
    public function visitCriteriaStartsWith(SabaiFramework_Criteria_StartsWith $criteria, &$valuePassed);
    public function visitCriteriaEndsWith(SabaiFramework_Criteria_EndsWith $criteria, &$valuePassed);
    public function visitCriteriaIn(SabaiFramework_Criteria_In $criteria, &$valuePassed);
    public function visitCriteriaNotIn(SabaiFramework_Criteria_NotIn $criteria, &$valuePassed);
    public function visitCriteriaIsNull(SabaiFramework_Criteria_IsNull $criteria, &$valuePassed);
    public function visitCriteriaIsNotNull(SabaiFramework_Criteria_IsNotNull $criteria, &$valuePassed);
    public function visitCriteriaIs(SabaiFramework_Criteria_Is $criteria, &$valuePassed);
    public function visitCriteriaIsNot(SabaiFramework_Criteria_IsNot $criteria, &$valuePassed);
    public function visitCriteriaIsSmallerThan(SabaiFramework_Criteria_IsSmallerThan $criteria, &$valuePassed);
    public function visitCriteriaIsGreaterThan(SabaiFramework_Criteria_IsGreaterThan $criteria, &$valuePassed);
    public function visitCriteriaIsOrSmallerThan(SabaiFramework_Criteria_IsOrSmallerThan $criteria, &$valuePassed);
    public function visitCriteriaIsOrGreaterThan(SabaiFramework_Criteria_IsOrGreaterThan $criteria, &$valuePassed);
    public function visitCriteriaIsField(SabaiFramework_Criteria_IsField $criteria, &$valuePassed);
    public function visitCriteriaIsNotField(SabaiFramework_Criteria_IsNotField $criteria, &$valuePassed);
    public function visitCriteriaIsSmallerThanField(SabaiFramework_Criteria_IsSmallerThanField $criteria, &$valuePassed);
    public function visitCriteriaIsGreaterThanField(SabaiFramework_Criteria_IsGreaterThanField $criteria, &$valuePassed);
    public function visitCriteriaIsOrSmallerThanField(SabaiFramework_Criteria_IsOrSmallerThanField $criteria, &$valuePassed);
    public function visitCriteriaIsOrGreaterThanField(SabaiFramework_Criteria_IsOrGreaterThanField $criteria, &$valuePassed);
}