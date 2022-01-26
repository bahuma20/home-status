<?php

namespace App\Controller;

use App\Service\KeyValueStore;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksController extends AbstractController
{
    protected KeyValueStore $keyValueStore;

    protected Client $client;

    public function __construct(KeyValueStore $keyValueStore)
    {
        $this->keyValueStore = $keyValueStore;

        /** @var AccessToken $token */
        $token = $this->keyValueStore->get("google_token");

        $this->client = new Client([
            'base_uri' => 'https://tasks.googleapis.com/tasks/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $token->getToken(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    #[Route('/api/tasks', name: 'tasks')]
    public function index(): Response
    {
        try {
            $response = $this->client->get('lists/MDc3MDUzNzcxMjAxODgwNDczOTA6MDow/tasks');
            $body = json_decode($response->getBody()->getContents());

            return $this->json($body->items);
        } catch (ClientException $e) {
            print_r($e->getMessage());
            print_r($e->getResponse()->getBody()->getContents());
            throw $e;
        }
    }
}
