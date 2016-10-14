<?php
class Sabai_Helper_ValidateDirectory extends Sabai_Helper
{
    public function help(Sabai $application, $dir, $ensureWriteable = false, $writeableMode = 0755, $recursive = true)
    {
        $dir = $application->Path($dir);
        if (!is_dir($dir) && !@mkdir($dir, $writeableMode, $recursive)) {
            throw new Sabai_RuntimeException(sprintf(__('Directory %s does not exist.', 'sabai'), $dir));
        }
        if ($ensureWriteable && !is_writeable($dir) && !@chmod($dir, $writeableMode)) {
            throw new Sabai_RuntimeException(sprintf(__('Directory %s is not writeable by the server.', 'sabai'), $dir));
        }
        return $dir;
    }
}