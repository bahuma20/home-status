<?php

namespace App\Controller;

use App\Entity\Alert;
use App\Error\EntityNotFoundException;
use App\Service\AlertService;
use App\Service\KeyValueStore;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AlertsController extends AbstractController
{
    private AlertService $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    #[Route('/api/alerts', name: 'alerts_list', methods: ['GET'])]
    public function index(KeyValueStore $keyValueStore): Response
    {
        return $this->json($this->alertService->list());
    }

    #[Route('/api/alerts', name: 'alerts_create', methods: ['POST'])]
    public function createAlert(Request $request): Response
    {
        try {
            $this->alertService->findById($request->get('id'));
            throw new BadRequestException('An alert with this id already exists');
        } catch (EntityNotFoundException $e) {
        }

        $alert = new Alert();
        $alert->id = $request->get('id');
        $alert->title = $request->get('title');
        $alert->body = $request->get('body');
        $alert->icon = $request->get('icon');
        $alert->created = new DateTime();

        $this->alertService->add($alert);

        return new Response('Alert created', Response::HTTP_CREATED);
    }

    #[Route('/api/alerts/{id}', name: 'alerts_delete', methods: ['DELETE'])]
    public function deleteAlert(string $id): Response
    {
        try {
            $this->alertService->delete($id);
            return new Response('Alert deleted');
        } catch (EntityNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
