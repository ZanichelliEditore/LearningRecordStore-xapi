<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\OauthClient;
use Illuminate\Http\Response;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class BasicAuthentication
{
    /**
     * The guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;
    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth;
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $username = $request->getUser();
        $password = $request->getPassword();
        $user = new OauthClient();
        
        if (!isset($username) || !$user->validateUser($username, $password)) {
            return response([
                "code" => 401,
                "message" => "Unauthorized request."
            ], 401);
        } 

        return $next($request);
    }

}
