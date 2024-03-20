<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\LookupController;
use GuzzleHttp\Client;
use Illuminate\Http\Request;


class LookupControllerUnitTest extends TestCase
{
    public function testSteamOnlySupportsIDs()
    {
        // Mocking Guzzle HTTP Client
        $mockHttpClient = $this->createMock(Client::class);

        // Creating request with 'steam' type and 'username' parameter
        $request = new Request([
            'type' => 'steam',
            'username' => 'test'
        ]);

        // Creating LookupController instance with mocked Guzzle HTTP Client
        $controller = new LookupController($mockHttpClient);

        // Making the lookup request
        $response = $controller->lookup($request);

        // Asserting that the response contains the expected error message
        $this->assertEquals(json_encode(['error' => 'Steam only supports IDs']), $response->getContent());
        $this->assertEquals(400, $response->getStatusCode());
    }
}
