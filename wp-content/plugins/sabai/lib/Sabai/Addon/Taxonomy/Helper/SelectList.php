<?php
class Sabai_Addon_Taxonomy_Helper_SelectList extends Sabai_Addon_Taxonomy_Helper_List
{
    public function help(Sabai $application, $bundleName, array $options = array())
    {
        $options += parent::_getDefaults() + array(
            'class' => '',
            'name' => '',
            'current' => null,
            'default_text' => null,
            'prefix' => '-',
            'group' => false,
        );
        $html = array();
        if (is_array($bundleName)) {
            $options['group'] = count($bundleName) > 1;
            foreach ($bundleName as $bundle_name => $label) {
                $options['bundle_name'] = $bundle_name;
                if ($options['group']) {
                    $html[] = sprintf('<option value="%s"%s>%s</option>', $bundle_name, $options['current'] === $bundle_name ? ' selected="selected"' : '', Sabai::h($label));
                }
                $terms = $application->Taxonomy_Terms($bundle_name);
                if (!empty($terms[$options['parent']])) {
                    $this->_makeTermHtml($application, $terms, $html, $options, $options['parent']);
                }
            }       
        } else {
            $terms = $application->Taxonomy_Terms($bundleName);
            if (!empty($terms[$options['parent']])) {
                $this->_makeTermHtml($application, $terms, $html, $options, $options['parent']);
            }
        }
        if (empty($html)) {
            return '';
        }
        if (isset($options['default_text'])) {
            array_unshift($html, '<option value="'. $options['parent'] .'">' . Sabai::h($options['default_text']) . '</option>');
        }
        if (isset($options['class'])) {
            array_unshift($html, '<select name="'. $options['name'] .'" class="'. $options['class'] .'">');
        } else {
            array_unshift($html, '<select name="'. $options['name'] .'">');
        }
        $html[] = '</select>';
        
        return implode(PHP_EOL, $html);
    }
    
    protected function _renderTerm(Sabai $application, array $term, array $options, $depth, $count)
    {
        $selected = '';
        if (isset($options['current'])){
            if (is_numeric($options['current'])) {
                if ($term['id'] == $options['current']) {
                    $selected = ' selected="selected"';
                }
            } else {
                if (!isset($options['bundle_name']) || $options['bundle_name'] === $term['bundle_name']) {
                    if ($term['name'] == $options['current']) {
                        $selected = ' selected="selected"';
                    }
                }
            }
        }
        return sprintf(
            '<option value="%d"%s>%s%s</option>',
            $term['id'],
            $selected,
            str_repeat($options['prefix'], $options['group'] ? $depth : $depth - 1),
            isset($count) ? sprintf(__('%s (%d)', 'sabai'), Sabai::h($term['title']), $count) : Sabai::h($term['title'])
        );
    }
}