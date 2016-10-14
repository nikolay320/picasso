<?php
class Sabai_Template
{
    private $_helperBroker, $_vars, $_dirs, $_paths = array();

    public function __construct(Sabai $application, array $dirs = array(), array $vars = array())
    {
        $this->_helperBroker = $application->getHelperBroker();
        $this->_dirs = $dirs;
        $this->_vars = $vars;
    }
    
    public function addDir($dir)
    {
        array_unshift($this->_dirs, $dir);
    }
    
    public function getDirs()
    {
        return $this->_dirs;
    }
    
    public function setVar($name, $value)
    {
        $this->_vars[$name] = $value;
    }
    
    public function getVars()
    {
        return $this->_vars;
    }

    public function __call($name, $args)
    {
        return $this->_helperBroker->callHelper($name, $args);
    }
    
    public function displayTemplate($templateName, array $vars = array(), $extension = '.html')
    {
        foreach ((array)$templateName as $template_name) {
            if ($template_path = $this->_isValidTemplate($template_name, $extension)) {
                $this->_include($template_path, $this->_vars + $vars);
                return;
            }
        }  
        throw new Sabai_RuntimeException(sprintf('No valid template file was found: %s', implode(', ', (array)$templateName)));
    }

    public function renderTemplate($templateName, array $vars = array(), $extension = '.html')
    {
        ob_start();
        try {
            $this->displayTemplate($templateName, $vars, $extension);
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
        return ob_get_clean();
    }
    
    protected function _isValidTemplate($templateName, $extension)
    {
        // Resolve template path if not yet cached
        if (isset($this->_paths[$templateName])) {
            return $this->_paths[$templateName];
        }
        // Template name is a full template path?
        if (strpos($templateName, '/') !== false) {
            $template_path = $templateName . $extension . '.php';
            if (file_exists($template_path)) {
                $this->_paths[$templateName] = $template_path;
                return $this->_paths[$templateName];
            }
            $templateName = basename($templateName);
        }
        // Search all template directories
        $template_file = $templateName . $extension . '.php';
        foreach ($this->_dirs as $template_dir) {
            $template_path = $template_dir . '/' . $template_file;
            if (file_exists($template_path)) {
                $this->_paths[$templateName] = $template_path;
                return $template_path;
            }
        }
        // Template not found
        $this->_paths[$templateName] = false;
        return false;
    }

    private function _include()
    {
        extract(func_get_arg(1), EXTR_SKIP);
        include func_get_arg(0);
    }
}