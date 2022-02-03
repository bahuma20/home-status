<?php

namespace App\Controller;

use App\Entity\Alert;
use App\Error\EntityNotFoundException;
use App\Service\AlertService;
use App\Service\KeyValueStore;
use App\Service\TwitchClient;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
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
    public function webhook(Request $request, TwitchClient $twitchClient, AlertService $alertService): Response
    {
        // Verify signature
        $this->logger->debug(print_r($request->headers, true));

        $messageSignature = $request->headers->get('twitch-eventsub-message-signature');
        $messageId = $request->headers->get('twitch-eventsub-message-id');
        $messageTimestamp = $request->headers->get('twitch-eventsub-message-timestamp');

        $secret = $_ENV['TWITCH_WEBHOOK_SECRET'];

        $message = $messageId . $messageTimestamp . $request->getContent();
        $hmac = 'sha256=' . hash_hmac('sha256', $message, $secret, FALSE);

        if ($hmac !== $messageSignature) {
            throw new UnauthorizedHttpException('Twitch Signature', 'Twitch Signature could not be verified');
        }


        $data = json_decode($request->getContent());
        switch ($request->headers->get('twitch-eventsub-message-type')) {
            case 'webhook_callback_verification':
                return new Response($data->challenge);
                break;
            case 'revocation':
                $this->logger->warning('Subscripton of type "' . $data->subscription->type . '" to "' . $data->subscription->condition->broadcaster_user_id . '" was revoked for reason "' . $data->subscription->status);
                return new Response();
                break;

            case 'notification':
                switch ($data->subscription->type) {
                    case 'stream.online':
                        $alert = new Alert();
                        $alert->id = 'twitch_' . $data->event->broadcaster_user_id;
                        $alert->title = $data->event->broadcaster_user_name . ' ist live';
                        $alert->body = 'No body';
                        $alert->icon = 'twitch';
                        $alertService->add($alert);
                        return new Response('Alert created');
                        break;
                    case 'stream.offline':
                        try {
                            $alertService->delete('twitch_' . $data->event->broadcaster_user_id);
                        } catch (EntityNotFoundException $e) {
                            // If it does not exist, it's ok :D
                        }
                        return new Response('Alert removed');
                        break;
                    default:
                        throw new BadRequestHttpException('The subscription type "' . $data->subscription->type . '" is not supported.');
                        break;
                }
                break;

            default:
                throw new BadRequestHttpException('Unknown eventsub-message-type');
        }
    }

    #[Route('/test-twitch', name: 'testtwitch', methods: ['GET'])]
    public function test(TwitchClient $twitchClient)
    {
        $subscriptions = $twitchClient->getEnabledEventSubscriptions();

        $userIds = $twitchClient->getFollowedUsersIds();

        // Delete orphaned subscriptions
        $orphanedSubscriptions = array_filter($subscriptions, function ($subscription) use ($userIds) {
            return in_array($subscription->type, ['stream.offline', 'stream.online']) && !in_array($subscription->condition->broadcaster_user_id, $userIds);
        });

        foreach ($orphanedSubscriptions as $subscription) {
            $twitchClient->deleteEventSubscription($subscription->id);
        }


        // Create missing subscriptions
        $missingSubscriptions = [];

        foreach ($userIds as $userId) {
            $onlineSubscriptions = array_filter($subscriptions, function ($subscription) use ($userId) {
                return $subscription->condition->broadcaster_user_id == $userId && $subscription->type == "stream.online";
            });

            $offlineSubscriptions = array_filter($subscriptions, function ($subscription) use ($userId) {
                return $subscription->condition->broadcaster_user_id == $userId && $subscription->type == "stream.offline";
            });

            if (count($onlineSubscriptions) == 0) {
                $missingSubscriptions[$userId][] = 'online';
            }

            if (count($offlineSubscriptions) == 0) {
                $missingSubscriptions[$userId][] = 'offline';
            }
        }

        foreach ($missingSubscriptions as $userId => $types) {
            if (in_array('online', $types)) {
                $twitchClient->subscribeToStreamOnline($userId, 'twitch_webhook');
            }

            if (in_array('offline', $types)) {
                $twitchClient->subscribeToStreamOffline($userId, 'twitch_webhook');
            }
        }
    }
}
