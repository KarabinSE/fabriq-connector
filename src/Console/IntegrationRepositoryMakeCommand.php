<?php

namespace Karabin\FabriqConnector\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(
    name: 'fabriq:make-integration',
    description: 'Creates a new Integration repository from a stub',
)]
class IntegrationRepositoryMakeCommand extends GeneratorCommand
{
    protected $type = 'Integration repository';

    protected function getStub()
    {
        return __DIR__.'/Stubs/repository.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Http\\Integrations\\'.Str::studly(parent::getNameInput());
    }

    protected function getNameInput()
    {
        $name = Str::studly(parent::getNameInput());

        return Str::endsWith($name, 'Repository')
            ? $name
            : $name.'Repository';
    }

    protected function getArguments()
    {
        return [
            ...parent::getArguments(),
            ['connector', InputArgument::REQUIRED, 'The connector class to use'],
        ];
    }

    protected function promptForMissingArgumentsUsing()
    {
        return [
            'name' => ['What is the name of the integration?', 'e.g. silo'],
            'connector' => ['What is the name of the connector?', 'e.g. SiloConnector'],
        ];
    }

    protected function buildClass($name)
    {
        return str_replace(
            ['{{ connector }}', '{{ integration }}'],
            [$this->argument('connector'), Str::studly(parent::getNameInput())],
            parent::buildClass($name),
        );
    }

    public function handle()
    {
        $connector = $this->argument('connector');

        if (! $this->connectorExists($connector)
            && $this->confirm("Connector {$connector} does not exist. Would you like to create it?")) {
            $exitCode = $this->call('saloon:connector', [
                'integration' => Str::studly(parent::getNameInput()),
                'name' => $connector,
            ]);

            if ($exitCode !== self::SUCCESS) {
                return $exitCode;
            }
        }

        return parent::handle();
    }

    private function connectorExists(string $connector): bool
    {
        if (class_exists($connector)) {
            return true;
        }

        if (str_contains($connector, '\\')) {
            return false;
        }

        $class = $this->rootNamespace()
            .'Http\\Integrations\\'
            .Str::studly(parent::getNameInput())
            .'\\'.$connector;

        return class_exists($class);
    }
}
