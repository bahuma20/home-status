<?php

namespace App\Controller;

use App\Service\KeyValueStore;
use App\Service\TwitchClient;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TwitchController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/connect/twitch', name: 'connect_twitch_start', methods: ['GET'])]
    public function connectAction(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('twitch')
            ->redirect([
                'openid',
                'user:read:email',
                'user:read:follows',
            ]);
    }

    #[Route('/connect/twitch/callback', name: 'connect_twitch_callback', methods: ['GET'])]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry, KeyValueStore $keyValueStore)
    {
        $client = $clientRegistry->getClient('twitch');
        $token = $client->getAccessToken();

        $keyValueStore->set('twitch_access_token', $token);
        $keyValueStore->set('twitch_refresh_token', $token->getRefreshToken());

        return new Response('Successfully linked twitch account');
    }

    #[Route('/api/twitch/webhook', name: 'twitch_webhook', methods: ['POST'])]
    public function webhook(Request $request)
    {
        $this->logger->debug(print_r($request->headers, true));
        $this->logger->debug($request->getContent());
    }

    #[Route('/test-twitch', name: 'testtwitch', methods: ['GET'])]
    public function test(TwitchClient $twitchClient)
    {
        $twitchClient->getEnabledEventSubscriptions();

        $userIds = $twitchClient->getFollowedUsersIds();

        $twitchClient->subscribeToStreamOnline($userIds[0], 'twitch_webhook');

    }
}
