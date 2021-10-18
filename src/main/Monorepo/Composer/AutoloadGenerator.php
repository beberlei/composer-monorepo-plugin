<?php

namespace Monorepo\Composer;

use Composer\Installer\InstallationManager;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;

class AutoloadGenerator extends \Composer\Autoload\AutoloadGenerator
{
    public function buildPackageMap(InstallationManager $installationManager, PackageInterface $mainPackage, array $packages)
    {
        $packageMap = parent::buildPackageMap($installationManager, $mainPackage, $packages);

        $packageMap[0][1] = $installationManager->getInstallPath($mainPackage); // hack the install path

        return $packageMap;
    }

    protected function getFileIdentifier(PackageInterface $package, $path)
    {
        $extra = $package->getExtra();

        return md5(
            (isset($extra['monorepo']['original_name']) ? $extra['monorepo']['original_name'] : $package->getName()) .
            ':' .
            $path
        );
    }

    protected function filterPackageMap(array $packageMap, RootPackageInterface $mainPackage)
    {
        return $packageMap;
    }

    protected function getAutoloadFile($vendorPathToTargetDirCode, $suffix)
    {
        $code = parent::getAutoloadFile($vendorPathToTargetDirCode, $suffix);

        $code = str_replace('<?php', <<<PHP
<?php
putenv('COMPOSER_VENDOR_DIR=' . __DIR__);

PHP
            , $code);

        return $code;
    }
}
