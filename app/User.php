<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * @codeCoverageIgnore
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table = "oauth_clients";

    /**
     * User validation
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function validateUser(string $username,string $password) {
        $user = User::find($username);

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
