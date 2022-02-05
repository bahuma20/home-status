<?php

namespace App\Service;

use App\Entity\Twitch\EventSubscription;
use App\Entity\Twitch\EventSubscriptionCondition;
use App\Entity\Twitch\EventSubscriptionTransport;
use App\Entity\Twitch\User;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Token\AccessToken;
use NewTwitchApi\HelixGuzzleClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use TwitchApi\TwitchApi;

class TwitchClient
{

    protected AccessToken $accessToken;
    protected string $appAccessToken;
    protected TwitchApi $twitchApi;
    protected RouterInterface $router;

    public function __construct(KeyValueStore $keyValueStore, ClientRegistry $clientRegistry, RouterInterface $router)
    {
        $tkn = $keyValueStore->get("twitch_access_token");
        if ($tkn == null) {
            throw new Exception("Twitch Account is not connected. Please visit /connect/twitch!");
        }

        $this->accessToken = $tkn;

        if ($this->accessToken->hasExpired()) {
            $refreshToken = $keyValueStore->get('twitch_refresh_token');
            $oauthClient = $clientRegistry->getClient('twitch');
            $newToken = $oauthClient->refreshAccessToken($refreshToken);
            $keyValueStore->set('twitch_access_token', $newToken);
            $this->accessToken = $newToken;
        }
        $this->router = $router;
    }

    public function getApi(): TwitchApi
    {
        if (empty($this->twitchApi)) {
            $helixGuzzleClient = new HelixGuzzleClient($_ENV['OAUTH_TWITCH_ID']);

            $this->twitchApi = new TwitchApi($helixGuzzleClient, $_ENV['OAUTH_TWITCH_ID'], $_ENV['OAUTH_TWITCH_SECRET']);
        }

        return $this->twitchApi;
    }

    /**
     * @throws GuzzleException
     */
    public function getFollowedUsersIds(): array
    {
        $me = $this->getMe();
        $response = $this->getApi()->getUsersApi()->getUsersFollows($this->accessToken->getToken(), $me->id);
        $data = json_decode($response->getBody()->getContents());
        $data = $data->data;

        return array_map(function ($entry) {
            return $entry->to_id;
        }, $data);
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function getMe(): User
    {
        $response = $this->getApi()->getUsersApi()->getUserByAccessToken($this->accessToken->getToken());
        $data = json_decode($response->getBody()->getContents());
        $data = $data->data[0];

        $user = new User();

        $user->id = $data->id;
        $user->login = $data->login;
        $user->display_name = $data->display_name;
        $user->type = $data->type;
        $user->broadcaster_type = $data->broadcaster_type;
        $user->description = $data->description;
        $user->profile_image_url = new Uri($data->profile_image_url);
        $user->offline_image_url = new Uri($data->offline_image_url);
        $user->view_count = $data->view_count;
        $user->email = $data->email;
        $user->created_at = new DateTime($data->created_at);

        return $user;
    }

    /**
     * @throws GuzzleException
     */
    public function getAppAccessToken(): string
    {
        if (empty($this->appAccessToken)) {
            $response = $this->twitchApi->getOauthApi()->getAppAccessToken();
            $data = json_decode($response->getBody()->getContents());
            $this->appAccessToken = $data->access_token;
        }

        return $this->appAccessToken;
    }

    /**
     * @return EventSubscription[]
     * @throws GuzzleException
     * @throws Exception
     */
    public function getEnabledEventSubscriptions(): array
    {
        $response = $this->getApi()->getEventSubApi()->getEventSubSubscription($this->getAppAccessToken());
        $data = json_decode($response->getBody()->getContents());
        $data = $data->data;

        return array_map(function ($entry) {
            $subscription = new EventSubscription();
            $subscription->id = $entry->id;
            $subscription->status = $entry->status;
            $subscription->type = $entry->type;
            $subscription->version = $entry->version;
            $subscription->condition = new EventSubscriptionCondition();
            $subscription->condition->broadcaster_user_id = $entry->condition->broadcaster_user_id;
            $subscription->created_at = new DateTime($entry->created_at);
            $subscription->transport = new EventSubscriptionTransport();
            $subscription->transport->method = $entry->transport->method;
            $subscription->transport->callback = $entry->transport->callback;
            $subscription->cost = $entry->cost;
            return $subscription;
        }, $data);
    }

    /**
     * @throws GuzzleException
     */
    public function subscribeToStreamOnline(int $userId, string $routeId, array $routeParameters = []): void
    {
        $webhookUrl = $this->router->generate($routeId, $routeParameters, UrlGeneratorInterface::ABSOLUTE_URL);
        var_dump($webhookUrl);
        $this->getApi()->getEventSubApi()->subscribeToStreamOnline($this->getAppAccessToken(), $_ENV['TWITCH_WEBHOOK_SECRET'], $webhookUrl, $userId);
    }

    /**
     * @throws GuzzleException
     */
    public function subscribeToStreamOffline(int $userId, string $routeId, array $routeParameters = []): void
    {
        $webhookUrl = $this->router->generate($routeId, $routeParameters, UrlGeneratorInterface::ABSOLUTE_URL);
        var_dump($webhookUrl);
        $this->getApi()->getEventSubApi()->subscribeToStreamOffline($this->getAppAccessToken(), $_ENV['TWITCH_WEBHOOK_SECRET'], $webhookUrl, $userId);
    }

    public function deleteEventSubscription($subscriptionId): void
    {
        $this->getApi()->getEventSubApi()->deleteEventSubSubscription($this->getAppAccessToken(), $subscriptionId);
    }
}
