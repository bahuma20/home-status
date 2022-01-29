<?php

namespace App\Controller;

use App\Service\GoogleClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksController extends AbstractController
{
    protected Client $client;

    public function __construct(GoogleClient $googleClient)
    {
        $this->client = $googleClient->getClient('https://tasks.googleapis.com/tasks/v1/');
    }

    #[Route('/api/tasks', name: 'tasks', methods: ['GET'])]
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
