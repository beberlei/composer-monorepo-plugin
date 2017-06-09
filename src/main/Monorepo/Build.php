<?php
/**
 * Monorepo
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace Monorepo;

use Monorepo\Composer\MonorepoInstalledRepository;
use Monorepo\Composer\MonorepoInstaller;
use Monorepo\Composer\EventDispatcher;
use Monorepo\Composer\AutoloadGenerator;
use Symfony\Component\Finder\Finder;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Config;
use Composer\Composer;
use Composer\Package\Package;

/**
 * Scan project for monorepo.json files, indicating components, "building" them.
 *
 * The build step is very simple and consists of generating a
 * `vendor/autoload.php` file similar to how Composer generates it.
 *
 * Prototype at Monorepo funtionality. No change detection yet.
 */
class Build
{
    private $io;

    public function __construct(IOInterface $io = null)
    {
        $this->io = $io ?: new NullIO();
    }

    public function build($rootDirectory, $optimize = false, $noDevMode = false)
    {
        $this->io->write(sprintf('<info>Generating autoload files for monorepo sub-packages %s dev-dependencies.</info>', $noDevMode ? 'without' : 'with'));
        $start = microtime(true);

        $packages = $this->loadPackages($rootDirectory);

        $evm = new EventDispatcher(new Composer(), $this->io);
        $generator = new AutoloadGenerator($evm, $this->io);
        $generator->setDevMode(!$noDevMode);
        $installationManager = new InstallationManager();
        $installationManager->addInstaller(new MonorepoInstaller());

        foreach ($packages as $packageName => $config) {
            if (strpos($packageName, 'vendor') === 0) {
                continue;
            }

            $this->io->write(sprintf(' [Subpackage] <comment>%s</comment>', $packageName));

            $mainPackage = new Package($packageName, "@stable", "@stable");
            $mainPackage->setType('monorepo');
            $mainPackage->setAutoload($config['autoload']);
            $mainPackage->setDevAutoload($config['autoload-dev']);

            $localRepo = new MonorepoInstalledRepository();
            $this->resolvePackageDependencies($localRepo, $packages, $packageName);

            $composerConfig = new Config(true, $rootDirectory);
            $composerConfig->merge(array('config' => array('vendor-dir' => $config['path']. '/vendor')));
            $generator->dump(
                $composerConfig,
                $localRepo,
                $mainPackage,
                $installationManager,
                'composer',
                $optimize
            );

            $binDir = $config['path'] . '/vendor/bin';
            $fullBinDir = $rootDirectory . '/' . $binDir;
            $relativeRootDirectory = str_repeat('../', substr_count($binDir, '/')+1);

            if (! is_dir($fullBinDir)) {
                mkdir($fullBinDir, 0755, true);
            }

            // remove old symlinks
            array_map('unlink', glob($fullBinDir . '/*'));

            foreach ($localRepo->getPackages() as $package) {


                foreach ($package->getBinaries() as $binary) {

                    $binFile = $fullBinDir . '/' . basename($binary);
                    symlink($relativeRootDirectory . $binary, $binFile);
                }
            }
        }

        $duration = microtime(true) - $start;

        $this->io->write(sprintf('Monorepo subpackage autoloads generated in <comment>%0.2f</comment> seconds.', $duration));
    }

    private function resolvePackageDependencies($repository, $packages, $packageName)
    {
        $config = $packages[$packageName];
        $dependencies = $config['deps'];

        if (isset($config['deps-dev'])) {
            $dependencies = array_merge($dependencies, $config['deps-dev']);
        }

        foreach ($dependencies as $dependencyName) {
            $isVendor = (strpos($dependencyName, 'vendor/') === 0);
            if ($dependencyName === 'vendor/php' || strpos($dependencyName, 'vendor/ext-') === 0 || strpos($dependencyName, 'vendor/lib-') === 0) {
                continue; // Meta-dependencies that composer checks
            }

            if (!isset($packages[$dependencyName])) {
                if ($dependencyName == 'vendor/composer-plugin-api') {
                    continue;
                }
                if($isVendor){
                    throw new \RuntimeException("Requiring non-existent composer-package '" . $dependencyName . "' in '" . $packageName . "'. Please ensure it is present in composer.json.");
                }else{
                    throw new \RuntimeException("Requiring non-existent repo-module '" . $dependencyName . "' in '" . $packageName . "'. Please check that the subdirectory exists, or append \"vendor/\" to reference a composer-package.");
                }

            }

            $dependency = $packages[$dependencyName];
            $package = new Package($dependency['path'], "@stable", "@stable");
            $package->setType('monorepo');

            if (isset($dependency['autoload']) && is_array($dependency['autoload'])) {
                $package->setAutoload($dependency['autoload']);
            }

            if (isset($dependency['bin']) && is_array($dependency['bin'])) {
                $package->setBinaries($dependency['bin']);
            }

            if (!$repository->hasPackage($package)) {
                $repository->addPackage($package);
                $this->resolvePackageDependencies($repository, $packages, $dependencyName);
            }
        }
    }

