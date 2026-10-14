<?php

namespace Karabin\FabriqConnector\Services;

use Inertia\Inertia;
use Karabin\FabriqConnector\BlockHandler\BlockHandler;

class BlockService
{
    private array $blocks = config('fabriq-connector.block_handlers', []);

    private array $handlers = [];

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

    public function shareInertia(): self
    {
        Inertia::share('blocks', fn () => $this->blocks);

        return $this;
    }

    private function processBlock(array $block): array
    {
        $handler = $this->resolveHandler($block['block_type']['component_name']);

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
