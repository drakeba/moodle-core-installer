<?php

namespace Drakeba\Composer;

use Composer\Config;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class MoodleCoreInstaller extends LibraryInstaller
{
    const TYPE = 'moodle-core';
    const DEFAULT_DIR = 'moodle';
    const INSTALL_META_KEY = 'moodle-install-dir';

    const MESSAGE_CONFLICT = 'Two packages (%s and %s) cannot share the same directory!';
    const MESSAGE_SENSITIVE = 'Warning! %s is an invalid Moodle install directory (from %s)!';

    private static $_installedPaths = array();

    private $sensitiveDirectories = array( '.' );

    public function getInstallPath( PackageInterface $package ) {
        $installationDir = false;
        $prettyName      = $package->getPrettyName();
        if ( $this->composer->getPackage() ) {
            $topExtra = $this->composer->getPackage()->getExtra();
            if ( ! empty( $topExtra[self::INSTALL_META_KEY] ) ) {
                $installationDir = $topExtra[self::INSTALL_META_KEY];
                if ( is_array( $installationDir ) ) {
                    $installationDir = empty( $installationDir[ $prettyName ] ) ? false : $installationDir[ $prettyName ];
                }
            }
        }
        $extra = $package->getExtra();
        if ( ! $installationDir && ! empty( $extra[self::INSTALL_META_KEY] ) ) {
            $installationDir = $extra[self::INSTALL_META_KEY];
        }
        if ( ! $installationDir ) {
            $installationDir = self::DEFAULT_DIR;
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

    public function supports( $packageType ) {
        return self::TYPE === $packageType;
    }

    private function getConflictMessage( $attempted, $alreadyExists ) {
        return sprintf( self::MESSAGE_CONFLICT, $attempted, $alreadyExists );
    }

    private function getSensitiveDirectoryMessage( $attempted, $packageName ) {
        return sprintf( self::MESSAGE_SENSITIVE, $attempted, $packageName );
    }
}
