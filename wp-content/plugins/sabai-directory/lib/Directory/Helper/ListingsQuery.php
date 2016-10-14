<?php
class Sabai_Addon_Directory_Helper_ListingsQuery extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Query $query, array $latlng, array $keywords, $category, $distance = 0, $isMile = false, $featuredOnly = false, $claimedOnly = false, $matchAny = false, array $fields = null)
    {
        if (!empty($latlng)) {
            $lat = $latlng[0];
            $lng = $latlng[1];
            if (!is_array($distance)) {
                $target = array(
                    'table' => array(
                        $application->getDB()->getResourcePrefix() . 'entity_field_directory_location'  => array(
                            'alias' => 'directory_location',
                            'on' => null,
                        ),
                    ),
                    'column' => sprintf(
                        '(%1$d * acos(cos(radians(%2$.6F)) * cos(radians(directory_location.lat)) * cos(radians(directory_location.lng) - radians(%3$.6F)) + sin(radians(%2$.6F)) * sin(radians(directory_location.lat))))',
                        $isMile ? 3959 : 6371,
                        $lat,
                        $lng
                    ),
                    'column_type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
                    'is_property' => false,
                    'field_name' => false,
                );
                $query->addCriteria(new SabaiFramework_Criteria_IsOrSmallerThan($target, $distance));
            } else {
                $lat1 = $distance[0][0];
                $lat2 = $distance[1][0];
                $lng1 = $distance[0][1];
                $lng2 = $distance[1][1];
                $query->fieldIsOrGreaterThan('directory_location', $lat1, 'lat')
                    ->fieldIsOrSmallerThan('directory_location', $lat2, 'lat')
                    ->fieldIsOrGreaterThan('directory_location', $lng1, 'lng')
                    ->fieldIsOrSmallerThan('directory_location', $lng2, 'lng');
            }
            $query->addExtraField('lat', 'directory_location.lat')
                ->addExtraField('lng', 'directory_location.lng')
                ->addExtraField('weight', 'directory_location.weight')
                ->isDistinct(false);
        }
        if (!empty($keywords[0])) {
            if ($matchAny) {
                $query->startCriteriaGroup('OR');
                $this->_queryKeywordFields($query, $keywords, $fields);
                $query->finishCriteriaGroup();
            } else {
                $this->_queryKeywordFields($query, $keywords, $fields);
            }
        }
        if (!empty($category)) {
            if (!is_array($category)) {
                $category = array($category);
                foreach ($application->Taxonomy_Descendants($category[0], false) as $_category) {
                    $category[] = $_category->id;
                }
            }
            $query->fieldIsIn('directory_category', $category);
        }
        if ($featuredOnly) {
            $query->fieldIsNotNull('content_featured');
        }
        if ($claimedOnly) {
            $query->fieldIsNotNull('directory_claim', 'claimed_by');
        }
        return $query;
    }
    
    protected function _queryKeywordFields($query, array $keywords, array $fields = null)
    {
        if (isset($fields)) {
            foreach ($keywords[0] as $keyword) {
                $query->startCriteriaGroup('OR')
                    ->fieldContains('content_body', $keyword)
                    ->propertyContains('post_title', $keyword);
                foreach ($fields as $field) {
                    $query->fieldContains($field, $keyword);
                }
                $query->finishCriteriaGroup();
            }
        } else {
            foreach ($keywords[0] as $keyword) {
                $query->startCriteriaGroup('OR')
                    ->fieldContains('content_body', $keyword)
                    ->propertyContains('post_title', $keyword)
                    ->finishCriteriaGroup();
            }
        }
    }
}