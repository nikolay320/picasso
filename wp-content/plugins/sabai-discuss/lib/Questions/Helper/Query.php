<?php
class Sabai_Addon_Questions_Helper_Query extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Query $query, array $keywords, $category, $tag, $feature = false, $featuredOnly = false, $matchAny = false, array $fields = null)
    {
        if (!empty($category)) {
            if (!is_array($category)) {
                $category = array($category);
                foreach ($application->Taxonomy_Descendants($category[0], false) as $_category) {
                    $category[] = $_category->id;
                }
            }
            $query->fieldIsIn('questions_categories', $category);
        }
        if (!empty($keywords[0])) {
            $table_prefix = $application->getDB()->getResourcePrefix();
            $query->setTableIdColumn('COALESCE(content_parent.value, entity.post_id)')
                ->addTableJoin($table_prefix . 'entity_field_content_parent', 'content_parent', 'entity_id = entity.post_id');
            $target = array(
                'table' => array(
                    $table_prefix . 'entity_field_content_body'  => array(
                        'alias' => 'questions_body',
                        'on' => 'entity_id = entity.post_id', // need this since we changed the table ID column in order to target both questions and answers
                    ),
                ),
                'column' => 'questions_body.value',
                'column_type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'is_property' => false,
                'field_name' => false,
            );
            if (!empty($keywords[0])) {
                if ($matchAny) {
                    $query->startCriteriaGroup('OR');
                    $this->_queryKeywordFields($query, $keywords, $target, $fields);
                    $query->finishCriteriaGroup();
                } else {
                    $this->_queryKeywordFields($query, $keywords, $target, $fields);
                }
            }
        }
        if (!empty($tag)) {
            $query->fieldIs('questions_tags', $tag);
        }
        if (!empty($feature)) {
            $query->sortByField('content_featured', 'DESC');
        }
        if ($featuredOnly) {
            $query->fieldIsNotNull('content_featured');
        }
        return $query;
    }
    
    protected function _queryKeywordFields($query, array $keywords, $target, array $fields = null)
    {
        if (isset($fields)) {
            foreach ($keywords[0] as $keyword) {
                $query->startCriteriaGroup('OR')
                    ->addCriteria(new SabaiFramework_Criteria_Contains($target, $keyword))
                    ->propertyContains('post_title', $keyword);
                foreach ($fields as $field) {
                    $query->fieldContains($field, $keyword);
                }
                $query->finishCriteriaGroup();
            }
        } else {
            foreach ($keywords[0] as $keyword) {
                $query->startCriteriaGroup('OR')
                    ->addCriteria(new SabaiFramework_Criteria_Contains($target, $keyword))
                    ->propertyContains('post_title', $keyword)
                    ->finishCriteriaGroup();
            }
        }
    }
}