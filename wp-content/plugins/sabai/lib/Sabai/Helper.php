<?php
abstract class Sabai_Helper
{
    public function __call($name, $arguments)
    {
        if ($name === 'help') {
            throw new BadMethodCallException(sprintf('%s::help(Sabai $application) must be implemented', get_class($this)));
        }
        throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_class($this), $name));
    }
    
    public function reset(Sabai $application){}
}