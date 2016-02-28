<?php

namespace Fiddler\Composer;

use Fiddler\Build;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\Event;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    public function testOnPostAutoloadDump()
    {
        $build = \Phake::mock(Build::class);
        $composer = \Phake::mock(Composer::class);
        $io = \Phake::mock(IOInterface::class);

        $event = new Event('post-autoload-dump', $composer, $io);
        $plugin = new Plugin($build);
        $plugin->generateMonorepoAutoloads($event);

        \Phake::verify($build)->build(getcwd(), false, false);
    }
}
