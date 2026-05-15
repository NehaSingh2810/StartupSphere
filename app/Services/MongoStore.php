<?php

namespace App\Services;

use MongoDB\Client;
use MongoDB\Collection;
use Throwable;

class MongoStore
{
    private ?Client $client = null;
    private string $database;

    public function __construct()
    {
        $this->database = env('MONGODB_DATABASE', 'startupsphere');

        try {
            $this->client = new Client(env('MONGODB_URI', 'mongodb://127.0.0.1:27017'));
        } catch (Throwable) {
            $this->client = null;
        }
    }

    public function available(): bool
    {
        try {
            $this->client?->selectDatabase($this->database)->command(['ping' => 1]);

            return (bool) $this->client;
        } catch (Throwable) {
            return false;
        }
    }

    public function all(string $collection, array $fallback = [], array $filter = [], int $limit = 200): array
    {
        try {
            $mongo = $this->collection($collection);

            if ($mongo->countDocuments() === 0 && $fallback !== []) {
                $mongo->insertMany($fallback);
            }

            return array_map(fn ($item) => $this->normalize($item), iterator_to_array(
                $mongo->find($filter, ['limit' => $limit, 'sort' => ['created_at' => -1]])
            ));
        } catch (Throwable) {
            return $fallback;
        }
    }

    public function findOne(string $collection, array $filter, ?array $fallback = null): ?array
    {
        try {
            $item = $this->collection($collection)->findOne($filter);

            return $item ? $this->normalize($item) : $fallback;
        } catch (Throwable) {
            return $fallback;
        }
    }

    public function insert(string $collection, array $document): bool
    {
        try {
            $document['created_at'] = now()->toDateTimeString();
            $this->collection($collection)->insertOne($document);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function updateOne(string $collection, array $filter, array $data): bool
    {
        try {
            $data['updated_at'] = now()->toDateTimeString();
            $this->collection($collection)->updateOne($filter, ['$set' => $data]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function collection(string $name): Collection
    {
        return $this->client->selectCollection($this->database, $name);
    }

    private function normalize(object|array $item): array
    {
        $array = json_decode(json_encode($item), true) ?: [];

        if (isset($array['_id']['$oid'])) {
            $array['_id'] = $array['_id']['$oid'];
        }

        return $array;
    }
}
