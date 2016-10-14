<?php
if (!defined('ABSPATH')) exit;

add_shortcode('sabai-directory', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-map', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-categories', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-listings', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-reviews', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-photos', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-slider', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-photo-slider', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-search-form', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-add-listing-button', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-listing', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-bookmarks', 'sabai_wordpress_directory_shortcode');
add_shortcode('sabai-directory-pricing-table', 'sabai_wordpress_directory_shortcode');

function sabai_wordpress_directory_shortcode($atts, $content, $tag)
{
    $platform = get_sabai_platform();
    switch ($tag) {
        case 'sabai-directory':
            $path = !empty($atts['geolocate']) ? '/sabai/directory/geolocate' : '/sabai/directory';
            if (isset($atts['user_name']) && ($user = get_user_by('login', $atts['user_name']))) {
                $atts['user_id'] = $user->ID;
            }
            if (isset($atts['filter']) && strlen($atts['filter'])) {
                $atts['filters'] = array();
                parse_str(strtr($atts['filter'], array('{' => '%5B', '}' => '%5D', '&#038;' => '&')), $atts['filters']);
                $atts['filter'] = 1;
            }
            break;
        case 'sabai-directory-map':
            $path = '/sabai/directory/map';
            break;
        case 'sabai-directory-categories':
            $path = '/sabai/directory/categories';
            break;
        case 'sabai-directory-listings':
            $path = !empty($atts['geolocate']) ? '/sabai/directory/geolocate' : '/sabai/directory';
            $atts = array(
                'hide_searchbox' => true,
                'hide_nav' => !empty($atts['hide_nav']),
                'hide_pager' => !empty($atts['hide_pager']),
                'hide_nav_views' => true,
                'view' => 'list',
                'list_map_show' => false,
            ) + (array)$atts;
            if (isset($atts['user_name']) && ($user = get_user_by('login', $atts['user_name']))) {
                $atts['user_id'] = $user->ID;
            }
            if (isset($atts['filter']) && strlen($atts['filter'])) {
                $atts['filters'] = array();
                parse_str(strtr($atts['filter'], array('{' => '%5B', '}' => '%5D', '&#038;' => '&')), $atts['filters']);
                $atts['filter'] = 1;
            }
            break;
        case 'sabai-directory-reviews':
            $path = '/sabai/directory/reviews';
            $atts = array(
                'hide_nav' => !empty($atts['hide_nav']),
                'hide_pager' => !empty($atts['hide_pager']),
            ) + (array)$atts;
            if (isset($atts['user_name']) && ($user = get_user_by('login', $atts['user_name']))) {
                $atts['user_id'] = $user->ID;
            }
            break;
        case 'sabai-directory-photos':
            $path = '/sabai/directory/photos';
            $atts = array(
                'hide_nav' => !empty($atts['hide_nav']),
                'hide_pager' => !empty($atts['hide_pager']),
            ) + (array)$atts;
            if (isset($atts['user_name']) && ($user = get_user_by('login', $atts['user_name']))) {
                $atts['user_id'] = $user->ID;
            }
            break;
        case 'sabai-directory-slider':
            $path = '/sabai/directory/slider';
            $atts = array(
                'hide_nav' => !isset($atts['hide_nav']) || !empty($atts['hide_nav']),
                'hide_nav_views' => true,
            ) + (array)$atts;
            if (isset($atts['user_name']) && ($user = get_user_by('login', $atts['user_name']))) {
                $atts['user_id'] = $user->ID;
            }
            break;
        case 'sabai-directory-photo-slider':
            $path = '/sabai/directory/slider';
            $atts = array(
                'hide_nav' => !isset($atts['hide_nav']) || !empty($atts['hide_nav']),
                'hide_nav_views' => true,
                'photo_only' => true,
            ) + (array)$atts;
            if (isset($atts['user_name']) && ($user = get_user_by('login', $atts['user_name']))) {
                $atts['user_id'] = $user->ID;
            }
            break;
        case 'sabai-directory-search-form':
            $path = '/sabai/directory/searchform';
            if (!empty($atts['page'])) {
                unset($atts['action_url']);
                $page = is_numeric($atts['page']) ? get_post($atts['page']) : get_page_by_path($atts['page']);
                if (is_object($page)) {
                    $atts['action_url'] = get_permalink($page);
                }
            }
            break;
        case 'sabai-directory-add-listing-button':
            if (!empty($atts['page'])) {
                $page = is_numeric($atts['page']) ? get_post($atts['page']) : get_page_by_path($atts['page']);
                if (is_object($page)) {
                    $url = get_permalink($page);
                }
            }
            if (!isset($url)) {
                $addon = isset($atts['addon']) ? $atts['addon'] : 'Directory';
                $application = $platform->getSabai();
                $url = $application->Url(
                    '/' . $application->getAddon('Directory')->getSlug('add-listing'),
                    array('bundle' => $application->getAddon($addon)->getListingBundleName())
                );
            }
            return sprintf(
                '<a href="%s" class="sabai-btn %s %s">%s</a>',
                $url,
                isset($atts['size']) ? Sabai::h('sabai-btn-' . $atts['size']) : '',
                isset($atts['type']) ? Sabai::h('sabai-btn-' . $atts['type']) : 'sabai-btn-default',
                isset($atts['label']) ? Sabai::h($atts['label']) : __('Add Listing', 'sabai-directory')
            );
        case 'sabai-directory-listing':
            if (empty($atts['id'])) return;
            $path = '/sabai/directory/listing/' . $atts['id'];
            break;
        case 'sabai-directory-bookmarks':
            $path = '/sabai/directory/bookmarks';
            break;
        case 'sabai-directory-pricing-table':
            $path = '/sabai/paiddirectorylistings/pricing';
            break;
        default:
            return;
    }
    return $platform->shortcode($path, (array)$atts, $content);
}
