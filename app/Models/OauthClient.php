<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * @codeCoverageIgnore
 */
class OauthClient extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;

    protected $table = "oauth_clients";

    /**
     * Get the client record associated with oauth clients.
    */
    public function clients()
    {
        return $this->hasOne('App\Models\Client', 'api_basic_key');
    }

    /**
     * User validation
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function validateUser(string $username,string $password) {
        $user = OauthClient::find($username);

        if (isset($user)) {
            $user = $user->getAttributes();
            $pwd = $user['secret'];
            if ($pwd === $password) {
                return true;
            }
        }
        
        return false;
    }

}
