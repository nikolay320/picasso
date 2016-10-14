<?php
class Sabai_Helper_ButtonLinks extends Sabai_Helper
{
    public function help(Sabai $application, array $links = null, $options = array(), $showTooltip = true, $showLabel = false)
    {
        if (empty($links)) return '';
        
        if (!is_array($options)) {
            // Backward compat for 1.2.x
            $options = array(
                'size' => $options,
                'tooltip' => $showTooltip,
                'label' => $showLabel,
                'separator' => PHP_EOL,
                'right' => false,
            );
        } else {
            $options += array(
                'size' => 'sm',
                'tooltip' => true,
                'label' => false,
                'separator' => PHP_EOL,
                'right' => false,
            );
        }
        foreach (array_keys($links) as $i) {
            $link = $links[$i];
            // Show dropdowns if multiple links
            if (is_array($link)) {
                $links[$i] = $application->DropdownButtonLinks($link, $options['size'], null, $options['tooltip'], $options['label'], $options['right']);
                if (count($links) === 1) return $links[$i];
                continue;
            } elseif (is_string($link)) {
                continue;
            }
            // Single link
            $class = 'sabai-btn sabai-btn-default sabai-btn-' . $options['size'];
            if ($_class = $link->getAttribute('class')) {
                $class .= ' ' . $_class;
            }
            $link->setAttribute('class', $class);
            if ($options['tooltip']) {
                $link->setAttribute('rel', 'sabaitooltip');
                // Use label as tooltip if no title is set
                if (!$options['label'] && !$link->getAttribute('title')) {
                    $link->setAttribute('title', strip_tags($link->getLabel()));
                }
            }
            if (!$options['label']) {
                $link->setLabel('');
            }
        }
        return '<div class="sabai-btn-group">' . implode($options['separator'], $links) . '</div>';
    }
}
