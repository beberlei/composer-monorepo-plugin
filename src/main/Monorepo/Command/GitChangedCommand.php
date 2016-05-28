<?php

namespace Monorepo\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Command\BaseCommand;
use Composer\IO\ConsoleIO;

use Monorepo\Build;

class GitChangedCommand extends BaseCommand
{
    private $packages;
    private $checkPaths = [];
    private $checkedPackages = [];

    protected function configure()
    {
        $this
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

        if ($output->isVerbose()) {
            $output->writeln('Checking for changes in the following directories:');
            foreach ($this->checkPaths as $checkPath) {
                $output->writeln('- ' . $checkPath);
            }

            $output->writeln(sprintf('Iterating the changed files in git commit range %s', $range));
        }

        $found = false;
        foreach ($result as $changedFile) {
            if ($output->isVerbose()) {
                $output->writeln(sprintf("- %s", $changedFile));
            }

            foreach ($this->checkPaths as $checkPath) {
                if (strpos(trim($changedFile), $checkPath) !== false) {
                    if ($output->isVerbose()) {
                        $output->writeln(sprintf('  Matches check path %s', $checkPath));
                    }
                    $found = true;
                }
            }
        }

        exit($found ? 0 : 1);
    }

    private function calculateDependencies($packageName)
    {
        if (strpos($packageName, 'vendor') === 0) {
            return;
        }

        if (isset($this->checkedPackages[$packageName])) {
            return;
        }

        if (!isset($this->packages[$packageName])) {
            throw new \RuntimeException(sprintf("No package named '%s'.", $packageName));
        }

        $this->checkPaths[] = $packageName;

        foreach ($this->packages[$packageName]['deps'] as $dep) {
            $this->calculateDependencies($dep);
        }
    }
}
