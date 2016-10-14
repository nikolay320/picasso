<?php
if (!defined('ABSPATH')) exit;

add_shortcode('sabai-discuss', 'sabai_wordpress_discuss_shortcode');
add_shortcode('sabai-discuss-questions', 'sabai_wordpress_discuss_shortcode');
add_shortcode('sabai-discuss-answers', 'sabai_wordpress_discuss_shortcode');
add_shortcode('sabai-discuss-favorites', 'sabai_wordpress_discuss_shortcode');
add_shortcode('sabai-discuss-search-form', 'sabai_wordpress_discuss_shortcode');

function sabai_wordpress_discuss_shortcode($atts, $content, $tag)
{
    switch ($tag) {
        case 'sabai-discuss':
            $path = '/sabai/questions';
            if (isset($atts['filter']) && strlen($atts['filter'])) {
                $atts['filters'] = array();
                parse_str(strtr($atts['filter'], array('{' => '%5B', '}' => '%5D', '&#038;' => '&')), $atts['filters']);
                $atts['filter'] = 1;
            }
            break;
        case 'sabai-discuss-questions':
            $path = '/sabai/questions';
            $atts = array(
                'hide_searchbox' => true,
                'hide_nav' => !empty($atts['hide_nav']),
                'hide_pager' => !empty($atts['hide_pager']),
            ) + (array)$atts;
            if (isset($atts['filter']) && strlen($atts['filter'])) {
                $atts['filters'] = array();
                parse_str(strtr($atts['filter'], array('{' => '%5B', '}' => '%5D', '&#038;' => '&')), $atts['filters']);
                $atts['filter'] = 1;
            }
            break;
        case 'sabai-discuss-answers':
            $path = '/sabai/questions/answers';
            $atts = array(
                'hide_nav' => !empty($atts['hide_nav']),
                'hide_pager' => !empty($atts['hide_pager']),
            ) + (array)$atts;
            break;
        case 'sabai-discuss-favorites':
            $path = '/sabai/questions/favorites';
            break;
        case 'sabai-discuss-search-form':
            $path = '/sabai/questions/searchform';
            if (!empty($atts['page'])) {
                unset($atts['action_url']);
                $page = is_numeric($atts['page']) ? get_post($atts['page']) : get_page_by_path($atts['page']);
                if (is_object($page)) {
                    $atts['action_url'] = get_permalink($page);
                }
            }
            break;
        default:
            return;
    }
    if (isset($atts['user_name']) && ($user = get_user_by('login', $atts['user_name']))) {
        $atts['user_id'] = $user->ID;
    }
    return get_sabai_platform()->shortcode($path, (array)$atts, $content);
}
