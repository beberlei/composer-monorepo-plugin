<?php

namespace Monorepo;

use Composer\Util\Filesystem;

class BuildTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadPackagesSimpleExampleProject()
    {
        $build = new Build();
        $packages = $build->loadPackages(__DIR__ . '/../_fixtures/example-simple');

        $packageNames = array_keys($packages);
        sort($packageNames);
        $this->assertEquals(array('PSR4', 'bar', 'foo'), $packageNames);
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
        $baseDir = dirname(__DIR__);

        $build = new Build();
        $build->build(__DIR__ . '/../_fixtures/example-simple');

        $barNamespaces = include(__DIR__ . '/../_fixtures/example-simple/bar/vendor/composer/autoload_namespaces.php');
        $this->assertCount(1, $barNamespaces);
        $this->assertEquals(array('Bar\\'), array_keys($barNamespaces));

        $fooNamespaces = include(__DIR__ . '/../_fixtures/example-simple/foo/vendor/composer/autoload_namespaces.php');
        $this->assertCount(2, $fooNamespaces);
        $this->assertEquals(array('Foo\\', 'Bar\\'), array_keys($fooNamespaces));

        $psr4Namespaces = include(__DIR__ . '/../_fixtures/example-simple/PSR4/vendor/composer/autoload_psr4.php');
        $this->assertCount(1, $psr4Namespaces);
        $this->assertEquals(array('PSR4\\' => array($baseDir.'/PSR4/src')), $psr4Namespaces);
    }

    public function testBuildReplaceExampleProject()
    {
        $build = new Build();
        $build->build(__DIR__ . '/../_fixtures/example-replace');

        $bazNamespaces = include(__DIR__ . '/../_fixtures/example-replace/baz/vendor/composer/autoload_namespaces.php');
        $this->assertCount(2, $bazNamespaces);
        $this->assertEquals(array('Baz\\', 'Bar\\'), array_keys($bazNamespaces));
    }

    public function testBuildProvideExampleProject()
    {
        $build = new Build();
        $build->build(__DIR__ . '/../_fixtures/example-provide');

        $bazNamespaces = include(__DIR__ . '/../_fixtures/example-provide/baz/vendor/composer/autoload_namespaces.php');
        $this->assertCount(2, $bazNamespaces);
        $this->assertEquals(array('Baz\\', 'Bar\\'), array_keys($bazNamespaces));
    }

    public function testBuildWithAdvancedExampleProject()
    {
        $build = new Build();
        $build->build(__DIR__ . '/../_fixtures/example-advanced');

        $barAutoloadReal = file_get_contents(__DIR__ . '/../_fixtures/example-advanced/bar/vendor/composer/autoload_real.php');
        $barIncludeFiles = include(__DIR__ . '/../_fixtures/example-advanced/bar/vendor/composer/autoload_files.php');

        $this->assertEquals(array(realpath(__DIR__ . '/../../') . '/vendor/foo/baz/bin/baz'), array_values($barIncludeFiles));
        $this->assertContains('composerRequireOnce', $barAutoloadReal);
    }

    public function testBuildWithBinExampleProject()
    {
        $exampleDir = realpath(__DIR__ . '/../_fixtures/example-bin');
        chdir($exampleDir);
        $build = new Build();
        $build->build($exampleDir);

        $this->assertTrue(file_exists("$exampleDir/bar/vendor/bin/test-bin"));
        $link = readlink("$exampleDir/bar/vendor/bin/test-bin");
        $this->assertEquals('../../../vendor/test/bin/test-bin', $link);

        $this->assertTrue(file_exists("$exampleDir/foo/baz/vendor/bin/test-bin"));
        $link = readlink("$exampleDir/foo/baz/vendor/bin/test-bin");
        $this->assertEquals('../../../../vendor/test/bin/test-bin', $link);
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $dirs = glob(__DIR__ . '/../_fixtures/*/*/vendor');
        foreach ($dirs as $dir) {
            $fs->removeDirectoryPhp($dir);
        }

        $dirs = glob(__DIR__ . '/../_fixtures/*/*/*/vendor');
        foreach ($dirs as $dir) {
            $fs->removeDirectoryPhp($dir);
        }
    }
}
