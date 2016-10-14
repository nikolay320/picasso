<?php
class Sabai_Addon_Directory_Controller_AllCategories extends Sabai_Controller
{ 
    protected function _doExecute(Sabai_Context $context)
    {
        $defaults = array(
            'addons' => array(),
            'columns' => 2,
            'hide_children' => false,
            'hide_count' => false,
            'hide_empty' => false,
            'child_count' => 0,
        );
        $attr = array_intersect_key($context->getAttributes(), $defaults) + $defaults;
        if (!is_array($attr['addons'])) {
            $attr['addons'] = array_map('trim', explode(',', $attr['addons']));
        }

        $directories = array();   
        // Fetch main directory
        if (in_array('Directory', $attr['addons']) || empty($attr['addons'])) {
            $directory_addon = $this->getAddon('Directory');
            $directories[$directory_addon->getCategoryBundleName()] = array(
                'title' => $directory_addon->getTitle('directory'),
                'url' => $this->Url('/' . $directory_addon->getSlug('directory')),
            );
        }
        // Fetch cloned directories
        $cloned_addons = $this->getModel('Addon', 'System')->parentAddon_is('Directory');
        if (!empty($attr['addons'])) {
            $cloned_addons->name_in($attr['addons']);
        }
        foreach ($cloned_addons->fetch() as $addon) {
            $cloned_addon = $this->getAddon($addon->name);
            $directories[$cloned_addon->getCategoryBundleName()] = array(
                'title' => $cloned_addon->getTitle('directory'),
                'url' => $this->Url('/' . $cloned_addon->getSlug('directory')),
            );
        }
        // Fetch category entities for each directory
        $entities = array();
        foreach (array_keys($directories) as $category_bundle) {    
            $entities[$category_bundle] = $this->Entity_Query('taxonomy')
                ->propertyIs('term_entity_bundle_name', $category_bundle)->sortByProperty('term_title')
                ->propertyIs('term_parent', 0)
                ->fetch();
            $entities[$category_bundle] = $this->Entity_Render('taxonomy', $entities[$category_bundle], null, 'summary');
        }
        
        $context->column_count = isset($attr['columns']) && in_array($attr['columns'], array(1, 2, 3, 4)) ? $attr['columns'] : 2;
        $context->hide_children = !empty($attr['hide_children']);
        $context->hide_empty = !empty($attr['hide_empty']);
        $context->hide_count = !empty($attr['hide_count']);
        $context->child_count = (int)@$attr['child_count'];
        if (count($directories) === 1) {
            $context->addTemplate('directory_categories')
                ->setAttributes(array(
                    'entities' => array_shift($entities),
                ));
        } else {
            uasort($directories, array($this, '_sortDirectories'));
            $context->addTemplate('directory_all_categories')
                ->setAttributes(array(
                    'entities' => $entities,
                    'directories' => $directories,
                ));
        }
    }
    
    private function _sortDirectories($a, $b)
    {
        return strcmp($a['title'], $b['title']);
    }
}