<?php
class Sabai_Addon_File_Helper_Icon extends Sabai_Helper
{    
    public function help(Sabai $application, $extension)
    {
        switch ($extension) {
            case 'gif':
            case 'jpg':
            case 'jpeg':
            case 'png':
                return 'fa-file-image-o';
            case 'xls':
                return 'fa-file-excel-o';
            case 'php':
            case 'js':
            case 'html':
            case 'htm':
            case 'xml':
                return 'fa-file-code-o';
            case 'zip':
            case 'tgz':
                return 'fa-file-archive-o';
            case 'txt':
                return 'fa-file-text-o';
            case 'wmv':
            case 'mpg':
            case 'mpeg':
                return 'fa-file-video-o';
            case 'mp3':
                return 'fa-file-audio-o';
            case 'pdf':
                return 'fa-file-pdf-o';
            case 'doc':
                return 'fa-file-word-o';
            case 'ppt':
                return 'fa-file-powerpoint-o';
            default:
                return 'fa-file-o';
        }
    }
}