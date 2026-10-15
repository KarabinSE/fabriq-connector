<?php

namespace Karabin\FabriqConnector\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'fabriq:make-block-handler',
    description: 'Creates a Fabriq block handler from a stub',
)]
class BlockHandlerMakeCommand extends GeneratorCommand
{
    protected $type = 'BlockHandler';

    protected function getStub()
    {
        return __DIR__.'/Stubs/block_handler.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Support\BlockHandlers';
    }

    protected function getNameInput()
    {
        $name = parent::getNameInput();

        return Str::endsWith($name, 'Handler') ? $name : $name.'Handler';
    }

    protected function promptForMissingArgumentsUsing()
    {
        return [
            'name' => ['What is the name of the block?', 'e.g. ContactsBlock'],
        ];
    }
}
