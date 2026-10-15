<?php

namespace Karabin\FabriqConnector;

use Karabin\FabriqConnector\Console\BlockHandlerMakeCommand;
use Karabin\FabriqConnector\Console\IntegrationRepositoryMakeCommand;
use Karabin\FabriqConnector\Console\PublishCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasCommands([
                PublishCommand::class,
                BlockHandlerMakeCommand::class,
                IntegrationRepositoryMakeCommand::class,
            ])
            ->name('fabriq-connector')
            ->hasConfigFile();
    }

    public function bootingPackage()
    {
        $this->publishes([
            __DIR__.'/Console/publishes/Integrations.php' => app_path('Http/Integrations/Integrations.php'),
            __DIR__.'/Console/publishes/FabriqRepository.php' => app_path('Http/Integrations/Fabriq/FabriqRepository.php'),
        ], $this->package->shortName().'-integration-extends');
    }
}
