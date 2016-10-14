<?php
class Sabai_Addon_Taxonomy_Helper_Term extends Sabai_Helper
{
    public function help(Sabai $application, $termId)
    {
        return $application->Entity_Entity('taxonomy', $termId);
    }
}