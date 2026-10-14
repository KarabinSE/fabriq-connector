<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Karabin\FabriqConnector\BlockHandler\Handlers\ContactsBlockHandler;
use Karabin\FabriqConnector\Services\BlockService;
use Karabin\FabriqConnector\Tests\TestCase;
use Saloon\MockConfig;

class BlockServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        MockConfig::setFixturePath('tests/_fixtures/Saloon');
    }

    public function test_it_has_default_block_handlers()
    {
        // Arrange
        $service = new BlockService;

        $this->assertCount(2, $service->handlers);
    }

    public function test_it_can_add_custom_handlers()
    {
        // Arrange
        Config::set('fabriq-connector.block_handlers', []);

        $service = new BlockService;
        $this->assertCount(0, $service->handlers);

        $service->configure()
            ->registerHandlers([
                'ContactsBlock' => ContactsBlockHandler::class,
            ]);

        $this->assertCount(1, $service->handlers);
    }
}
