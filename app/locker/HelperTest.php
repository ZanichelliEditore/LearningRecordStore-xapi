<?php 

namespace App\Locker;

use Rhumsaa\Uuid\Uuid;
use Illuminate\Support\Facades\Storage;

class HelperTest
{
    const URL = "/data/xAPI/statements";
    const GRANT_TYPE = 'client_credentials';
    const HEADER_AUTH_NAME = 'HTTP_Authorization';
    const HEADER_AUTH_TYPE = 'Bearer';
    const BASIC_HEADER_AUTH_NAME = 'Authorization';
    const BASIC_HEADER_AUTH_TYPE = 'Basic';
    const HEADER_ACCEPT = 'Accept';
    const HEADER_ACCEPTED_TYPE = 'application/json';

    const STORAGE_PATH = "/testing/statements";
    const STORAGE_BACKUP_PATH ="/testing/backup_statements";

    /**
     * Example statement
     *
     * @return array
     */
    public function getStatement()
    {
        $statement = [
            "actor" => [
                "objectType" => "Agent",
                "name" => "Giuseppe",
                "account" => [
                    "homePage" => "https://my.zanichelli.it",
                    "name" => "10881"
                ]
            ],
            "verb" => [
                "id" => "https://w3id.org/xapi/adl/verbs/action",
                "display" => ["en-US" => "sentences"]
            ],
            "object" => [
                "objectType" => "Activity",
                "id" => "https://w3id.org/xapi/keys/object-id",
                "definition" => ["type" => "https://example.com/xapi/keys/type"]
            ],
            "context" => [
                "platform" => "APP",
                "extensions" => ["https://example.com/xapi/activities" => "something"],
                "contextActivities" => [
                    "parent" => ["id" => "http://www.example.com/xapi/activities/else"]
                ],
                "registration" => "0ab5f76e-3389-11e9-873f-6aa43c3ec3b2"
            ],
            "result" => [
                "success" => true,
                "completion" => true, 
                "response" => "We agreed on some example actions.",
                "duration" => "PT1H0M0S"
            ],
            "authority" => [
                "objectType" => "Agent",
                "name" => "Client",
                "mbox" => "mailto:mail@test.com"
            ],
            "version" => "1.0.0",
            "timestamp" => "2019-01-20T12:17:00+00:00"
        ];
        return $statement;
    }

    public function getStatementWithUuid(string $uid = null)
    {
        $statement = $this->getStatement();
        $statement["id"] = $uid ?: (string) Uuid::uuid1();
        return $statement;
    }

    /**
     * Example statement with substatement
     *
     * @return array
     */
    public function getStatementWithSubstatement()
    {
        return [
            "actor" => [
                "objectType" => "Agent",
                "name" => "Giuseppe",
                "account" => [
                    "homePage" => "https://my.zanichelli.it",
                    "name" => "10881"
                ]
            ],
            "verb" => [
                "id" => "https://w3id.org/xapi/adl/verbs/action",
                "display" => ["en-US" => "sentences"]
            ],
            "object" => [ 
                "objectType" => "SubStatement",
                "actor" => [
                    "objectType" => "Agent", 
                    "mbox"=> "mailto:test@example.com" 
                ],
                "verb" => [
                    "id" => "http://example.com/visited", 
                    "display" => [
                        "en-US" => "will visit"
                    ]
                ],
                "object" => [
                    "objectType" => "Activity",
                    "id" => "http://example.com/website",
                    "definition" => [
                        "name" => [
                            "en-US" => "Some Awesome Website"
                        ]
                    ]
                ]            
            ]
        ];
    }
    
    /**
     * Create auth header from an access token
     * @param string $access_token
     * @return  array
     */
    public function createHeader($access_token)
    {
        return [
            self::HEADER_ACCEPT => self::HEADER_ACCEPTED_TYPE,
            self::HEADER_AUTH_NAME => self::HEADER_AUTH_TYPE.' '.$access_token
        ];
    }

    /**
     * Create auth header from an access token
     * @param string $access_token
     * @return  array
     */
    static function createBasicHeader()
    {
        return [
            "PHP_AUTH_USER" => '85834ea3f1150032809f16ab1d4ec194b1ec8608',
            "PHP_AUTH_PW" => 'PxEr4aRcHs4Tnfz7BatQqVoovCqSxXbqXKcmeJom'
        ];
    }

    /**
     * Delete test folders
     */
    public static function deleteTestingFolders()
    {
        Storage::deleteDirectory(self::STORAGE_PATH);
        Storage::deleteDirectory(self::STORAGE_BACKUP_PATH);
    }


}