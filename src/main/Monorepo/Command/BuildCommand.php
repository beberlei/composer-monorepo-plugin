<?php

namespace Monorepo\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\IO\ConsoleIO;
use Composer\Command\BaseCommand;

use Monorepo\Build;

class BuildCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setDescription('Monorepo Build step generates all autoloaders for all components.')
            ->setDefinition(array(
                new InputOption('no-dev', null, InputOption::VALUE_NONE, 'Disables installation of require-dev packages.'),
                new InputOption('optimize-autoloader', 'o', InputOption::VALUE_NONE, 'Optimize autoloader during autoloader dump'),
                new InputOption('classmap-authoritative', 'a', InputOption::VALUE_NONE, 'Autoload classes from the classmap only. Implicitly enables `--optimize-autoloader`.'),
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $noDevMode = (bool)$input->getOption('no-dev');
        $optimize = (bool)$input->getOption('optimize-autoloader');
        $classmapAuthoritative = (bool)$input->getOption('classmap-authoritative');

        $build = new Build(new ConsoleIO($input, $output, $this->getHelperSet()));
        $build->build(getcwd(), $optimize, $noDevMode, $classmapAuthoritative);
    }
}
