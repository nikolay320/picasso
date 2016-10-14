<?php
class Sabai_Helper_FileType extends Sabai_Helper
{
    public function help(Sabai $application, $file, $isImage = false)
    {
        if ($isImage) {
            if ($size = @getimagesize($file)) {
                return $size['mime'];
            }
        }
        if (function_exists('finfo_file')) {
            if (($finfo = @finfo_open(FILEINFO_MIME))
                && ($mime = finfo_file($finfo, $file))
            ) {
                return $mime;
            }
            @finfo_close($finfo);
        }
        if (!function_exists('mime_content_type')) {
            throw new Sabai_RuntimeException('Could not find finfo_file or mime_content_type function');
        }
        
        return mime_content_type($file);
    }
}