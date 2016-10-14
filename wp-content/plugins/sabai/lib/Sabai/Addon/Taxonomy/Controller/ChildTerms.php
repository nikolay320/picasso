<?php
class Sabai_Addon_Taxonomy_Controller_ChildTerms extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        $list = array();
        if (($bundle = $context->getRequest()->asStr('bundle'))
            && ($parent_id = $context->getRequest()->asInt('value'))
        ) {
            $with_count = $context->getRequest()->asBool('count', false);
            $terms = $this->Taxonomy_Terms($bundle);
            if (!empty($terms[$parent_id])) {
                foreach ($terms[$parent_id] as $term) {
                    $list[] = array(
                        $term['id'], 
                        $with_count ? sprintf(__('%s (%d)', 'sabai'), $term['title'], $term['count']) : $term['title']
                    );
                }
            }
        }
        $context->list = $list;
        $context->addTemplate('system_list');
    }
}
