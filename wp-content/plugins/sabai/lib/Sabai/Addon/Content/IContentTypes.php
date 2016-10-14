<?php
interface Sabai_Addon_Content_IContentTypes
{
    public function contentGetContentTypeNames();
    public function contentGetContentType($name);
}