<?php
class Sabai_Addon_System_Controller_IconList extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        $context->addTemplate('system_list');
        $list = array();
        foreach ($query->fetch() as $term) {
            $list[] = array(
                'name' => $term->getSlug(),
            );
        }
        $context->list = $list;
    }
}