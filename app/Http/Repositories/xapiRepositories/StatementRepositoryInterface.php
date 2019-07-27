<?php

namespace App\Http\Repositories\xapiRepositories;

interface StatementRepositoryInterface
{
    /**
     * Save statements in application folder
     *
     * @param string $statements
     * @param string $folder
     * @return boolean
    */
    public function store(string $statements, string $folder);

    public function all(string $folder, $limit, $verb, int $page);

    public function find(string $folder, string $id);
}