<?php
class Sabai_Addon_Entity_Helper_RenderLabels extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $options = array())
    {
        if (!isset($options['labels'])) {
            $options['labels'] = array();
        }
        if (!empty($entity->data['entity_labels'])) {
            $options['labels'] += $entity->data['entity_labels'];
        }
        if (empty($options['no_feature']) && $entity->isFeatured()) {
            $options['labels']['featured'] = array(
                'label' => __('Featured', 'sabai'),
                'title' => __('This post is featured.', 'sabai'),
                'icon' => 'certificate',
            );
        }
        
        $options = $application->Filter('entity_render_label_options', $options, array($entity));
        
        $ret = array();
        foreach ($options['labels'] as $key => $label) {
            $class = 'sabai-label sabai-label-default sabai-entity-label-' . str_replace('_', '-', $key);
            if (is_array($label)) {
                $ret[] = '<span class="' . $class . '" title="'. Sabai::h(@$label['title']) .'"><i class="fa fa-' . $label['icon'] . '"></i> ' . Sabai::h($label['label']) . '</span>';
            } else {
                $ret[] = '<span class="' . $class . '">' . Sabai::h($label) . '</span>';
            }
        }
        
        return implode(' ', $ret);
    }
}