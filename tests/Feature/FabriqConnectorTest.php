<?php

namespace Tests\Feature;

use Karabin\FabriqConnector\FabriqConnector;
use Karabin\FabriqConnector\Requests\GetContactsRequest;
use Karabin\FabriqConnector\Requests\GetNewsRequest;
use Karabin\FabriqConnector\Tests\TestCase;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Saloon;
use Saloon\MockConfig;

class FabriqConnectorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        MockConfig::setFixturePath('tests/_fixtures/Saloon');
    }

    public function test_it_can_instantiate_the_connector()
    {
        // Arrange
        $connector = new FabriqConnector(locale: 'en');

        // Act

        // Assert
        $this->assertInstanceOf(FabriqConnector::class, $connector);
    }

    public function test_it_can_get_a_list_of_news()
    {
        // Arrange
        Saloon::fake([
            GetNewsRequest::class => MockResponse::fixture('news'),
        ]);
        $connector = new FabriqConnector();
        $request = new GetNewsRequest();

        // Act
        $response = $connector->send($request);

        // Assert
        $this->assertEquals('2023-08-09', $response->json('0.publishes_at_date'));
    }

    public function test_it_can_get_contacts()
    {
        // Arrange
        Saloon::fake([
            GetContactsRequest::class => MockResponse::fixture('contacts'),
        ]);
        $connector = new FabriqConnector();
        $request = new GetContactsRequest();

        // Act
        $response = $connector->send($request);

        // Assert
        $this->assertEquals('Frans Rosander', $response->json('0.name'));
    }
}
