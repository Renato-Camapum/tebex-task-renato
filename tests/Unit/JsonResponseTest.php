<?php

use PHPUnit\Framework\TestCase;


class JsonResponseTest extends TestCase
{
    public function testJsonResponse()
    {
        $url = 'http://localhost:8000/lookup?type=xbl&username=tebex';

        // Make the HTTP request
        $response = file_get_contents($url);

        // Decode the JSON response
        $json = json_decode($response, true);

        // Define the expected JSON data
        $expectedJson = [
            "username" => "Tebex",
            "id" => "2533274844413377",
            "avatar" => "https://avatar-ssl.xboxlive.com/avatar/2533274844413377/avatarpic-l.png"
        ];

        // Assert that the response matches the expected JSON
        $this->assertEquals($expectedJson, $json);
    }
}
