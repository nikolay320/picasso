<?php
class Sabai_Helper_SplitDropdownButtonLinks extends Sabai_Helper
{
    public function help(Sabai $application, array $links = null, $size = 'sm', $labelFormat = null, $showTooltip = false)
    {
        if (empty($links)) return '';
        
        $dropdown_links = array();
        foreach ($links as $key => $link) {
            $link->setAttribute('title', '');
            if (!$link->isActive()) {
                $dropdown_links[$key] = $link;
                continue;
            }
            $current = $this->_markCurrent($link, $size, $labelFormat, $showTooltip);
        }
        if (!isset($current)) {
            $current = $this->_markCurrent(array_shift($links), $size, $labelFormat, $showTooltip);
            array_shift($dropdown_links);
        }
        if (!count($dropdown_links)) {
            return (string)$current;
        }
        $dropdown_toggle_class = 'sabai-btn sabai-btn-default sabai-dropdown-toggle sabai-dropdown-link';
        if ($size) {
            foreach ((array)$size as $_size) {
                $dropdown_toggle_class .= ' sabai-btn-' . $_size;
            }
        }
        $dropdown_toggle = '<a class="' . $dropdown_toggle_class .'" data-toggle="dropdown"><span class="sabai-caret"></span></a>';
        return '<div class="sabai-btn-group">' . $current . $dropdown_toggle . '<ul class="sabai-dropdown-menu"><li>' . implode('</li><li>', $dropdown_links) . '</li></ul></div>';
    }
    
    private function _markCurrent($link, $size, $labelFormat, $showTooltip)
    {
        $class = 'sabai-btn sabai-btn-default';
        if ($size) {
            foreach ((array)$size as $_size) {
                $class .= ' sabai-btn-' . $_size;
            }
        }
        if ($_class = $link->getAttribute('class')) {
            $class .= ' ' . $_class;
        }
        if ($showTooltip) {
            $link->setAttribute('rel', 'sabaitooltip');
        }
        if (isset($labelFormat)) {
            $label = $link->isNoEscape() ? $link->getLabel() : Sabai::h($link->getLabel());
            $link->setLabel(sprintf($labelFormat, $label), false);
        }
        return $link->setActive(false)->setAttribute('class', $class);
    }
}