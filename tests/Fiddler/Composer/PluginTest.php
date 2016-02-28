<?php

namespace Fiddler\Composer;

use Fiddler\Build;
use Composer\Composer;
use Composer\Plugin\CommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    public function testOnPostAutoloadDump()
    {
        $build = \Phake::mock(Build::class);
        $composer = \Phake::mock(Composer::class);
        $input = \Phake::mock(InputInterface::class);
        $output = \Phake::mock(OutputInterface::class);

        $plugin = new Plugin($build);
        $plugin->onPostAutoloadDump(new CommandEvent('foo', 'foo', $input, $output));

        \Phake::verify($build)->build(getcwd(), false, false);
    }
}
