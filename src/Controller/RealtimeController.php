<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mercure\HubRegistry;
use Symfony\Component\Routing\Annotation\Route;

class RealtimeController extends AbstractController
{
    protected HubRegistry $hubRegistry;

    public function __construct(HubRegistry $hubRegistry)
    {
        $this->hubRegistry = $hubRegistry;
    }

    #[Route('/api/realtime/subscription-url', name: 'realtime_subscription-url', methods: ['GET'])]
    public function getSubscriptionUrl()
    {
        $url = $this->hubRegistry->getHub()->getPublicUrl();

        return $this->json([
            'url' => $url,
        ]);
    }
}
