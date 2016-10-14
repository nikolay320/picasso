<?php
interface Sabai_Addon_Content_IContentType
{
    public function contentTypeGetInfo();
    public function contentTypeIsPostTrashable(Sabai_Addon_Content_Entity $entity, SabaiFramework_User $user);
    public function contentTypeIsPostRoutable(Sabai_Addon_Content_Entity $entity, SabaiFramework_User $user);
}