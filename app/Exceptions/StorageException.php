<?php

namespace App\Exceptions;

use Exception;

/**
 * @codeCoverageIgnore
 */
class StorageException extends Exception
{
    private const MESSAGE = 'Storage access exception: ';

    public function __construct(string $message, $code = \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR) {
        parent::__construct(self::MESSAGE . $message, $code);
    }
}