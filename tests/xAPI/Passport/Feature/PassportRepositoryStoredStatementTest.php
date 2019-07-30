<?php

use App\Constants\Scope;
use App\Locker\HelperTest;
use \RepositoryBaseCase as RepositoryStatementBase;


class PassportRepositoryStoredStatementTest extends RepositoryStatementBase
{
    /**
     * Request oauth token
     * @return  array
    */
    public function authentication()
    {
        $helper = $this->help();
        $bodyParams = [
            "client_id" => env('CLIENT_ID'),
            "client_secret" => env('CLIENT_SECRET'),
            "grant_type" => HelperTest::GRANT_TYPE,
            "scope" => [Scope::STATEMENTS_READ, Scope::STATEMENTS_WRITE]
        ];

        $content = $this->call('POST', env("SWAGGER_LUME_CONST_HOST"), $bodyParams)->getContent();
        $body = json_decode($content);

        try {
            $access_token = $body->access_token;
        } catch (Exception $e) {
            Log::error('Test error: '. (string)$e);
            $access_token = '';
        }

        return $helper->createHeader($access_token);
    }

}