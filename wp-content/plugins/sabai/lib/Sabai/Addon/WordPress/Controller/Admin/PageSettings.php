<?php
class Sabai_Addon_WordPress_Controller_Admin_PageSettings extends Sabai_Addon_System_Controller_Admin_Settings
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $slugs = $this->System_Slugs();
        if (!$slugs = @$slugs[$context->slug_addon]) return false;
        
        $form = array(
            'pages' => array(),
            'slugs' => array(),
        );
        $wp_pages = array('' => '');
        foreach (get_pages() as $wp_page) {
            $wp_pages[$wp_page->ID] = $wp_page->post_title;
        }
        $page_slugs = $this->getPlatform()->getSabaiOption('page_slugs');
        foreach ($slugs['slugs'] as $slug_name => $slug) {
            if ($slug['is_root'] || !empty($slug['parent'])) {
                if (!isset($form['pages'][$slug['addon']])) {
                    $form['pages'][$slug['addon']] = array('#collapsible' => false);
                }
                $slug_parts = explode('/', $slug['slug']);
                $form['pages'][$slug['addon']][$slug_name] = array(
                    '#title' => $slug['admin_title'],
                    '#collapsible' => false,
                    '#tree' => true,
                    '#class' => 'sabai-form-group',
                    'type' => array(
                        '#type' => 'radios',
                        '#options' => array('existing' => __('Use an existing page', 'sabai'), 'new' => __('Create a new page', 'sabai')),
                        '#class' => 'sabai-form-inline',
                        '#default_value' => 'existing',
                    ),
                    'id' => array(
                        '#type' => 'select',
                        '#options' => empty($slug['parent']) ? $wp_pages : array('' => ''),
                        '#empty_value' => '',
                        '#default_value' => ((null !== $current_slug = @$page_slugs[1][$slug['addon']][$slug_name])) && isset($page_slugs[2][$current_slug]) ? $page_slugs[2][$current_slug] : null,
                        '#states' => array(
                            'visible' => array(sprintf('input[name="pages[%s][%s][type]"]', $slug['addon'], $slug_name) => array('value' => 'existing')),
                        ),
                        '#required' => array(array($this, 'isFieldRequired'), array($slug['addon'], $slug_name, 'existing')),
                    ),
                    'page' => array(
                        '#class' => 'sabai-form-inline',
                        '#states' => array(
                            'visible' => array(sprintf('input[name="pages[%s][%s][type]"]', $slug['addon'], $slug_name) => array('value' => 'new')),
                        ),
                        'title' => array(
                            '#type' => 'textfield',
                            '#size' => 20,
                            '#field_prefix' => __('Title:', 'sabai'),
                            '#default_value' => $slug['title'],
                            '#required' => array(array($this, 'isFieldRequired'), array($slug['addon'], $slug_name, 'new')),
                        ),
                        'slug' => array(
                            '#type' => 'textfield',
                            '#size' => 15,
                            '#field_prefix' => __('Slug:', 'sabai'),
                            '#default_value' => end($slug_parts),
                            '#regex' => '/^[a-z0-9-_]+$/',
                            '#required' => array(array($this, 'isFieldRequired'), array($slug['addon'], $slug_name, 'new')),
                        ),
                    ),
                );
                if (!empty($slug['parent'])) {
                    $current_id = ((null !== $current_slug = @$page_slugs[1][$slug['addon']][$slug_name])) && isset($page_slugs[2][$current_slug]) ? $page_slugs[2][$current_slug] : null;
                    $form['pages'][$slug['addon']][$slug_name]['type']['#options']['none'] = __('No page (use the parent page)', 'sabai');
                    $form['pages'][$slug['addon']][$slug_name]['type']['#default_value'] = !empty($current_id) ? 'existing' : 'none';
                    $form['pages'][$slug['addon']][$slug_name]['id']['#states']['load_options'] = array(sprintf('select[name="pages[%s][%s][id]"]', $slug['addon'], $slug['parent']) => array('type' => 'selected', 'value' => true));
                    $form['pages'][$slug['addon']][$slug_name]['id']['#attributes'] = array('data-load-url' => $this->Url('/wordpress/pages', array(Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&'));
                    if (isset($current_id)) {
                        $form['pages'][$slug['addon']][$slug_name]['id']['#attributes']['data-value'] = $current_id;
                    }
                    $form['pages'][$slug['addon']][$slug_name]['id']['#skip_validate_option'] = true;
                    $slug_parts = explode('/', isset($current_slug) ? $current_slug : $slug['slug']);
                    $form['pages'][$slug['addon']][$slug_name]['slug'] = array(
                        '#type' => 'textfield',
                        '#size' => 15,
                        '#field_prefix' => __('Slug:', 'sabai'),
                        '#default_value' => end($slug_parts),
                        '#regex' => '/^[a-z0-9-_]+$/',
                        '#required' => array(array($this, 'isFieldRequired'), array($slug['addon'], $slug_name, 'none')),
                        '#states' => array(
                            'visible' => array(sprintf('input[name="pages[%s][%s][type]"]', $slug['addon'], $slug_name) => array('value' => 'none')),
                        ),
                    );
                    $form['pages'][$slug['addon']][$slug_name]['parent'] = array(
                        '#type' => 'hidden',
                        '#value' => $slug['parent'],
                    );
                    if (isset($slug['parent_addon'])) {
                        $form['pages'][$slug['addon']][$slug_name]['parent_addon'] = array(
                            '#type' => 'hidden',
                            '#value' => $slug['parent_addon'],
                        );
                    }
                }
            } else {
                if (!isset($form['slugs'][$slug['addon']])) {
                    $form['slugs'][$slug['addon']] = array('#collapsible' => false, '#tree' => true);
                }
                $form['slugs'][$slug['addon']][$slug_name] = array('#title' => $slug['admin_title'], '#collapsible' => true, '#tree' => true) + (array)@$slug['settings'];
                $form['slugs'][$slug['addon']][$slug_name] += array(
                    '#type' => 'textfield',
                    '#size' => 15,
                    '#regex' => '/^[a-z0-9-_]+$/',
                    '#required' => true,
                    '#default_value' => (null !== $current_slug = @$page_slugs[1][$slug['addon']][$slug_name]) ? $current_slug : $slug['slug'],
                );
            }
            $form['#addons'][$slug['addon']] = $slug['addon'];
        }
        
        if (!empty($form['pages'])) {
            $form['pages'] += array(
                '#title' => __('Page Settings', 'sabai'),
                '#tree' => true,
                '#weight' => 1,
            );
        } else {
            unset($form['pages']);
        }
        if (!empty($form['slugs'])) {
            $form['slugs'] += array(
                '#title' => __('Slug Settings', 'sabai'),
                '#tree' => true,
                '#weight' => 2,
            );
        } else {
            unset($form['slugs']);
        }
        
        return $form;
    }

    public function isFieldRequired($form, $addon, $slug, $type)
    {
        return $form->values['pages'][$addon][$slug]['type'] === $type;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $slugs = $this->getPlatform()->getSabaiOption('page_slugs', array());
        foreach ($form->settings['#addons'] as $addon_name) {
            $old_slugs = $slugs[1][$addon_name];
            $slugs[1][$addon_name] = array();
            if (!empty($form->values['pages'][$addon_name])) {
                foreach ($form->values['pages'][$addon_name] as $name => $page) {
                    $parent_slug = null;
                    if (!empty($page['parent'])) {
                        $parent_addon = empty($page['parent_addon']) ? $addon_name : $page['parent_addon'];
                        if (!$parent_slug = @$slugs[1][$parent_addon][$page['parent']]) {
                            continue;
                        }
                    }
                    if ($page['type'] === 'none'
                        || ($page['type'] === 'id' && (empty($page['id']) || (!$post = get_page($page['id']))))
                    ) {
                        // No page, save slug only
                        if ($current_slug = @$old_slugs[$name]) {
                            unset($slugs[0][$current_slug], $slugs[2][$current_slug]);
                        }
                        $slugs[1][$addon_name][$name] = isset($parent_slug) ? rtrim($parent_slug . '/' . $page['slug'], '/') : $page['slug'];
                        continue;
                    }
                    if ($page['type'] === 'new') {
                        // Create a new page
                        $slug = isset($parent_slug) ? $parent_slug . '/' . $page['page']['slug'] : $page['page']['slug'];
                        if (!$page_id = $this->getPlatform()->createPage($slug, $page['page']['title'])) {
                            $form->setError('Could not create page', 'pages['. $addon_name .'][' . $name . '][slug]');
                            continue;
                        }
                        
                        $page['id'] = $page_id;
                    }
                    if (!$post = get_page($page['id'])) continue;
            
                    $slug = trim(str_replace(home_url(), '', get_permalink($post->ID)), '/');
                    
                    // Set the name as slug if the selected page is the front page
                    if ($slug === '') {
                        if (!empty($page['parent'])) continue; // must be a top level page
                        
                        $slug = $name;
                    }
                    
                    $slugs[0][$slug] = $slug;
                    $slugs[1][$addon_name][$name] = $slug;
                    $slugs[2][$slug] = $page['id'];
                }
            }
            if (!empty($form->values['slugs'][$addon_name])) {
                foreach ($form->values['slugs'][$addon_name] as $name => $slug) {
                    $slugs[1][$addon_name][$name] = $slug;
                }
            }
        }
        
        // Clear slugs that do not exist or no londer a sabai page slug
        $valid_slugs = array();
        foreach ($slugs[1] as $addon_name => $_slugs) {
            foreach ($_slugs as $name => $slug) {
                $valid_slugs[$slug] = $slug;
            }
        }
        $slugs[0] = array_intersect_key($slugs[0], $valid_slugs);
        $slugs[2] = array_intersect_key($slugs[2], $valid_slugs);
        
        $this->getPlatform()->updateSabaiOption('page_slugs', $slugs);
        
        // Run upgrade process to refresh all slug data
        $this->UpgradeAddons($form->settings['#addons']);
    }
    
    protected function _getSuccessUrl(Sabai_Context $context)
    {
        return $this->Url($context->getRoute());
    }
}