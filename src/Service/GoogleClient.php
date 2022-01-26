<?php

namespace App\Service;

use GuzzleHttp\Client;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Token\AccessToken;

class GoogleClient
{

    protected AccessToken $accessToken;

    public function __construct(KeyValueStore $keyValueStore, ClientRegistry $clientRegistry)
    {
        $tkn = $keyValueStore->get("google_access_token");
        if ($tkn == null) {
            throw new \Exception("Google Account is not connected. Please visit /connect/google!");
        }

        $this->accessToken = $tkn;

        if ($this->accessToken->hasExpired()) {
            $refreshToken = $keyValueStore->get('google_refresh_token');
            $oauthClient = $clientRegistry->getClient('google');
            $newToken = $oauthClient->refreshAccessToken($refreshToken);
            $keyValueStore->set('google_access_token', $newToken->getToken());
            $this->accessToken = $newToken;
        }
    }

    public function getClient($baseUri): Client
    {
        return new Client([
            'base_uri' => $baseUri,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken->getToken(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }
}
