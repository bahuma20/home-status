<?php

namespace App\Controller;

use App\Service\KeyValueStore;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google_start', methods: ['GET'])]
    public function connectAction(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect([
                'email',
                'https://www.googleapis.com/auth/tasks.readonly',
                'https://www.googleapis.com/auth/photoslibrary.readonly',
            ], [
                'prompt' => 'consent',
                'access_type' => 'offline',
            ]);
    }

    #[Route('/connect/google/callback', name: 'connect_google_callback', methods: ['GET'])]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry, KeyValueStore $keyValueStore)
    {
        $client = $clientRegistry->getClient('google');
        $token = $client->getAccessToken();

        $keyValueStore->set('google_access_token', $token);
        $keyValueStore->set('google_refresh_token', $token->getRefreshToken());

        return new Response('Successfully linked google account');
    }
}
