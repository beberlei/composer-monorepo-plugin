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
            ->setName('build')
            ->setDescription('Fiddler Build step generates all autoloaders for all components.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $build = new Build(new ConsoleIO($input, $output, $this->getHelperSet()));
        $build->build(getcwd());
    }
}
