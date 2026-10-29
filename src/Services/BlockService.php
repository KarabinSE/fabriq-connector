<?php

namespace Karabin\FabriqConnector\Services;

use Karabin\FabriqConnector\BlockHandler\BlockHandler;

class BlockService
{
    public array $blocks = [];

    public array $handlers = [];

    public function __construct()
    {
        $this->handlers = config('fabriq-connector.block_handlers', []);
    }

    public function configure(?array $handlers = []): self
    {
        $this->registerHandlers($handlers);

        return $this;
    }

    public function processBlocks(array $blocks): self
    {
        $this->blocks = array_map(
            fn (array $block) => $this->processBlock($block),
            $blocks
        );

        return $this;
    }

    public function blocks(): array
    {
        return $this->blocks;
    }

    private function processBlock(array $block): array
    {
        $handler = $this->resolveHandler($block['component_name']);

        if (! $handler) {
            return $block;
        }

        return $handler->handle($block);
    }

    public function registerHandlers(array $handlers): self
    {
        $this->handlers = array_merge($this->handlers, $handlers);

        return $this;
    }

    private function resolveHandler(string $blockName): ?BlockHandler
    {
        if (! isset($this->handlers[$blockName])) {
            return null;
        }

        return app($this->handlers[$blockName]);
    }
}
