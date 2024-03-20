<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;

class LookupControllerTest extends TestCase
{
    protected $baseUrl = 'http://localhost';
    //overriding the createApplication method to bootstrap Laravel before running the test.
    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        return $app;
    }

    /** @test */
    public function it_returns_correct_data_for_minecraft_username_lookup()
    {
        // Mocking Guzzle Client
        $httpClientMock = $this->createMock(Client::class);

        $this->app->instance(Client::class, $httpClientMock);

        $username = 'Notch';
        $id = '069a79f444e94726a5befca90e38aaf5';

        // Mocking Guzzle Response
        $expectedResponse = [
            'name' => $username,
            'id' => $id
        ];
        $response = new Response(200, [], json_encode($expectedResponse));

        $httpClientMock->expects($this->once())
            ->method('get')
            ->willReturn($response);

        // Calling the lookup method
        $request = Request::create('/lookup', 'GET', ['type' => 'minecraft', 'username' => $username]);
        $response = $this->json('GET', '/lookup', ['type' => 'minecraft', 'username' => $username]);

        // Assertion
        $response->assertStatus(200)
            ->assertJson([
                'username' => $username,
                'id' => $id,
                'avatar' => "https://crafatar.com/avatars/{$id}"
            ]);
    }
}
