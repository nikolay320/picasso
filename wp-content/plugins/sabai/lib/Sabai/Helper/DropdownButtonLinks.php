<?php
class Sabai_Helper_DropdownButtonLinks extends Sabai_Helper
{
    public function help(Sabai $application, array $links = null, $size = 'sm', $labelFormat = null, $showTooltip = false, $showLabel = true, $right = false)
    {
        if (empty($links)) return '';
        
        $dropdown_links = array();
        $dropdown_link_class = 'sabai-dropdown-link';
        foreach ($links as $key => $link) {
            $link->setAttribute('title', '');
            if (!$link->isActive()) {
                $dropdown_links[$key] = $link;
                $dropdown_links[$key]->setAttribute('class', ($class = $link->getAttribute('class')) ? $dropdown_link_class . ' ' . $class : $dropdown_link_class);
                continue;
            }
            $current = $this->_markCurrent($link, $size, $labelFormat, $showTooltip, $showLabel);
        }
        if (!isset($current)) {
            $current = $this->_markCurrent(array_shift($links), $size, $labelFormat, $showTooltip, $showLabel);
            array_shift($dropdown_links);
        }
        $class = $right ? ' sabai-pull-right' : '';
        return count($dropdown_links)
            ? '<div class="sabai-btn-group">' . $current . '<ul class="sabai-dropdown-menu' . $class . '"><li>' . implode('</li><li>', $dropdown_links) . '</li></ul></div>'
            : (string)$current;
    }
    
    private function _markCurrent($link, $size, $labelFormat, $showTooltip, $showLabel)
    {
        $dropdown_toggle_class = 'sabai-btn sabai-btn-default sabai-dropdown-toggle';
        if ($size) {
            foreach ((array)$size as $_size) {
                $dropdown_toggle_class .= ' sabai-btn-' . $_size;
            }
        }
        if ($class = $link->getAttribute('class')) {
            $dropdown_toggle_class .= ' ' . $class;
        }
        if ($showTooltip) {
            $link->setAttribute('rel', 'sabaitooltip');
            // Use label as tooltip if no title is set
            if (!$showLabel && !$link->getAttribute('title')) {
                $link->setAttribute('title', strip_tags($link->getLabel()));
            }
        }
        if (!$showLabel) {
            $link->setLabel('<span class="sabai-caret"></span>', false);
        } else {
            $label = $link->isNoEscape() ? $link->getLabel() : Sabai::h($link->getLabel());
            $link->setLabel(sprintf(isset($labelFormat) ? $labelFormat : '%s', $label) . ' <span class="sabai-caret"></span>', false);
        }
        return $link->setActive(false)
            ->setAttribute('onclick', '')
            ->setAttribute('class', $dropdown_toggle_class)
            ->setAttribute('data-toggle', 'dropdown');
    }
}
