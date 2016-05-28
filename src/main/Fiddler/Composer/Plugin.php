<?php

namespace Fiddler\Composer;

use Fiddler\Build;
use Fiddler\Command;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Plugin\CommandsProviderInterface;

class Plugin implements PluginInterface, EventSubscriberInterface, CommandsProviderInterface
{
    /**
     * @var \Fiddler\Build
     */
    private $build;

    public function __construct(Build $build = null)
    {
        $this->build = $build;
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->build = $this->build ?: new Build($io);
    }

    public static function getSubscribedEvents()
    {
        return [
            'post-autoload-dump' => 'generateMonorepoAutoloads',
        ];
    }

    /**
     * Delegate autoload dump to all the fiddler monorepo subdirectories.
     */
    public function generateMonorepoAutoloads(Event $event)
    {
        $flags = $event->getFlags();
        $optimize = isset($flags['optimize']) ? $flags['optimize'] : false;

        $this->build->build(getcwd(), $optimize, !$event->isDevMode());
    }

    public function getCommands()
    {
        return [
            new Command\BuildCommand('monorepo:build'),
            new Command\GitChangedCommand('monorepo:git-changed?')
        ];
    }
}
