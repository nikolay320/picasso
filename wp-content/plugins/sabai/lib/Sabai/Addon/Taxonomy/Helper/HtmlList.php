<?php
class Sabai_Addon_Taxonomy_Helper_HtmlList extends Sabai_Addon_Taxonomy_Helper_List
{    
    protected function _getDefaults()
    {
        return parent::_getDefaults() + array(
            'format' => null,
            'permalink' => array(),
            'prefix' => '-',
        );
    }

    protected function _makeTermHtml(Sabai $application, array $terms, array &$html, array $options, $parentId = 0, $depth = 1)
    {
        $html[] = isset($options['class']) ? '<ul class="'. $options['class'] .'">' : '<ul>';
        parent::_makeTermHtml($application, $terms, $html, $options, $parentId, $depth);
        $html[] = '</ul>';
    }
    
    protected function _renderTerm(Sabai $application, array $term, array $options, $depth, $count)
    {
        $formatted = $application->Entity_Permalink($term, $options['permalink']);
        if (isset($options['format'])) {
            if (is_array($options['format'])) {
                // Check if format for the current term is set
                if (isset($options['format'][$term['id']])) {
                    // Use the format specifically set for the current term
                    $formatted = sprintf($options['format'][$term['id']], $formatted, $count);
                } else {
                    $formatted = sprintf($options['format'][0], $formatted, $count);
                }
            } else {
                $formatted = sprintf($options['format'], $formatted, $count);
            }
        } else {
            if (isset($count)) {
                $formatted = sprintf(__('%s (%d)', 'sabai'), $formatted, $count);
            }
        }
        if ($options['prefix']) {
            $formatted = str_repeat($options['prefix'], $depth - 1) . $formatted;
        }
        
        return '<li data-sabai-taxonomy-term="'. $term['id'] .'">' . $formatted . '</li>';
    }
}