<?php

namespace App\Controller;

use App\Entity\Alert;
use App\Error\EntityNotFoundException;
use App\Service\AlertService;
use App\Service\KeyValueStore;
use App\Service\TwitchClient;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
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

    /**
     * @throws Exception
     */
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
                        $alert = new Alert(
                            'twitch_' . $data->event->broadcaster_user_id,
                            $data->event->broadcaster_user_name . ' ist live',
                            new DateTime($data->event->started_at)
                        );

                        $alert->body = 'No body';
                        $alert->icon = 'twitch';
                        $alert->priority = Alert::PRIORITY_LOW;
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

    /**
     * Synchronize followed users and creates subscriptions for stream online and offline events.
     *
     * @param TwitchClient $twitchClient
     * @return Response
     * @throws GuzzleException
     */
    #[Route('/api/twitch/sync', name: 'twitch_sync', methods: ['GET'])]
    public function sync(TwitchClient $twitchClient): Response
    {
        $subscriptions = $twitchClient->getEnabledEventSubscriptions();

        $userIds = $twitchClient->getFollowedUsersIds();

        // Delete orphaned subscriptions
        $orphanedSubscriptions = array_filter($subscriptions, function ($subscription) use ($userIds) {
            return in_array($subscription->type, ['stream.offline', 'stream.online']) && !in_array($subscription->condition->broadcaster_user_id, $userIds);
        });

        $this->logger->info(sprintf('Found %s orphaned subscriptions', count($orphanedSubscriptions)));

        foreach ($orphanedSubscriptions as $subscription) {
            $this->logger->info(sprintf('Delete subscription with id "%s"', $subscription->id));
            $twitchClient->deleteEventSubscription($subscription->id);
        }


        // Create missing subscriptions
        $missingSubscriptions = [];

        foreach ($userIds as $userId) {
            $onlineSubscriptions = array_filter($subscriptions, function ($subscription) use ($userId) {
                $this->logger->info(sprintf('Create online subscription for user "%s"', $subscription->condition->broadcaster_user_id));
                return $subscription->condition->broadcaster_user_id == $userId && $subscription->type == "stream.online";
            });

            $offlineSubscriptions = array_filter($subscriptions, function ($subscription) use ($userId) {
                $this->logger->info(sprintf('Create offline subscription for user "%s"', $subscription->condition->broadcaster_user_id));
                return $subscription->condition->broadcaster_user_id == $userId && $subscription->type == "stream.offline";
            });

            if (count($onlineSubscriptions) == 0) {
                $missingSubscriptions[$userId][] = 'online';
            }

            if (count($offlineSubscriptions) == 0) {
                $missingSubscriptions[$userId][] = 'offline';
            }
        }

        $this->logger->info(sprintf('Found %s missing subscriptions', count($missingSubscriptions)));

        foreach ($missingSubscriptions as $userId => $types) {
            if (in_array('online', $types)) {
                $twitchClient->subscribeToStreamOnline($userId, 'twitch_webhook');
            }

            if (in_array('offline', $types)) {
                $twitchClient->subscribeToStreamOffline($userId, 'twitch_webhook');
            }
        }

        return new Response('Followers were synchronized and subscriptions created.');
    }
}
