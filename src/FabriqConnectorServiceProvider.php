<?php

namespace Karabin\FabriqConnector;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Karabin\FabriqConnector\Commands\FabriqConnectorCommand;

class FabriqConnectorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('fabriq-connector')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_fabriq-connector_table')
            ->hasCommand(FabriqConnectorCommand::class);
    }
}
