<?php
$ret = array();
if ($pages = get_pages(array('parent' => $parent))) {
    foreach ($pages as $page) {
        $ret[] = array($page->ID, Sabai::h($page->post_title));
    }
}
echo json_encode($ret);