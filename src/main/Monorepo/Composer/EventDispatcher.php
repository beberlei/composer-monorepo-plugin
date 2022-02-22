<?php

namespace Monorepo\Composer;

use Composer\EventDispatcher\Event;

class EventDispatcher extends \Composer\EventDispatcher\EventDispatcher
{
    public function dispatch($eventName, Event $event = null): int
    {
        return 0;
    }

    public function dispatchScript($eventName, $devMode = false, $additionalArgs = array(), $flags = array()): int
    {
        return 0;
    }
}
