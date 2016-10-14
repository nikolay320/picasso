<?php
class Sabai_Addon_File_Model_FilesWithToken extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('token_id', 'Token', $collection);
    }
}