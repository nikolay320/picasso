<?php
interface SabaiFramework_EventDispatcher_EventListener
{
    public function handleEvent(SabaiFramework_EventDispatcher_Event $event);
}