<?php
class Sabai_Addon_Entity_Helper_Permalink extends Sabai_Helper
{
    public function help(Sabai $application, $entity, array $options = array(), $fragment = '')
    {
        if ($entity instanceof Sabai_Addon_Entity_Entity) {
            $entity = array(
                'id' => $entity->getId(),
                'type' => $entity->getType(),
                'bundle_name' => $entity->getBundleName(),
                'bundle_type' => $entity->getBundleType(),
                'title' => $entity->getTitle(),
                'url' => $application->Entity_Url($entity, '', array(), $fragment),
                'thumbnail' => isset($options['thumbnail']) && ($thumbnail = $entity->getSingleFieldValue($options['thumbnail'], 'name')) ? $thumbnail : null,
            );
        } else {
            if (isset($options['thumbnail']) 
                && isset($entity['fields'][$options['thumbnail']][0]['name'])
            ) {
                $entity['thumbnail'] = $entity['fields'][$options['thumbnail']][0]['name'];
            }
        }
        if (!strlen($entity['title'])) {
            $entity['title'] = __('Untitled', 'sabai');
        }
        $atts = isset($options['atts']) ? $options['atts'] : array();
        if (!isset($atts['class'])) $atts['class'] = '';
        $atts['class'] .= str_replace('_', '-', ' sabai-entity-permalink sabai-entity-id-' . $entity['id'] . ' sabai-entity-type-' . $entity['type'] . ' sabai-entity-bundle-name-' . $entity['bundle_name'] . ' sabai-entity-bundle-type-' . $entity['bundle_type']);
        if (isset($entity['thumbnail'])) {
            if (isset($options['title'])) {
                $title = empty($options['no_escape']) ? Sabai::h($options['title']) : $options['title'];
            } else {
                $title = Sabai::h($entity['title']);
            }
            $options['no_escape'] = true;
            if (!isset($options['thumbnail_size'])) {
                $options['thumbnail_size'] = 32;
            }
            $options['title'] = sprintf('<img src="%1$s" alt="" width="%2$d" height="%2$d" />%3$s', $application->File_ThumbnailUrl($entity['thumbnail']), $options['thumbnail_size'], $title);
            $atts['class'] .= ' sabai-entity-permalink-with-thumbnail';
        }
        return $application->LinkTo(
            isset($options['title']) ? $options['title'] : $entity['title'],
            $entity['url'],
            $options,
            $atts
        );
    }
}