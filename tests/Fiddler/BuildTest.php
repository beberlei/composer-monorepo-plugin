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

        $barNamespaces = include(__DIR__ . '/../_fixtures/example-simple/bar/vendor/composer/autoload_namespaces.php');
        $this->assertCount(1, $barNamespaces);
        $this->assertEquals(array('Bar\\'), array_keys($barNamespaces));

        $fooNamespaces = include(__DIR__ . '/../_fixtures/example-simple/foo/vendor/composer/autoload_namespaces.php');
        $this->assertCount(2, $fooNamespaces);
        $this->assertEquals(array('Foo\\', 'Bar\\'), array_keys($fooNamespaces));
    }

    public function testBuildReplaceExampleProject()
    {
        $build = new Build();
        $build->build(__DIR__ . '/../_fixtures/example-replace');

        $bazNamespaces = include(__DIR__ . '/../_fixtures/example-replace/baz/vendor/composer/autoload_namespaces.php');
        $this->assertCount(2, $bazNamespaces);
        $this->assertEquals(array('Baz\\', 'Bar\\'), array_keys($bazNamespaces));
    }
}
