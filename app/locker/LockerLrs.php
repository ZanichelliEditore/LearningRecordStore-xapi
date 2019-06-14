<?php

namespace App\Locker;

use Exception;
use App\Models\Lrs;
use App\Models\Client;
use Lcobucci\JWT\Parser;
use App\Models\Authority;
use Laravel\Passport\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LockerLrs
{
    /**
     * Gets the Client/Lrs username and password from the OAuth authorization string.
     * @param String $authorization
     * @return [String] Formed of [Username, Password]
     */
    static function getUserPassFromOAuth($bearerToken)
    {

        $tokenId = (new Parser())->parse($bearerToken)->getHeader('jti');

        $client_id = Token::find($tokenId)->client_id;

        $client_secret = DB::table('oauth_clients')->where('id', $client_id)->get();
        $client_secret = $client_secret[0]->secret;

        return [$client_id, $client_secret];
    }

    /**
     * Gets the Client/Lrs username and password from the Basic Auth authorization string.
     * @param String $authorization
     * @return [String] Formed of [Username, Password]
     */
    static function getUserPassFromBAuth($request)
    {

        $username = json_decode('"' . $request->getUser() . '"');
        $password = json_decode('"' . $request->getPassword() . '"');
        return [$username, $password];
    }

    /**
     * Gets the username and password from the authorization string.
     * @param Request $request
     * @return [String] Formed of [Username, Password]
     */
    static function getUserPassFromAuth($request)
    {
        $authorization = $request->header('Authorization');
        $bearerToken = $request->bearerToken();

        if ($authorization !== null && strpos($authorization, 'Basic') === 0) {
            list($username, $password) = self::getUserPassFromBAuth($request);
        } else if ($authorization !== null && strpos($authorization, 'Bearer') === 0) {
            list($username, $password) = self::getUserPassFromOAuth($bearerToken);
        } else {
            throw new Exception('Invalid auth', 400);
        }
        return [$username, $password];
    }

    /**
     * Checks the authentication.
     * @param String $type The name of the model used to authenticate.
     * @param String $username
     * @param String $username
     * @return Model
     */
    static function getClient($username, $password)
    {
        return Client::where('api_basic_key', $username)
            ->where('api_basic_secret', $password)
            ->first();
    }

    /**
     * Gets the Lrs associated with the given username and password.
     * @param String $username
     * @param String $password
     * @return Lrs
     */
    static function getLrsFromUserPass($username, $password)
    {
        $client = self::getClient($username, $password);
        $lrs_id = (string)$client->lrs_id;
        $lrs = Lrs::where('_id', $lrs_id)->first();

        if (is_null($lrs)) {
            throw new Exception('Unauthorized request.', 401);
        }
        return $lrs;
    }

    /**
     * Gets the current LRS from the Authorization header.
     * @param Request $request
     * @return Lrs
     */
    static function getLrsFromAuth($request)
    {
        list($username, $password) = self::getUserPassFromAuth($request);

        return self::getLrsFromUserPass($username, $password);
    }

    /**
     * Gets the Lrs associated with the given username and password.
     * @param String $username
     * @param String $password
     * @return Authority
     */
    static function getAuthorityFromUserPass($username, $password)
    {
        $client = self::getClient($username, $password);

        $authority = is_null($client) ? null : new Authority($client->authority_name, $client->authority_mbox);
        if (is_null($authority)) {
            throw new Exception('Unauthorized request.', 401);
        }

        return $authority;
    }

    /**
     * Gets the current LRS from the Authorization header.
     * @param Request $request
     * @return Authority
     */
    static function getAuthorityFromAuth($request)
    {
        list($username, $password) = self::getUserPassFromAuth($request);

        return self::getAuthorityFromUserPass($username, $password);
    }

    /**
     * Gets the current Client from the Authorization header.
     * @param Request $request
     * @return ClientId
     */
    static function getClientId($request)
    {
        list($username, $password) = self::getUserPassFromAuth($request);

        $client = self::getClient($username, $password);

        return $client->api_basic_key;
    }
}
