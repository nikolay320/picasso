<?php
class Sabai_Addon_Entity_Helper_TemplateTags extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, $prefix = '', $bodyLength = 100)
    {
        $author = $application->Entity_Author($entity);
        $tags = array(
            '{' . $prefix . 'id}' => $entity->getId(),
            '{' . $prefix . 'title}' => $entity->getTitle(),
            '{' . $prefix . 'author_id}' => $author->id,
            '{' . $prefix . 'author_name}' => $author->name,
            '{' . $prefix . 'author_email}' => $author->email,
            '{' . $prefix . 'url}' => $application->Entity_Url($entity, '', array(), '', '&'),
            '{' . $prefix . 'date}' => $application->Date($entity->getTimestamp()),
            '{' . $prefix . 'summary}' => $application->Summarize($entity->getContent(), $bodyLength),
            '{' . $prefix . 'body}' => $application->Summarize($entity->getContent()),
            '{' . $prefix . 'type}' => $application->Entity_BundleLabel($application->Entity_Bundle($entity), true),
        );
        //$custom_field_outputs = array();
        //foreach ($application->Entity_CustomFields($entity) as $field) {
        //    $output = $application->Entity_RenderField($entity, $field, 'plain');
        //    if (strlen($output)) {
        //        $custom_field_outputs[] = $field->getFieldTitle('plain');
        //        $custom_field_outputs[] = '----------';
        //        $custom_field_outputs[] = $output;
        //    }
        //}
        //$tags['{' . $prefix . 'custom_fields}'] = implode("\r\n", $custom_field_outputs);
        
        return $tags;
    }
}