    public function loadPackages($rootDirectory)
    {
        $finder = new Finder();
        $finder->in($rootDirectory)
               ->exclude('vendor')
               ->ignoreVCS(true)
               ->name('monorepo.json');

        $packages = array();

        foreach ($finder as $file) {
            $monorepoJson = $this->loadMonorepoJson($file->getContents(), $file->getPath());

            if ($monorepoJson === NULL) {
                throw new \RuntimeException("Invalid " . $file->getRelativePath() . '/monorepo.json file.');
            }

            $monorepoJson['path'] = $file->getRelativePath();

            if (!isset($monorepoJson['autoload'])) {
                $monorepoJson['autoload'] = array();
            }
            if (!isset($monorepoJson['autoload-dev'])) {
                $monorepoJson['autoload-dev'] = array();
            }
            if (!isset($monorepoJson['deps'])) {
                $monorepoJson['deps'] = array();
            }
            if (!isset($monorepoJson['deps-dev'])) {
                $monorepoJson['deps-dev'] = array();
            }

            $packages[$file->getRelativePath()] = $monorepoJson;
        }

        $installedJsonFile = $rootDirectory . '/vendor/composer/installed.json';
        if (file_exists($installedJsonFile)) {
            $installed = json_decode(file_get_contents($installedJsonFile), true);

            if ($installed === NULL) {
                throw new \RuntimeException("Invalid installed.json file at " . dirname($installedJsonFile));
            }

            foreach ($installed as $composerJson) {
                $name = $composerJson['name'];

                $monorepoedComposerJson = array(
                    'path' => 'vendor/' . $name,
                    'autoload' => array(),
                    'deps' => array(),
                    'bin' => array(),
                );

                if (isset($composerJson['autoload'])) {
                    $monorepoedComposerJson['autoload'] = $composerJson['autoload'];
                }

                if (isset($composerJson['autoload-dev'])) {
                    $monorepoedComposerJson['autoload'] = array_merge_recursive(
                        $monorepoedComposerJson['autoload'],
                        $composerJson['autoload-dev']
                    );
                }

                if (isset($composerJson['require'])) {
                    foreach ($composerJson['require'] as $packageName => $_) {
                        $monorepoedComposerJson['deps'][] = 'vendor/' . $packageName;
                    }
                }

                if (isset($composerJson['bin'])) {
                    foreach ($composerJson['bin'] as $binary) {
                        $binary = 'vendor/' . $composerJson['name'] . '/' . $binary;
                        if (! in_array($binary, $monorepoedComposerJson['bin'])) {
                            $monorepoedComposerJson['bin'][] = $binary;
                        }
                    }
                }

                $packages['vendor/' . strtolower($name)] = $monorepoedComposerJson;

                if (isset($composerJson['provide'])) {
                    foreach ($composerJson['provide'] as $provideName => $_) {
                        $packages['vendor/' . $provideName] = $monorepoedComposerJson;
                    }
                }

                if (isset($composerJson['replace'])) {
                    foreach ($composerJson['replace'] as $replaceName => $_) {
                        $packages['vendor/' . $replaceName] = $monorepoedComposerJson;
                    }
                }
            }
        }

        return $packages;
    }

    private function loadMonorepoJson($contents, $path)
    {
        $schema = json_decode(file_get_contents(__DIR__ . '/../../resources/monorepo-schema.json'));
        $data = json_decode($contents);

        // Validate
        $validator = new \JsonSchema\Validator();
        $validator->check($data, $schema);

        if (!$validator->isValid()) {
            $errors = array();
            foreach ($validator->getErrors() as $error) {
                $errors[] = sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            throw new \RuntimeException(sprintf("JSON is not valid in %s\n%s", $path, implode("\n", $errors)));
        }

        return json_decode($contents, true);
    }
}
