<?php
class Sabai_Addon_File_Model_TokensWithFiles extends SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct('file_token_id', 'File', $collection, 'Files');
    }
}