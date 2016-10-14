<?php
class Sabai_Helper_ButtonToolbar extends Sabai_Helper
{
    public function help(Sabai $application, array $links, array $options = array())
    {
        $options += array(
            'size' => 'sm',
            'tooltip' => false,
            'label' => true,
            'separator' => PHP_EOL,
            'suffix' => array(),
        );
        foreach (array_keys($links) as $i) {
            if (is_string($links[$i])) continue;
            
            $links[$i] = $application->ButtonLinks(array($links[$i]), array('separator' => PHP_EOL) + $options);
        }
		return '<div class="sabai-btn-toolbar">' . implode($options['separator'], $links) . '</div>' . PHP_EOL . implode(PHP_EOL, $options['suffix']);
    }
}