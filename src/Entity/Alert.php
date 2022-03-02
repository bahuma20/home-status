<?php

namespace App\Entity;

use App\Repository\AlertRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use GuzzleHttp\Psr7\Uri;
use JsonSerializable;

#[ORM\Entity(repositoryClass: AlertRepository::class)]
class Alert implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private $body;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $icon;

    #[ORM\Column(type: 'datetime')]
    private $created;

    #[ORM\Column(type: 'integer')]
    private $priority;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $url;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $expires;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getCreated(): ?DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getUrl(): ?Uri
    {
        return $this->url ? new Uri($this->url) : null;
    }

    public function setUrl(?Uri $url): self
    {
        $this->url = $url->__toString();

        return $this;
    }

    public function getExpires(): ?DateTimeInterface
    {
        return $this->expires;
    }

    public function setExpires(?DateTimeInterface $expires): self
    {
        $this->expires = $expires;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'body' => $this->getBody(),
            'icon' => $this->getIcon(),
            'created' => $this->getCreated()->format('c'),
            'priority' => $this->getPriority(),
            'url' => $this->getUrl()?->__toString(),
            'expires' => $this->getExpires()?->format('c'),
        ];
    }
}
