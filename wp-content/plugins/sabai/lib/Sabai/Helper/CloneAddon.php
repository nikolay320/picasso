<?php
class Sabai_Helper_CloneAddon extends Sabai_Helper
{
    public function help(Sabai $application, $addonName, $cloneName)
    {
        $clone_dir = $application->getClonesDir();
        $application->ValidateDirectory($clone_dir, true);        
        $clone_name = ucfirst($cloneName);
        $clone_content = sprintf('<?php
if (!class_exists(\'Sabai_Addon_%2$s\', false)) {
    if (!class_exists(\'Sabai_Addon_%1$s\', false)) {
        if (!include $this->getAddonPath(\'%1$s\', false) . \'.php\') {
            return;
        }
    }
    class Sabai_Addon_%2$s extends Sabai_Addon_%1$s {}
}', $addonName, $clone_name);
        if (false === file_put_contents($clone_dir . '/' . $clone_name . '.php', $clone_content)) {
            return false;
        }
        return true;
    }
}