<?php
class SabaiFramework_Application_HelperBroker
{
    protected $_application, $_helpers = array(), $_helperDir = array();

    public function __construct(SabaiFramework_Application $application)
    {
        $this->_application = $application;
    }

    public function addHelperDir($dir, $prefix)
    {
        $this->_helperDir = array($dir => $prefix) + $this->_helperDir;

        return $this;
    }
    
    public function callHelper($name, array $args = array())
    {
        array_unshift($args, $this->_application);
        $callback = $this->getHelper($name);
        // Append additional args if any
        if (is_array($callback) && is_array($callback[1])) {
            $args = empty($args) ? $callback[1] : array_merge($args, $callback[1]);
            $callback = $callback[0];
        }
        return call_user_func_array($callback, $args);
    }

    public function getHelper($name)
    {
        if (!isset($this->_helpers[$name])) {
            if (!$this->helperExists($name)) {
                throw new SabaiFramework_Exception(sprintf('Call to undefined application helper %s.', $name));
            }
        }

        return $this->_helpers[$name];
    }

    public function helperExists($name)
    {
        foreach ($this->_helperDir as $helper_dir => $helper_prefix) {
            $class = $helper_prefix . $name;
            if (!class_exists($class, false)) {
                if (!@include sprintf('%s/%s.php', $helper_dir, $name)) {
                    continue;
                }
            }
            $this->setHelper($name, array(new $class(), 'help'));
            return true;
        }
        return false;
    }

    /**
     * Set an application helper
     * @param $name string
     * @param $helper Callable method or function
     */
    public function setHelper($name, $helper)
    {
        $this->_helpers[$name] = $helper;

        return $this;
    }

    public function hasHelper($name)
    {
        return isset($this->_helpers[$name]);
    }
}