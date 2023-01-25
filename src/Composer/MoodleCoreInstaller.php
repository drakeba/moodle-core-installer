<?php

namespace Drakeba\Composer;

use Composer\Config;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class MoodleCoreInstaller extends LibraryInstaller
{
    public function getInstallPath( PackageInterface $package ) {
        $installationDir = false;
        $prettyName      = $package->getPrettyName();
        if ( $this->composer->getPackage() ) {
            $topExtra = $this->composer->getPackage()->getExtra();
            if ( ! empty( $topExtra['moodle-install-dir'] ) ) {
                $installationDir = $topExtra['moodle-install-dir'];
                if ( is_array( $installationDir ) ) {
                    $installationDir = empty( $installationDir[ $prettyName ] ) ? false : $installationDir[ $prettyName ];
                }
            }
        }
        $extra = $package->getExtra();
        if ( ! $installationDir && ! empty( $extra['moodle-install-dir'] ) ) {
            $installationDir = $extra['moodle-install-dir'];
        }
        if ( ! $installationDir ) {
            $installationDir = 'moodle';
        }
        $vendorDir = $this->composer->getConfig()->get( 'vendor-dir', Config::RELATIVE_PATHS ) ?: 'vendor';
        if (
            in_array( $installationDir, $this->sensitiveDirectories ) ||
            ( $installationDir === $vendorDir )
        ) {
            throw new \InvalidArgumentException( $this->getSensitiveDirectoryMessage( $installationDir, $prettyName ) );
        }
        if (
            ! empty( self::$_installedPaths[ $installationDir ] ) &&
            $prettyName !== self::$_installedPaths[ $installationDir ]
        ) {
            $conflict_message = $this->getConflictMessage( $prettyName, self::$_installedPaths[ $installationDir ] );
            throw new \InvalidArgumentException( $conflict_message );
        }
        self::$_installedPaths[ $installationDir ] = $prettyName;

        return $installationDir;
    }
}
