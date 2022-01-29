<?php

namespace App\Entity;

use DateTime;
use JsonSerializable;

class Alert implements JsonSerializable
{
    public string $id;
    public string $title;
    public string $body;
    public string $icon;
    public DateTime $created;

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'icon' => $this->icon,
            'created' => $this->created->format('c'),
        ];
    }
}
