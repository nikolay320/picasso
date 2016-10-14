<?php
interface Sabai_Addon_Social_IMedias
{
    public function socialGetMediaNames();
    public function socialMediaGetInfo($name);
    public function socialMediaGetShareUrl($name, Sabai_Addon_Entity_IEntity $entity);
}