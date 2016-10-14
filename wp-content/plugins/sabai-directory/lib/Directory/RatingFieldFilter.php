<?php
class Sabai_Addon_Directory_RatingFieldFilter extends Sabai_Addon_Voting_RatingFieldFilter
{
    protected $_valueColumn = 'value';
    
    protected function _fieldFilterGetInfo()
    {
        return array('field_types' => array('directory_rating')) + parent::_fieldFilterGetInfo();
    }
}