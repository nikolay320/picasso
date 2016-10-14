<?php
class Sabai_Addon_Questions_Taxonomy implements Sabai_Addon_Taxonomy_ITaxonomy
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Questions $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function taxonomyGetInfo()
    {
        switch ($this->_name) {
            case $this->_addon->getTagsBundleName():
                return array(
                    'type' => 'questions_tags',
                    'path' => '/' . $this->_addon->getSlug('tags'),
                    'admin_path' => '/' . strtolower($this->_addon->getName()) . '/tags',
                    'label' => $this->_addon->getApplication()->_t(_n_noop('Tags', 'Taxonomy', 'sabai-discuss'), 'sabai-discuss'),
                    'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Tag', 'Taxonomy', 'sabai-discuss'), 'sabai-discuss'),
                    'taxonomy_body' => array(
                        'required' => false,
                        'label' => __('Description', 'sabai-discuss'),
                        'widget_settings' => array('rows' => 15),
                        'weight' => 5,
                    ),
                    'taxonomy_default_permissions' => array(
                        'add', // grant add tag permission by default otherwise users will not be able to add new tags
                    ),
                    'filterable' => false,
                );
            case $this->_addon->getCategoriesBundleName():
                return array(
                    'type' => 'questions_categories',
                    'path' => '/' . $this->_addon->getSlug('categories'),
                    'admin_path' => '/' . strtolower($this->_addon->getName()) . '/categories',
                    'label' => $this->_addon->getApplication()->_t(_n_noop('Categories', 'Categories', 'sabai-discuss'), 'sabai-discuss'),
                    'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Category', 'Category', 'sabai-discuss'), 'sabai-discuss'),
                    'taxonomy_hierarchical' => true,
                    'taxonomy_body' => array(
                        'required' => false,
                        'label' => __('Description', 'sabai-discuss'),
                        'widget_settings' => array('rows' => 15),
                        'weight' => 6,
                    ),
                    'properties' => array(
                        'term_parent' => array(
                            'title' => __('Parent Category', 'sabai-discuss'),
                            'weight' => 4,
                        ),
                        'term_title' => array(
                            'weight' => 2,
                        ),
                    ),
                    'filterable' => false,
                );
        }
    }
}