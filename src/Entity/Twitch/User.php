<?php

namespace App\Entity\Twitch;

use DateTime;
use GuzzleHttp\Psr7\Uri;

class User
{
    public string $id;
    public string $login;
    public string $display_name;
    public string $type;
    public string $broadcaster_type;
    public string $description;
    public Uri $profile_image_url;
    public Uri $offline_image_url;
    public int $view_count;
    public string $email;
    public DateTime $created_at;
}
