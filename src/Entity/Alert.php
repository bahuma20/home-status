<?php

namespace App\Entity;

use DateTime;
use JsonSerializable;

class Alert implements JsonSerializable
{
    const PRIORITY_LOW = 0;
    const PRIORITY_MEDIUM = 3;
    const PRIORITY_HIGH = 5;

    public string $id;
    public string $title;
    public ?string $body = NULL;
    public ?string $icon = NULL;
    public DateTime $created;
    public int $priority = self::PRIORITY_MEDIUM;
    public ?string $url = NULL;

    /**
     * @param string $id
     * @param string $title
     * @param DateTime $created
     */
    public function __construct(string $id, string $title, DateTime $created)
    {
        $this->id = $id;
        $this->title = $title;
        $this->created = $created;
    }


    public function jsonSerialize()
    {
        return [
            'id' => $this->id ?: null,
            'title' => $this->title ?: null,
            'body' => $this->body,
            'icon' => $this->icon,
            'created' => $this->created->format('c'),
            'priority' => $this->priority,
            'url' => $this->url,
        ];
    }
}
