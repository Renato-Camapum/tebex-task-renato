<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LookupController extends Controller
{
    protected $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    // Method to handle different types of lookups based on 'type' parameter
    public function lookup(Request $request)
    {
        $type = $request->get('type');

        //here I have decided to go for a switch method to call the lookup method for each plataform type.
        switch ($type) {
            case 'minecraft':
                return $this->lookupMinecraft($request);
            case 'steam':
                return $this->lookupSteam($request);
            case 'xbl':
                return $this->lookupXBL($request);
            default:
                return response()->json(['error' => 'Unsupported type'], 400); // Handling unsupported type error in a better way than just 'die()'
        }
    }

    // Method to handle Minecraft lookup
    private function lookupMinecraft(Request $request)
    {
        $username = $request->get('username');
        $userId = $request->get('id');

        // Checking if username or userId is missing
        if (!$username && !$userId) {
            return response()->json(['error' => 'Username or ID is required'], 400);
        }

        // here I used terany operation beacuse of the simplicity of the condition
        $endpoint = $username ?
            "https://api.mojang.com/users/profiles/minecraft/{$username}" :
            "https://sessionserver.mojang.com/session/minecraft/profile/{$userId}";

        $cacheKey = 'minecraft_' . ($username ?: $userId); // Generating cache key

        // Caching the response for 10 minutes or retrieving from cache if available, I wasn't sure how long I should store it.
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($endpoint) {

            $response = $this->httpClient->get($endpoint);
            $match = json_decode($response->getBody()->getContents());


            return [
                'username' => $match->name,
                'id' => $match->id,
                'avatar' => "https://crafatar.com/avatars/{$match->id}"
            ];
        });
    }

    // Method to handle Steam lookup
    private function lookupSteam(Request $request)
    {
        $id = $request->get("id");
        if (!$id) {
            return response()->json(['error' => 'Steam only supports IDs'], 400);
        }

        $url = "https://ident.tebex.io/usernameservices/4/username/{$id}";

        $cacheKey = 'steam_' . $id;
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($url) {
            $response = $this->httpClient->get($url);
            $match = json_decode($response->getBody()->getContents());


            return [
                'username' => $match->username,
                'id' => $match->id,
                'avatar' => $match->meta->avatar
            ];
        });
    }

    // Method to handle Xbox Live lookup
    private function lookupXBL(Request $request)
    {
        $username = $request->get("username");
        $id = $request->get("id");

        if (!$username && !$id) {
            return response()->json(['error' => 'Username or ID is required'], 400);
        }

        $endpoint = $username ?
            "https://ident.tebex.io/usernameservices/3/username/{$username}?type=username" :
            "https://ident.tebex.io/usernameservices/3/username/{$id}";

        $cacheKey = 'xbl_' . ($username ?: $id);


        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($endpoint) {
            $response = $this->httpClient->get($endpoint);
            $profile = json_decode($response->getBody()->getContents());


            return [
                'username' => $profile->username,
                'id' => $profile->id,
                'avatar' => $profile->meta->avatar
            ];
        });
    }
}

/*
 NOTE TO THE REVIWER:
To refactor this controller I have Broken down the code into 3 smaller and more manageable methods for each plataform. I could have break down the code in 3 classes but I decided to keep it all together in one class but making it clean and more maintanable. Also I have added some caching implementation.
 I hope my code has sastified the requirments and I'm sure with the feedback and support from the team I can make even more improvements. 
 regards :) Renato  
*/