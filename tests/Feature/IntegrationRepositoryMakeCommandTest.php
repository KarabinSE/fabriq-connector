<?php

namespace Tests\Feature;

use Karabin\FabriqConnector\Tests\TestCase;
use Saloon\Laravel\SaloonServiceProvider;

class IntegrationRepositoryMakeCommandTest extends TestCase
{
    public function test_it_generates_a_repository_from_the_integration_name_and_connector()
    {
        $path = app_path('Http/Integrations/Silo/SiloRepository.php');

        try {
            $this->artisan('fabriq:make-integration', [
                'name' => 'silo',
                'connector' => 'SiloConnector',
            ])
                ->expectsConfirmation(
                    'Connector SiloConnector does not exist. Would you like to create it?',
                    'no',
                )
                ->assertExitCode(0);

            $this->assertFileExists($path);

            $contents = file_get_contents($path);
            $this->assertStringContainsString('namespace App\\Http\\Integrations\\Silo;', $contents);
            $this->assertStringContainsString('class SiloRepository', $contents);
            $this->assertStringContainsString('private readonly SiloConnector $connector;', $contents);
            $this->assertStringContainsString('$this->connector = new SiloConnector;', $contents);
            $this->assertStringNotContainsString('{{ connector }}', $contents);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_it_creates_a_missing_connector_when_confirmed()
    {
        $repositoryPath = app_path('Http/Integrations/Silo/SiloRepository.php');
        $connectorPath = app_path('Http/Integrations/Silo/SiloConnector.php');
        $this->app->register(SaloonServiceProvider::class);

        try {
            $this->artisan('fabriq:make-integration', [
                'name' => 'silo',
                'connector' => 'SiloConnector',
            ])
                ->expectsConfirmation(
                    'Connector SiloConnector does not exist. Would you like to create it?',
                    'yes',
                )
                ->assertExitCode(0);

            $this->assertFileExists($repositoryPath);
            $this->assertFileExists($connectorPath);
        } finally {
            foreach ([$repositoryPath, $connectorPath] as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }

            foreach ([dirname($repositoryPath), dirname($connectorPath)] as $directory) {
                if (is_dir($directory) && count(scandir($directory)) === 2) {
                    rmdir($directory);
                }
            }
        }
    }
}
