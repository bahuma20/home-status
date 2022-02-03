<?php

namespace App\Controller;

use App\Service\KeyValueStore;
use App\Service\PhotosService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PhotosController extends AbstractController
{
    protected PhotosService $photosService;
    protected KeyValueStore $keyValueStore;

    public function __construct(PhotosService $photosService, KeyValueStore $keyValueStore)
    {
        $this->photosService = $photosService;
        $this->keyValueStore = $keyValueStore;
    }

    /**
     * Loads all available photos in batch, and stores them in the key value store.
     *
     * @return Response
     */
    #[Route('/api/photos/sync', name: 'photos_sync', methods: ['GET'])]
    public function index(): Response
    {
        $this->photosService->storePeoplePhotos();

        $this->photosService->storeArtPhotos();

        return new Response('Photos were added to index.');
    }

    #[Route('/api/photos', name: 'photos_get', methods: ['GET'])]
    public function getPhoto(): Response
    {
        $types = ['art', 'people'];

        $type = $types[rand(0, count($types)-1)];

        $photos = $this->keyValueStore->get('photos_' . $type);

        if ($type == "people") {
            $rand = rand(0,100);
            if ($rand < 5) {
                $selector = 'more';
            } elseif ($rand < 10) {
                $selector = 'fouryear';
            } elseif ($rand < 20) {
                $selector = 'threeyear';
            } elseif ($rand < 35) {
                $selector = 'twoyear';
            } elseif ($rand < 55) {
                $selector = 'year';
            } elseif ($rand < 75) {
                $selector = 'month';
            } else {
                $selector = 'week';
            }

            if (count($photos[$selector]) == 0) {
                foreach ($photos as $sel => $group) {
                    if (count($group) !== 0) {
                        $selector = $sel;
                        break;
                    }
                }
            }

            $randomPhotoId = $photos[$selector][rand(0, count($photos[$selector]) - 1)];
        } else {
            $randomPhotoId = $photos[rand(0, count($photos))];
        }

        $mediaItem = $this->photosService->getMediaItem($randomPhotoId);
        $mediaItem->homeAppType = $type;

        return $this->json($mediaItem);
    }
}
