<?php
class Sabai_Addon_File_Model_FilesWithUser extends Sabai_ModelEntityWithUser
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
    }
}