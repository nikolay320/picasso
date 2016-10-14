<?php
class Sabai_Helper_Path extends Sabai_Helper
{
    public function help(Sabai $application, $path)
    {
        if (strpos($path, '\\') === false) {
            // Not a windows path
            // Make sure to start with single slash, some servers return // for some reason 
            return '/' . ltrim($path, '/');
        }
        $path = str_replace('\\', '/', $path);
        if (0 !== $first_slash_pos = strpos($path, '/')) {
            $path = substr($path, $first_slash_pos);  // remove c: part
        }
        return $path;
    }
}