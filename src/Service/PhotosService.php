<?php

namespace App\Service;

use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class PhotosService implements LoggerAwareInterface
{
    const BASE_URI = 'https://photoslibrary.googleapis.com/v1/';

    protected Client $client;
    protected KeyValueStore $keyValueStore;
    protected LoggerInterface $logger;

    public function __construct(GoogleClient $googleClient, KeyValueStore $keyValueStore)
    {
        $this->client = $googleClient->getClient(self::BASE_URI);
        $this->keyValueStore = $keyValueStore;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    protected function getMediaItems($albumId, $pageToken = FALSE, $counter = 1)
    {
        $body = [
            'albumId' => $albumId,
            'pageSize' => 100,
        ];

        if ($pageToken) {
            $body['pageToken'] = $pageToken;
        }

        try {
            $response = $this->client->post(self::BASE_URI . 'mediaItems:search', [
                'body' => json_encode($body)
            ]);
        } catch (Exception $e) {
            print_r($e->getMessage());
            throw $e;
        }


        $this->logger->info('Request #' . $counter . ' finished');

        $data = json_decode($response->getBody()->getContents());

        $items = [];

        foreach ($data->mediaItems as $item) {
            // Filter out videos
            if (!str_starts_with($item->mimeType, 'image')) {
                continue;
            }

            // Filter out images in portrait mode
            if ((int)$item->mediaMetadata->height > (int)$item->mediaMetadata->width) {
                continue;
            }

            $items[] = $item;
        }

        if (property_exists($data, 'nextPageToken')) {

            $items = array_merge($items, $this->getMediaItems($albumId, $data->nextPageToken, $counter + 1));
        }

        return $items;
    }


    /**
     * Stores real photos, that are not art and groups them by date.
     *
     * @return void
     */
    public function storePeoplePhotos()
    {
        $this->logger->info('Start storing people photos');
        $items = $this->getMediaItems('AG27JmVeTe_0jqsY8ZNuXS8DY9xpfMV7C-Ag8qw8a1WCPunRzCiIMpoFoffdB6I9RY5JvRk3QPdz');

        $groupedItems = [
            'week' => [],
            'month' => [],
            'year' => [],
            'twoyear' => [],
            'threeyear' => [],
            'fouryear' => [],
            'more' => [],
        ];

        foreach ($items as $item) {
            $date = \DateTime::createFromFormat(DATE_RFC3339, $item->mediaMetadata->creationTime);
            $now = new DateTime();
            $diff = $date->diff($now);

            if ($diff->days < 7) {
                $groupedItems['week'][] = $item->id;
            } elseif ($diff->days < 30) {
                $groupedItems['month'][] = $item->id;
            } elseif ($diff->days < 365) {
                $groupedItems['year'][] = $item->id;
            } elseif ($diff->days < (365 * 2)) {
                $groupedItems['twoyear'][] = $item->id;
            } elseif ($diff->days < (365 * 3)) {
                $groupedItems['threeyear'][] = $item->id;
            } elseif ($diff->days < (365 * 4)) {
                $groupedItems['fouryear'][] = $item->id;
            } else {
                $groupedItems['more'][] = $item->id;
            }
        }

        $this->keyValueStore->set('photos_people', $groupedItems);
        $this->logger->info('Finished storing people photos');
    }

    /**
     * Stores all photos from art albums
     *
     * @return void
     */
    public function storeArtPhotos()
    {
        $this->logger->info('Start storing art photos');
        $itemsBackgrounds = $this->getMediaItems('AG27JmXbcN6eThhKd6O1S1C5zYWAmosZT6FGBTXEOEKlztkANhpHybm9eU4sZJy-ZzISbkaUjwd4');

        $itemsDisplates = $this->getMediaItems('AG27JmXhRzBsNtipPgmTxFpw4QAioW2nA9X0pufW8CRoEKO2JqujMIMhGHfAWYo3R5ZxsF-ANkEB');

        $items = $itemsBackgrounds + $itemsDisplates;

        $outputItems = [];

        foreach ($items as $item) {
            $outputItems[] = $item->id;
        }

        $this->keyValueStore->set('photos_art', $outputItems);
        $this->logger->info('Finished storing art photos');
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function getMediaItem(string $mediaItemId): object
    {
        try {
            $response = $this->client->get('mediaItems/' . $mediaItemId);
            return json_decode($response->getBody()->getContents());
        } catch (Exception $e) {
            print_r($e->getMessage());
            throw $e;
        }
    }
}
