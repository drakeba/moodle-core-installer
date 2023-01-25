<?php

namespace Drakeba\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class MoodleCorePlugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new MoodleCoreInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
