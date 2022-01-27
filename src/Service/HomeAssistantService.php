<?php

namespace App\Service;

use GuzzleHttp\Client;

class HomeAssistantService {
    protected string $homeAssistantUrl;
    protected string $authToken;

    public function __construct()
    {
        $this->homeAssistantUrl = $_ENV['HOME_ASSISTANT_URL'];
        $this->authToken = $_ENV['HOME_ASSISTANT_TOKEN'];
    }

    public function getClient(): Client
    {
        return new Client([
            'base_uri' =>  $this->homeAssistantUrl. '/api/',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->authToken,
            ],
        ]);
    }
}
