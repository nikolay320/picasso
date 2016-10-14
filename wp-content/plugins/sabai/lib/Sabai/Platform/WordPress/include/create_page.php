<?php
function sabai_platform_wordpress_create_page(Sabai_Platform_WordPress $platform, $slug, $title, ArrayObject $log = null)
{
    if ($page = get_page_by_path($slug)) {
        wp_publish_post($page->ID);
        return $page->ID;
    }
    if (strpos($slug, '/')) { // not a root page
        if (!$parent_page = get_page_by_path(substr($slug, 0, strrpos($slug, '/')))) {
            // parent page must exist
            return;
        }
        $slug = basename($slug);
        $parent = $parent_page->ID;
    } else {
        $parent = 0;
    }
        
    // Create a new page for this slug
    $page = array(
        'comment_status' => 'closed',
        'ping_status' => 'closed',
        'post_content' => '',
        'post_date' => current_time('mysql'),
        'post_date_gmt' => current_time('mysql', 1),
        'post_name' => $slug,
        'post_status' => 'publish',
        'post_title' => $title,
        'post_type' => 'page',
        'post_parent' => $parent,
    );
    return wp_insert_post($page);
}