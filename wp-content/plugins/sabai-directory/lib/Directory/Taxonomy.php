<?php
class Sabai_Addon_Directory_Taxonomy implements Sabai_Addon_Taxonomy_ITaxonomy
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Directory $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function taxonomyGetInfo()
    {
        switch ($this->_name) {
            case $this->_addon->getCategoryBundleName():
                return array(
                    'type' => 'directory_category',
                    'path' => '/' . $this->_addon->getSlug('categories'),
                    'admin_path' => '/' . strtolower($this->_addon->getName()) . '/categories',
                    'label' => $this->_addon->getApplication()->_t(_n_noop('Categories', 'Categories', 'sabai-directory'), 'sabai-directory'),
                    'label_singular' => $this->_addon->getApplication()->_t(_n_noop('Category', 'Category', 'sabai-directory'), 'sabai-directory'),
                    'taxonomy_hierarchical' => true,
                    'taxonomy_body' => array(
                        'required' => false,
                        'label' => __('Description', 'sabai-directory'),
                        'widget_settings' => array('rows' => 15),
                        'weight' => 6,
                    ),
                    'taxonomy_permissions' => array(
                        'add' => false,
                        'edit' => false,
                        'delete' => false,
                    ),
                    'properties' => array(
                        'term_parent' => array(
                            'title' => __('Parent Category', 'sabai-directory'),
                            'weight' => 4,
                        ),
                        'term_title' => array(
                            'weight' => 2,
                        ),
                    ),
                    'fields' => array(
                        'directory_thumbnail' => array(
                            'type' => 'file_image',
                            'widget' => 'file_upload',
                            'widget_settings' => array('medium_image' => false, 'large_image' => false),
                            'label' => __('Thumbnail', 'sabai-directory'),
                            'max_num_items' => 1,
                            'weight' => 7,
                        ),
                        'directory_map_marker' => array(
                            'type' => 'file_image',
                            'widget' => 'file_upload',
                            'widget_settings' => array('medium_image' => false, 'large_image' => false),
                            'label' => __('Map Marker', 'sabai-directory'),
                            'max_num_items' => 1,
                            'weight' => 8,
                        ),
                    ),
                    'filterable' => false,
                );
        }
    }
}