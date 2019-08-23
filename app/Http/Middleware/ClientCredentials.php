<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Laravel\Passport\Http\Middleware\CheckClientCredentials;

class ClientCredentials extends CheckClientCredentials
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$scopes
     * @return mixed
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        $psr = (new DiactorosFactory)->createRequest($request);

        try {
            $psr = $this->server->validateAuthenticatedRequest($psr);
            } catch (OAuthServerException $e) {
            Log::error('Error message: ' . (string) $e);
            return response()->json((['code' => 401, 'message' => 'Unauthorized request.']), 401);            
        }

        return $next($request);
    }

}