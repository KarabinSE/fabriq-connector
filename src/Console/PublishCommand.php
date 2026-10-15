<?php

namespace Karabin\FabriqConnector\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'fabriq-connector:publish',
    description: 'Publishes files for extending the default FabriqConnector Integrations',
)]
class PublishCommand extends Command
{
    public function handle()
    {
        $this->info('Publishing files for extending FabriqConnector Integrations');
        $this->call('vendor:publish', [
            '--provider' => 'Karabin\FabriqConnector\FabriqConnectorServiceProvider',
            '--tag' => 'fabriq-connector-integration-extends',
            '--force' => true,
        ]);

        $this->info('Fabriq connector has been published');

        return 0;
    }
}
