<?php
class Sabai_Addon_Questions_Helper_TagLink extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Taxonomy_Entity $entity)
    {
        return sprintf(
            '<span>%s<span> &#215; </span><span class="sabai-number">%d</span></span>',
            $application->Entity_Permalink($entity, array('label' => true)),
            @$entity->getSingleFieldValue('taxonomy_content_count', 'questions')
        );
    }
}