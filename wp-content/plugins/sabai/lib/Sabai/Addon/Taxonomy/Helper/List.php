<?php
abstract class Sabai_Addon_Taxonomy_Helper_List extends Sabai_Helper
{
    public function help(Sabai $application, $bundleName, array $options = array())
    {
        $options += $this->_getDefaults();
        $html = array();
        $terms = $application->Taxonomy_Terms($bundleName);
        if (empty($terms[$options['parent']])) {
            return '';
        }  
        $this->_makeTermHtml($application, $terms, $html, $options, $options['parent']);
        
        return implode(PHP_EOL, $html);
    }
    
    protected function _getDefaults()
    {
        return array(
            'parent' => 0,
            'depth' => 0,
            'content_bundle' => null,
            'hide_empty' => false,
            'hide_count' => false,
        );
    }

    protected function _makeTermHtml(Sabai $application, array $terms, array &$html, array $options, $parentId = 0, $depth = 1)
    {
        if (isset($options['content_bundle'])) {
            foreach ($terms[$parentId] as $term) {
                if ($options['hide_empty'] && empty($term['count'][$options['content_bundle']])) {
                    continue;
                }
                if (!$options['hide_count']) {
                    $content_count = isset($term['count'][$options['content_bundle']]) ? $term['count'][$options['content_bundle']] : 0;
                } else {
                    $content_count = null;
                }
                $html[] = $this->_renderTerm($application, $term, $options, $depth, $content_count);
                // Add sub-lists if any child terms
                if (!empty($terms[$term['id']])
                    && (empty($options['depth']) || $depth + 1 <= $options['depth'])
                ) {
                    $this->_makeTermHtml($application, $terms, $html, $options, $term['id'], $depth + 1);
                }
            }
        } else {
            foreach ($terms[$parentId] as $term) {
                $html[] = $this->_renderTerm($application, $term, $options, $depth, null);
                // Add sub-lists if any child terms
                if (!empty($terms[$term['id']])
                    && (empty($options['depth']) || $depth + 1 <= $options['depth'])
                ) {
                    $this->_makeTermHtml($application, $terms, $html, $options, $term['id'], $depth + 1);
                }
            }
        }
    }
    
    abstract protected function _renderTerm(Sabai $application, array $term, array $options, $depth, $count);
}