<?php

namespace App\Services;

use App\Models\Client;
use App\Constants\Scope;
use App\Models\OauthClient;
use Laravel\Passport\ClientRepository;

class ClientService implements ClientServiceInterface
{

    /**
     * The client repository instance.
     *
     * @var \Laravel\Passport\ClientRepository
     */
    protected $clientsRepository;

    public function __construct(ClientRepository $clients) {
        $this->clientsRepository = $clients;

    }


    public function store(string $lrs_id, string $oauth_name, string $authority_name= '', 
        string $authority_mbox = '',
        array $scopes = []){

        $authority_name = empty($authority_name) ? 'New client' : $authority_name;
        $authority_mbox = empty($authority_mbox) ? 'mbox:newclient@example.com' : $authority_mbox;
        $scopes = empty($scopes)? [Scope::STATEMENTS_READ, Scope::STATEMENTS_WRITE]: $scopes;

        $oauth_client = $this->clientsRepository->create(null,'user_'.$oauth_name,'http://localhost', false, true);

        $client = new Client();
        $client->lrs_id = $lrs_id;
        $client->api_basic_key = $oauth_client->id;
        $client->api_basic_secret = $oauth_client->secret;
        $client->authority_mbox = $authority_mbox;
        $client->authority_name = $authority_name;
        $client->scopes = json_encode($scopes);

        return $client->save();


    }

 
}