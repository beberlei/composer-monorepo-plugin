<?php

namespace Fiddler\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Composer\IO\ConsoleIO;

use Fiddler\Build;

class GitChangedCommand extends Command
{
    private $packages;
    private $checkPaths = [];
    private $checkedPackages = [];

    protected function configure()
    {
        $this
            ->setName('git-changed?')
            ->setDescription('Test if a given component has changed based on a Git Commit Range')
            ->addArgument('package', InputArgument::REQUIRED, 'Path to the package')
            ->addArgument('range', InputArgument::OPTIONAL, 'Git commit range to check')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $range = $input->getArgument('range') ?: (isset($_SERVER['TRAVIS_COMMIT_RANGE']) ? $_SERVER['TRAVIS_COMMIT_RANGE'] : '');

        if (!$range) {
            throw new \RuntimeException("No git range given via argument or TRAVIS_COMMIT_RANGE environment variable.");
        }

        exec('git diff --name-only ' . escapeshellarg($range), $result);

        $build = new Build(new ConsoleIO($input, $output, $this->getHelperSet()));
        $this->packages = $build->loadPackages(getcwd());

        $changePackageName = rtrim($input->getArgument('package'), '/');

        $this->calculateDependencies($changePackageName);

        foreach ($this->checkPaths as $checkPath) {
            foreach ($result as $changedFile) {
                if (strpos(trim($changedFile), $checkPath) !== false) {
                    $output->write("0");
                    exit(0);
                }
            }
        }

        $output->write("1");
        exit(1);
    }

    private function calculateDependencies($packageName)
    {
        if (isset($this->checkedPackages[$packageName])) {
            return;
        }

        if (!isset($this->packages[$packageName])) {
            throw new \RuntimeException(sprintf("No package named '%s'.", $packageName));
        }

        foreach ($this->packages[$packageName]['deps'] as $dep) {
            if (strpos($dep, 'vendor') !== 0) {
                continue;
            }

            $this->checkPaths[] = $dep;
        }
    }
}
