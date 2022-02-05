<?php

namespace App\Model\Twitch;

use DateTime;

class EventSubscription
{
    public string $id;
    public string $status;
    public string $type;
    public string $version;
    public EventSubscriptionCondition $condition;
    public DateTime $created_at;
    public EventSubscriptionTransport $transport;
    public int $cost;
}
