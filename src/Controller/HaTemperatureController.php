<?php

namespace App\Controller;

use App\Service\HomeAssistantService;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HaTemperatureController extends AbstractController
{

    protected Client $client;

    public function __construct(HomeAssistantService $homeAssistant)
    {
        $this->client = $homeAssistant->getClient();
    }

    #[Route('/api/ha/temperature/{entityId}', methods: ['GET'])]
    public function getTemperature(string $entityId): Response
    {
        $response = $this->client->get('states/sensor.' . $entityId);
        $data = json_decode($response->getBody()->getContents());

        return $this->json([
            'name' => $data->attributes->friendly_name,
            'temperature' => $data->state,
        ]);
    }
}
