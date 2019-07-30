<?php

namespace App\Locker;

class Helper
{
    
    /**
     * Return response content
     *
     * @param string $message
     * @return Response
     */
    static function getResponse($message, $code = null, $success = false)
    {
        $code = $code ?: \Illuminate\Http\Response::HTTP_BAD_REQUEST;
        $content = [
            "error" => !$success,
            "success" => $success,
            "message" => [$message],
            "code" => $code
        ];

        return response($content, $code);
    }

    static function getUrlPage() {
        return env('APP_URL', 'http://localhost:8085') . "/data/xAPI/statements";
    }
}