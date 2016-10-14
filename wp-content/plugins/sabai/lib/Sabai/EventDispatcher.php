<?php
class Sabai_EventDispatcher extends SabaiFramework_EventDispatcher
{
    /**
     * @var Sabai
     */
    private $_application;

    /**
     * Constructor
     * @param Sabai $application
     */
    public function __construct(Sabai $application)
    {
        $this->_application = $application;
    }

    public function dispatch($eventType, array $eventArgs = array())
    {
        try {
            parent::dispatch($eventType, $eventArgs);
        } catch (Exception $e) {
            // Event listeners should never halt the execution of the main script, so catch all possible exceptions and log them
            $this->_application->getHelperBroker()->callHelper('LogError', array($e));
        }
    }
    
    protected function _dispatchEvent($listenerName, SabaiFramework_EventDispatcher_Event $event)
    {
        $this->_application->getAddon($listenerName)->handleEvent($event);
    }
}