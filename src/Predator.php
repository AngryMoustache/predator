<?php

namespace AngryMoustache\Predator;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class Predator
{
    public Client $client;

    /**
     * Ready the client for use
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client([
            'verify' => false,
            'base_uri' => config('predator.base_uri'),
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token(),
            ],
        ]);
    }

    /**
     * Get the token to use if not set yet
     * @param bool $force Force generation of new token
     * @return string
     */
    public function token($force = false)
    {
        if ($force) {
            Cache::forget('predator-token');
        }

        return Cache::rememberForever('predator-token', function () {
            return (new Client([
                'verify' => false,
                'base_uri' => config('predator.base_uri'),
                'form_params' => config('predator.auth'),
                'headers' => ['Accept' => 'application/json'],
            ]))
                ->post('token')
                ->getBody()
                ->getContents();
        });
    }

    /**
     * Store an item in the database
     * @param object $item The object to save
     * @param ?string $type The item type, used later to filter on
     * @return object
     */
    public function store($item, $type = null)
    {
        $data = optional($item)->toPredator() ?? $item;

        return $this->post('store', ['form_params' => [
            'item_type' => $type ?? get_class($item),
            'item_id' => $item->id,
            'data' => $data,
        ]]);
    }

    /**
     * Fetch a filtered response from the server.
     * @param string $type The item type to filter on
     * @param array $filters The filters to use
     * @param array $weights The weights to use
     * @param array $orderBy The ordering to use
     * @param array $weights The fields to return
     * @return Collection
     */
    public function filter($type, $filters = [], $weights = [], $orderBy = [], $fields = [])
    {
        return collect($this->post('filter', ['form_params' => [
            'item_type' => $type,
            'weights' => $weights,
            'orderBy' => $orderBy,
            'filters' => $filters,
            'fields' => $fields,
        ]]));
    }

    /**
     * Remove all data of a certain type
     * @param string $type The item type to truncate
     * @return Collection
     */
    public function truncate($type)
    {
        return collect($this->post('truncate', ['form_params' => [
            'item_type' => $type,
        ]]));
    }

    /**
     * Start a new filter object.
     * @param string|array $types The item types to filter on
     * @return PredatorFilter
     */
    public function query($types)
    {
        return PredatorFilter::query(Arr::wrap($types));
    }

    /**
     * Attempt a post request and fetch a token if needed
     * @param string $uri URI to pass to Guzzle
     * @param array $options Options to pass to Guzzle
     * @param bool $alreadyForced Prevent infinite looping
     * @return object
     */
    private function post($uri, $options = [], $alreadyForced = false)
    {
        try {
            $response = $this->client->post($uri, $options);
        } catch (\Throwable $th) {
            if ($alreadyForced || ! $th->getCode() === 401) {
                throw $th;
            }

            $this->client = new Client([
                'verify' => false,
                'base_uri' => config('predator.base_uri'),
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token(true),
                ],
            ]);

            return $this->post($uri, $options, true);
        }

        return json_decode($response->getBody()->getContents());
    }
}
