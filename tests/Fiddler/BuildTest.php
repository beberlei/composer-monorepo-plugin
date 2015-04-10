<?php

namespace Fiddler;

class BuildTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadPackagesSimpleExampleProject()
    {
        $build = new Build();
        $packages = $build->loadPackages(__DIR__ . '/../_fixtures/example-simple');

        $packageNames = array_keys($packages);
        $this->assertEquals(array('foo', 'bar'), $packageNames);
    }

    public function testLoadPackagesComposerExampleProject()
    {
        $build = new Build();
        $packages = $build->loadPackages(__DIR__ . '/../_fixtures/example-composer');

        $packageNames = array_keys($packages);
        $this->assertEquals(array('vendor/foo/bar', 'vendor/foo/baz'), $packageNames);
    }

    public function testBuildSimpleExampleProject()
    {
        $build = new Build();
        $build->build(__DIR__ . '/../_fixtures/example-simple');
    }

    public function testBuildReplaceExampleProject()
    {
        $build = new Build();
        $build->build(__DIR__ . '/../_fixtures/example-replace');
    }
}
