<?php

namespace Monorepo\Composer;

use Monorepo\Build;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    public function testOnPostAutoloadDump()
    {
        $build = \Phake::mock(Build::class);
        $composer = \Phake::mock(Composer::class);
        $io = \Phake::mock(IOInterface::class);

        $event = new Event(
            'post-autoload-dump',
            $composer,
            $io,
            false, // dev-mode
            [], // args
            ['optimize' => false, 'classmap-authoritative' => false] // flags
        );
        $plugin = new Plugin($build);
        $plugin->generateMonorepoAutoloads($event);

        \Phake::verify($build)->build(getcwd(), false, true, false);
    }
}
