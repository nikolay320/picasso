<?php
abstract class SabaiFramework_EventDispatcher
{
    protected $_listeners = array();

    public function addListener($eventType, $listenerName, $priority = 10)
    {
        $this->_listeners[$eventType][$priority][] = $listenerName;
    }
    
    public function dispatch($eventType, array $eventArgs = array())
    {
        if (empty($this->_listeners[$eventType])) return;

        ksort($this->_listeners[$eventType]);
        $event = new SabaiFramework_EventDispatcher_Event($eventType, $eventArgs);
        foreach ($this->_listeners[$eventType] as $listeners) {
            foreach ($listeners as $listener_name) {
                $this->_dispatchEvent($listener_name, $event);
            }
        }
    }

    public function clear()
    {
        $this->_listeners = array();
    }

    abstract protected function _dispatchEvent($listenerName, SabaiFramework_EventDispatcher_Event $event);
}