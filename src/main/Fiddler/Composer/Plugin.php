<?php

namespace Fiddler\Composer;

use Fiddler\Build;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\CommandEvent;

class Plugin implements PluginInterface
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
        $this->build = $this->build ?: new Build($this->io);
    }

    public static function getSubscribedEvents()
    {
        return [
            'post-autoload-dump' => 'onPostAutoloadDump',
        ];
    }

    /**
     * Delegate autoload dump to all the fiddler monorepo subdirectories.
     */
    public function onPostAutoloadDump(CommandEvent $event)
    {
        $input = $event->getInput();
        $noDevMode = (bool)$input->getOption('no-dev');
        $optimize = (bool)$input->getOption('optimize-autoloader');

        $this->build->build(getcwd(), $optimize, $noDevMode);
    }
}
