<?php

namespace App\Service;

use App\Entity\Alert;
use App\Error\EntityNotFoundException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class AlertService
{
    const STORE_KEY = 'alerts';

    private KeyValueStore $keyValueStore;
    private HubInterface $hub;

    public function __construct(KeyValueStore $keyValueStore, HubInterface $hub)
    {
        $this->keyValueStore = $keyValueStore;
        $this->hub = $hub;
    }

    /**
     * @return Alert[]
     */
    public function list(): array
    {
        return $this->keyValueStore->get(self::STORE_KEY) ?: [];
    }

    /**
     * @throws EntityNotFoundException
     */
    public function findById(string $id): Alert
    {
        $alerts = $this->list();

        $alertKey = $this->getIndexById($alerts, $id);

        return $alerts[$alertKey];
    }

    public function add(Alert $alert): void
    {
        $alerts = $this->list();
        $alerts[] = $alert;
        $this->keyValueStore->set(self::STORE_KEY, $alerts);
        $this->notify();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $id): void
    {
        $alerts = $this->list();
        $alertKey = $this->getIndexById($alerts, $id);

        unset($alerts[$alertKey]);

        $this->keyValueStore->set(self::STORE_KEY, $alerts);
        $this->notify();
    }

    /**
     * @param Alert[] $alerts
     * @param string $id
     * @return string
     * @throws EntityNotFoundException
     */
    protected function getIndexById(array $alerts, string $id): string
    {
        $alertKey = false;

        foreach ($alerts as $key => $alert) {
            if ($alert->id == $id) {
                $alertKey = $key;
                break;
            }
        }

        if ($alertKey === false) {
            throw new EntityNotFoundException('Could not find an alert with this id.');
        }

        return $alertKey;
    }

    protected function notify(): void
    {
        $update = new Update(
            'alerts',
            json_encode($this->list())
        );

        $this->hub->publish($update);
    }
}
