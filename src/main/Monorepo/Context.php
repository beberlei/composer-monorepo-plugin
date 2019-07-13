<?php
/**
 * Created by PhpStorm.
 * User: santino83
 * Date: 13/07/19
 * Time: 21.51
 */

namespace Monorepo;


class Context
{
    /**
     * The monorepo project root directory
     * @var string
     */
    private $rootDirectory;

    /**
     * Optimize autoloader flag (default false)
     * @var bool
     */
    private $optimize = false;

    /**
     * Exclude dev packages (default false)
     * @var bool
     */
    private $noDevMode = false;

    /**
     * Context constructor.
     * @param string $rootDirectory
     * @param bool $optimize
     * @param bool $noDevMode
     */
    public function __construct($rootDirectory, $optimize = false, $noDevMode = false)
    {
        $this->rootDirectory = $rootDirectory;
        $this->optimize = $optimize;
        $this->noDevMode = $noDevMode;
    }

    /**
     * @return string
     */
    public function getRootDirectory()
    {
        return $this->rootDirectory;
    }

    /**
     * @return bool
     */
    public function isOptimize()
    {
        return $this->optimize;
    }

    /**
     * @return bool
     */
    public function isNoDevMode()
    {
        return $this->noDevMode;
    }

}