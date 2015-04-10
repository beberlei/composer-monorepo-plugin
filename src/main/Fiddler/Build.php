<?php
/**
 * Fiddler
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace Fiddler;

use Symfony\Component\Finder\Finder;

/**
 * Scan project for fiddler.json files, indicating components, "building" them.
 *
 * The build step is very simple and consists of generating a
 * `vendor/autoload.php` file similar to how Composer generates it.
 *
 * Prototype at Fiddler funtionality. No change detection yet.
 */
class Build
{
    public function build($rootDirectory)
    {
        $packages = $this->loadPackages($rootDirectory);
    }

    public function loadPackages($rootDirectory)
    {
        $finder = new Finder();
        $finder->in($rootDirectory)
               ->exclude('vendor')
               ->ignoreVCS(true)
               ->useBestAdapter()
               ->name('fiddler.json');

        $packages = array();

        foreach ($finder as $file) {
            $contents = $file->getContents();
            $packages[$file->getRelativePath()] = json_decode($contents, true);
        }

        if (file_exists($rootDirectory . '/vendor')) {
            $finder->in($rootDirectory . '/vendor')
                   ->ignoreVCS(true)
                   ->ignoreUnreadableDirs()
                   ->useBestAdapter()
                   ->name('composer.json');

            foreach ($finder as $file) {
                $contents = $file->getContents();
                $composerJson = json_decode($contents, true);
                $fiddleredComposerJson = array(
                    'autoload' => array(),
                    'deps' => array()
                );

                if (isset($composerJson['name'])) {
                    $fiddleredComposerJson['name'] = $composerJson['name'];
                }

                if (isset($composerJson['autoload'])) {
                    $fiddleredComposerJson['autoload'] = $composerJson['autoload'];
                }

                if (isset($composerJson['autoload-dev'])) {
                    $fiddleredComposerJson['autoload'] = array_merge_recursive(
                        $fiddleredComposerJson['autoload'],
                        $composerJson['autoload-dev']
                    );
                }

                if (isset($composerJson['require'])) {
                    foreach ($composerJson['require'] as $packageName => $_) {
                        $fiddleredComposerJson['deps'][] = 'vendor/' . $packageName;
                    }
                }
                if (isset($composerJson['require-dev'])) {
                    foreach ($composerJson['require-dev'] as $packageName => $_) {
                        $fiddleredComposerJson['deps'][] = 'vendor/' . $packageName;
                    }
                }

                $packages['vendor/' . $file->getRelativePath()] = $fiddleredComposerJson;
            }
        }

        return $packages;
    }
}
