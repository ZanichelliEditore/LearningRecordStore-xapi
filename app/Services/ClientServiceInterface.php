<?php

namespace App\Services;

interface ClientServiceInterface
{
    public function store(string $lrs_id, string $oauth_name,  string $authority_name= '', 
    string $authority_mbox = '',
    array $scopes = []);
}