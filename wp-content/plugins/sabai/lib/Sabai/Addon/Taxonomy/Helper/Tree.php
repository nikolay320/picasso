<?php
class Sabai_Addon_Taxonomy_Helper_Tree extends Sabai_Helper
{
    public function help(Sabai $application, $bundleName, array $options = array(), array $tree = array())
    {
        $options += array(
            'prefix' => '--',
            'depth' => 0,
            'init_depth' => 1,
            'content_bundle' => null,
            'hide_empty' => false,
        );
        $terms = $application->Taxonomy_Terms(is_object($bundleName) ? $bundleName->name : $bundleName);
        $this->_makeTermTree($terms, $options, $tree, $options['init_depth']);
        
        return $tree; 
    }
    
    private function _makeTermTree($terms, $options, &$tree, $depth, $parentId = 0)
    {
        if (!isset($terms[$parentId])) return;

        if ($options['depth'] && $depth > $options['depth']) return;

        $_prefix = str_repeat($options['prefix'], $depth - 1);
        foreach ($terms[$parentId] as $term_id => $term) {
            if (isset($options['content_bundle'])) {
                if (!isset($term['count'][$options['content_bundle']])) {
                    if ($options['hide_empty']) {
                        continue;
                    }
                    $count = 0;
                } else {
                    $count = $term['count'][$options['content_bundle']];
                }
            } else {
                $count = null;
            }
            $title = $_prefix . $term['title'];
            $tree[$term_id] = isset($count) ? sprintf(__('%s (%d)', 'sabai'), $title, $count) : $title;
            $this->_makeTermTree($terms, $options, $tree, $depth + 1, $term_id);
        }
    }
}