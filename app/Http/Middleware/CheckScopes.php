<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Client;
use League\OAuth2\Server\ResourceServer;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class CheckScopes
{
    /**
     * The Resource Server instance.
     *
     * @var \League\OAuth2\Server\ResourceServer
    */
    protected $server;

    /**
     * Create a new middleware instance.
     *
     * @param  \League\OAuth2\Server\ResourceServer  $server
     * @return void
    */
    public function __construct(ResourceServer $server)
    {
        $this->server = $server;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
    */
    public function handle($request, Closure $next, $scope)
    {
        $psr = (new DiactorosFactory)->createRequest($request);
        $authorization = $request->header('Authorization');

        if ($authorization !== null && strpos($authorization, 'Basic') === 0) {
            $username = $request->getUser();
            $client = new Client();
            $clientScopes = $client->retrieveClientScopes($username);
        } else if ($authorization !== null && strpos($authorization, 'Bearer') === 0) {

            $psr = $this->server->validateAuthenticatedRequest($psr);
            $clientScopes = $this->validateScopes($psr);

        }

        if(!in_array('all',$clientScopes) && !in_array($scope,$clientScopes)){
            return response([
                "code" => 403,
                "message" => "Invalid client scope(s)"
            ], 403);
        }
        
        return $next($request);
    }

    /**
     * Validate the scopes on the incoming request.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $psr
     * @return array
     */
    protected function validateScopes($psr)
    {
        $tokenScopes = $psr->getAttribute('oauth_scopes');

        $client_id = $psr->getAttribute('oauth_client_id');
        $scopes = Client::select('scopes')->where('api_basic_key', $client_id)->first();
        $clientScopes = json_decode($scopes->getAttribute('scopes'));
        foreach ($tokenScopes as $key => $val) {
            if (!in_array($val, $clientScopes)) {
                unset($tokenScopes[$key]);
            }
        }
        return array_values($tokenScopes);

    }
}
