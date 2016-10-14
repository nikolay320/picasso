<?php
class Sabai_Addon_Entity_Helper_RenderTitle extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $options = array())
    {
        // Define icon labels
        if (!isset($options['icons'])) {
            $options['icons'] = array();
        }
        if (!empty($entity->data['entity_icons'])) {
            $options['icons'] += $entity->data['entity_icons'];
        }
        if (empty($options['no_feature']) && $entity->isFeatured()) {
            $options['icons']['featured'] = array(
                'title' => __('This post is featured.', 'sabai'),
                'icon' => 'certificate',
            );
        }
        
        $options = $application->Filter('entity_render_title_options', $options, array($entity));
        
        $ret = array();
        foreach ($options['icons'] as $key => $icon) {
            if (is_array($icon)) {
                $ret[] = '<i class="sabai-entity-icon-' . str_replace('_', '-', $key) . ' fa fa-' . $icon['icon'] . '" title="' . Sabai::h($icon['title']) . '"></i>';
            } else {
                $ret[] = '<i class="sabai-entity-icon-' . str_replace('_', '-', $key) . ' fa fa-' . $icon . '"></i>';
            }
        }
        $title = isset($options['alt']) ? $options['alt'] : $entity->getTitle();
        if (!empty($options['length'])) {
            $title = mb_strimwidth($title, 0, $options['length'], isset($options['trim_marker']) ? $options['trim_marker'] : '...');
        }
        if (empty($options['no_link'])) {
            $title = $application->Entity_Permalink($entity, array('title' => $title, 'atts' => array('title' => $title)));
        } else {
            $title = '<span>' . Sabai::h($title) . '</span>'; // add span so js can target
        }
        $ret[] = isset($options['format']) ? sprintf($options['format'], $title) : $title;     

        return implode(' ', $ret);
    }
}