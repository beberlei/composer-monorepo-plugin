<?php

namespace Monorepo\Composer;

use Composer\Repository\InstalledRepositoryInterface;
use Composer\Package\PackageInterface;
use Composer\Installer\InstallationManager;

class MonorepoInstalledRepository implements InstalledRepositoryInterface
{
    /**
     * @var array
     */
    private $packages = array();

    /**
     * Checks if specified package registered (installed).
     *
     * @param PackageInterface $package package instance
     *
     * @return bool
     */
    public function hasPackage(PackageInterface $package)
    {
        return isset($this->packages[$package->getName()]);
    }

    /**
     * Searches for the first match of a package by name and version.
     *
     * @param string $name    package name
     * @param string $version package version
     *
     * @return PackageInterface|null
     */
    public function findPackage($name, $version)
    {
        if (isset($this->packages[$name])) {
            return $this->packages[$name];
        }
        return null;
    }

    /**
     * Searches for all packages matching a name and optionally a version.
     *
     * @param string $name    package name
     * @param string $version package version
     *
     * @return array
     */
    public function findPackages($name, $version = null)
    {
        return array();
    }

    public function getProviders($packageName)
    {
        return [];
    }

    public function isFresh()
    {
        return true;
    }

    public function getRepoName()
    {
    }

    /**
     * Returns list of registered packages.
     *
     * @return array
     */
    public function getPackages()
    {
        return $this->packages;
    }

    public function loadPackages(array $packageNameMap, array $acceptableStabilities, array $stabilityFlags)
    {
        return $this->packages;
    }

    /**
     * Searches the repository for packages containing the query
     *
     * @param  string  $query search query
     * @param  int     $mode  a set of SEARCH_* constants to search on, implementations should do a best effort only
     * @return array[] an array of array('name' => '...', 'description' => '...')
     */
    public function search($query, $mode = 0, $type = null)
    {
        return array();
    }

    public function count()
    {
        return count($this->packages);
    }

    /**
     * Writes repository (f.e. to the disc).
     */
    public function write($devMode, InstallationManager $installationManager)
    {
    }

    /**
     * Adds package to the repository.
     *
     * @param PackageInterface $package package instance
     */
    public function addPackage(PackageInterface $package)
    {
        $this->packages[$package->getName()] = $package;
    }

    /**
     * Removes package from the repository.
     *
     * @param PackageInterface $package package instance
     */
    public function removePackage(PackageInterface $package)
    {
        unset($this->packages[$package->getName()]);
    }

    /**
     * Get unique packages, with aliases resolved and removed
     *
     * @return PackageInterface[]
     */
    public function getCanonicalPackages()
    {
        return array_values($this->packages);
    }

    /**
     * Forces a reload of all packages
     */
    public function reload()
    {
    }
}
