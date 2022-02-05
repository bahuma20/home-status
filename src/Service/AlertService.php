<?php

namespace App\Service;

use App\Entity\Alert;
use App\Error\EntityNotFoundException;
use App\Repository\AlertRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class AlertService
{
    protected HubInterface $hub;
    protected AlertRepository $alertRepository;
    protected ObjectManager $entityManager;

    public function __construct(HubInterface $hub, AlertRepository $alertRepository, ManagerRegistry $doctrine)
    {
        $this->hub = $hub;
        $this->alertRepository = $alertRepository;
        $this->entityManager = $doctrine->getManager();
    }

    /**
     * @return Alert[]
     */
    public function list(): array
    {
        return $this->alertRepository->findAll();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function findById(string $id): Alert
    {
        $alert = $this->alertRepository->findOneBy([
            'id' => $id,
        ]);

        if (!$alert) {
            throw new EntityNotFoundException('No alert with id "' . $id . '" found.');
        }

        return $alert;
    }

    public function add(Alert $alert): void
    {
        $this->entityManager->persist($alert);
        $this->entityManager->flush();
        $this->notify();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $id): void
    {
        $alert = $this->findById($id);
        $this->entityManager->remove($alert);
        $this->entityManager->flush();
        $this->notify();
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
