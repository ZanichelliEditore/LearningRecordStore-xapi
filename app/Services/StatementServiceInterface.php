<?php

namespace App\Services;

interface StatementServiceInterface
{
    public function store(array $statements, string $folder);

    public function read(string $folder, bool $delete, bool $backup);

}
