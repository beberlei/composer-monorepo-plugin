<?php

namespace Fiddler\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Composer\IO\ConsoleIO;

use Fiddler\Build;

class BuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Fiddler Build step generates all autoloaders for all components.')
            ->setDefinition(array(
                new InputOption('no-dev', null, InputOption::VALUE_NONE, 'Disables installation of require-dev packages.'),
                new InputOption('optimize-autoloader', 'o', InputOption::VALUE_NONE, 'Optimize autoloader during autoloader dump'),
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $noDevMode = (bool)$input->getOption('no-dev');
        $optimize = (bool)$input->getOption('optimize-autoloader');

        $build = new Build(new ConsoleIO($input, $output, $this->getHelperSet()));
        $build->build(getcwd(), $optimize, $noDevMode);
    }
}
