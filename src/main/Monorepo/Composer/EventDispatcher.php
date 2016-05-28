<?php

namespace Monorepo\Composer;

use Composer\EventDispatcher\Event;

class EventDispatcher extends \Composer\EventDispatcher\EventDispatcher
{
    public function dispatch($eventName, Event $event = null)
    {
    }

    public function dispatchScript($eventName, $devMode = false, $additionalArgs = array(), $flags = array())
    {
    }
}
