<?php
class Sabai_Helper_PageNav extends Sabai_Helper
{
    public static $offset = 2;

    public function help(Sabai $application, $update, SabaiFramework_Paginator $pages, $linkUrl, array $options = array(), $offset = null)
    {
        if (1 >= $page_count = $pages->count()) return '';

        $current_page = $pages->getCurrentPage();
        $current_html = sprintf('<a class="sabai-btn sabai-btn-default sabai-btn-sm sabai-active">%d</a>', $current_page);
        $html = array();
        if (!isset($offset)) $offset = self::$offset;
        $link_url = $application->Url($linkUrl); // convert to SabaiFramework_Application_Url
        $ajax_url = isset($options['url']) ? $application->Url($options['url']) : clone $link_url; // convert to SabaiFramework_Application_Url
        $min = max(1, $current_page - $offset);
        $max = $current_page + $offset;
        if ($max > $page_count) $max = $page_count;
        if ($current_page != 1) {
            $html[] = $this->_getPageLink($application, '&laquo;', $current_page - 1, $update, $link_url, $ajax_url, $options);
        } else {
            $html[] = '<a class="sabai-btn sabai-btn-default sabai-btn-sm sabai-disabled">&laquo;</a>';
        }
        if ($min > 1) {
            $html[] = $this->_getPageLink($application, 1, 1, $update, $link_url, $ajax_url, $options);
            if ($min > 2) $html[] = '<a class="sabai-btn sabai-btn-default sabai-btn-sm sabai-disabled">...</a>';
        }
        for ($i = $min; $i <= $max; $i++) {
            $html[] = ($i == $current_page) ? $current_html : $this->_getPageLink($application, $i, $i, $update, $link_url, $ajax_url, $options);
        }
        if ($max < $page_count) {
            if ($page_count - $max > 1) $html[] = '<a class="sabai-btn sabai-btn-default sabai-btn-sm sabai-disabled">...</a>';
            $html[] = $this->_getPageLink($application, $page_count, $page_count, $update, $link_url, $ajax_url, $options);
        }
        if ($current_page != $page_count) {
            $html[] = $this->_getPageLink($application, '&raquo;', $current_page + 1, $update, $link_url, $ajax_url, $options);
        } else {
            $html[] = '<a class="sabai-btn sabai-btn-default sabai-btn-sm sabai-disabled">&raquo;</a>';
        }

        return sprintf('<div class="sabai-pagination sabai-btn-group">%s</div>', implode('', $html));
    }

    private function _getPageLink(Sabai $application, $text, $page, $update, $linkUrl, $ajaxUrl, array $options = array())
    {
        $linkUrl['params'] = array(Sabai::$p => $page) + $linkUrl['params'];
        $ajaxUrl['params'] = array(Sabai::$p => $page) + $ajaxUrl['params'];
        $options['url'] = $ajaxUrl;
        $options += array('scroll' => $update, 'pushState' => true, 'no_escape' => true);
        $class = 'sabai-btn sabai-btn-default sabai-btn-sm';

        return $application->LinkToRemote($text, $update, $linkUrl, $options, array('class' => $class));
    }
}