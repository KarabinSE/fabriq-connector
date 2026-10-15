<?php

namespace Karabin\FabriqConnector\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Karabin\FabriqConnector\FabriqConnectorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Saloon\Laravel\SaloonServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Karabin\\FabriqConnector\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            FabriqConnectorServiceProvider::class,
            SaloonServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_fabriq-connector_table.php.stub';
        $migration->up();
        */
    }
}
