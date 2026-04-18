<?php

namespace App\OAuth;

use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository as PassportClientRepository;

class ClientRepository extends PassportClientRepository
{
    public function findActive(string|int $id): ?Client
    {
        $client = parent::findActive($id);

        if ($client && $client->expires_at && now()->isAfter($client->expires_at)) {
            return null;
        }

        return $client;
    }
}
