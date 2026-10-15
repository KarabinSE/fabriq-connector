<?php

namespace Tests\Feature;

use Karabin\FabriqConnector\Tests\TestCase;

class BlockHandlerMakeCommandTest extends TestCase
{
    public function test_it_appends_handler_to_the_block_name()
    {
        $name = 'ExampleBlock'.uniqid();
        $path = app_path('Support/BlockHandlers/'.$name.'Handler.php');

        try {
            $this->artisan('fabriq:make-block-handler', ['name' => $name])
                ->assertExitCode(0);

            $this->assertFileExists($path);
            $this->assertStringContainsString(
                'class '.$name.'Handler extends BlockHandler',
                file_get_contents($path),
            );
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
