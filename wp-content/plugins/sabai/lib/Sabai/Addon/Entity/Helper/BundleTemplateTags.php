<?php
class Sabai_Addon_Entity_Helper_BundleTemplateTags extends Sabai_Helper
{
    public function help(Sabai $application, $bundleName, $prefix = '', array $exclude = array())
    {
        $tags = array();
        //foreach (array('id', 'title', 'author_name', 'author_email', 'url', 'date', 'summary', 'body', 'type', 'custom_fields') as $key) {
        foreach (array('id', 'title', 'author_id', 'author_name', 'author_email', 'url', 'date', 'summary', 'body', 'type') as $key) {
            if (!in_array($key, $exclude)) {
                $tags[] = '{' . $prefix . $key . '}'; 
            }
        }
        return $tags;
    }
}