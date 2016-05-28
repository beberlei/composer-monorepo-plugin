<?php

namespace Fiddler\Composer;

use Fiddler\Command;
use Composer\Plugin\Capability\CommandProvider;

class MonorepoCommands implements CommandProvider
{
    public function getCommands()
    {
        return [
            new Command\BuildCommand('monorepo:build'),
            new Command\GitChangedCommand('monorepo:git-changed?')
        ];
    }
}
