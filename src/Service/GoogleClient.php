<?php

namespace App\Service;

use Exception;
use GuzzleHttp\Client;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;

class GoogleClient
{

    protected KeyValueStore $keyValueStore;
    protected ClientRegistry $clientRegistry;
    protected LoggerInterface $logger;

    protected AccessToken $accessToken;

    public function __construct(KeyValueStore $keyValueStore, ClientRegistry $clientRegistry, LoggerInterface $logger)
    {
        $this->keyValueStore = $keyValueStore;
        $this->clientRegistry = $clientRegistry;
        $this->logger = $logger;

        $this->getAccessToken();
    }

    protected function getAccessToken()
    {
        $tkn = $this->keyValueStore->get("google_access_token");
        $this->logger->debug('Access token: ' . $tkn);
        if ($tkn == null) {
            throw new Exception("Google Account is not connected. Please visit /connect/google!");
        }

        $this->accessToken = $tkn;

        if ($this->accessToken->hasExpired()) {
            $this->logger->info('Access token for Google Client expired. Trying to refresh.');
            $refreshToken = $this->keyValueStore->get('google_refresh_token');
            $oauthClient = $this->clientRegistry->getClient('google');
            $newToken = $oauthClient->refreshAccessToken($refreshToken);
            $this->logger->info('New accessToken from refresh', [
                'access_token' => $newToken,
            ]);
            $this->keyValueStore->set('google_access_token', $newToken);
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
