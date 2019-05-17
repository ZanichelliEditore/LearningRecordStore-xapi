<?php

namespace App\Repositories;

interface StatementRepositoryInterface
{
    public function store(string $statements, string $folder);

    public function all(string $folder, $limit, $verb, int $page);

    public function find(string $folder, string $id);
}