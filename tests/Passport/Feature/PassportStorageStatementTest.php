<?php

use Rhumsaa\Uuid\Uuid;
use App\Locker\HelperTest;
use function GuzzleHttp\json_encode;
use Illuminate\Support\Facades\Storage;
use App\Services\StatementStorageService;

class PassportStorageStatementTest extends TestCase
{
    /**
     * creation class
     *
     * @return HelperTest
     */
    private function  help()
    {
        $helper = new HelperTest();
        return $helper;
    }

    /**
     * Request oauth token
     * @return  string
     */
    private function authentication()
    {
        $bodyParams = [
            "client_id" => '85834ea3f1150032809f16ab1d4ec194b1ec8608',
            "client_secret" => 'PxEr4aRcHs4Tnfz7BatQqVoovCqSxXbqXKcmeJom',
            "grant_type" => HelperTest::GRANT_TYPE
        ];

        $content = $this->call('POST', env("SWAGGER_LUME_CONST_HOST"), $bodyParams)->getContent();
        $body = json_decode($content);

        try {
            $access_token = $body->access_token;
        } catch (Exc $e) {
            $access_token = '';
        }
        return $access_token;
    }

    /**
     * @test
     */
    public function storageStatementSuccessTest() 
    {
        $helper = $this->help();
        $uid = (string) Uuid::uuid1();
        Storage::fake('local');
        
        $header = $helper->createHeader($this->authentication());
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid), [], [], $header);

        Storage::disk('local')->assertExists(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test' . DIRECTORY_SEPARATOR . $uid . '.json');
        HelperTest::deleteTestingFolders();
    }

    /**
     * @test
     */
    public function storageStatementsSuccessTest() 
    {
        $helper = $this->help();
        $uid = (string) Uuid::uuid1();
        $uid2 = (string) Uuid::uuid1();
        Storage::fake('local');

        $header = $helper->createHeader($this->authentication());
        $this->call('POST', HelperTest::URL, [$helper->getStatementWithUuid($uid), $helper->getStatementWithUuid($uid2)], [], [], $header);

        Storage::disk('local')->assertExists(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test' . DIRECTORY_SEPARATOR . $uid . '.json');
        HelperTest::deleteTestingFolders();
    }

    /**
     * @test
     */
    public function storageFailureTest() 
    {
        $helper = $this->help();
        $uid = (string) Uuid::uuid1();
        Storage::fake('local');

        $header = $helper->createHeader($this->authentication());
        $this->call('POST', HelperTest::URL, [], [], [], $header);

        Storage::disk('local')->assertMissing(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . $uid . '.json');
    }


    /**
     * @test
     */
    public function readStatementSuccessTest() 
    {
        $helper = $this->help();
        $uid = (string) Uuid::uuid1();
        Storage::fake('local');
        
        $header = $helper->createHeader($this->authentication());
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid), [], [], $header);
        
        $storageService = new StatementStorageService();
        $content  = $storageService->read('lrs_test');
        
        $statement = [
            'lrs_id' => '1234567890',
            'client_id' => '85834ea3f1150032809f16ab1d4ec194b1ec8608',
            'statement' => $helper->getStatementWithUuid($uid)
        ];
        $expected = '['.json_encode($statement, JSON_UNESCAPED_SLASHES).']';

        $filteredContent = json_decode($content);
        unset($filteredContent[0]->statement->stored);

        $this->assertEquals($expected, json_encode($filteredContent, JSON_UNESCAPED_SLASHES));
    }


    /**
     * @test
     */
    public function readMultipleStatementSuccessTest() 
    {
        $helper = $this->help();
        $uid  = (string) Uuid::uuid1();
        $uid2 = (string) Uuid::uuid1();
        Storage::fake('local');

        $header = $helper->createHeader($this->authentication());
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid),  [], [], $header);
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid2), [], [], $header);
        
        $storageService = new StatementStorageService();
        $content  = $storageService->read('lrs_test');
        $statement = [
            'lrs_id' => '1234567890',
            'client_id' => '85834ea3f1150032809f16ab1d4ec194b1ec8608',
            'statement' => $helper->getStatementWithUuid($uid)
        ];
        $statement2 = [
            'lrs_id' => '1234567890',
            'client_id' => '85834ea3f1150032809f16ab1d4ec194b1ec8608',
            'statement' => $helper->getStatementWithUuid($uid2)
        ];
        $expected = [json_encode($statement, JSON_UNESCAPED_SLASHES),json_encode($statement2, JSON_UNESCAPED_SLASHES)];

        $filteredContent = json_decode($content);
        foreach ($filteredContent as $statement) {
            unset($statement->statement->stored);
        }
        
        $this->assertEquals($expected, json_encode($filteredContent, JSON_UNESCAPED_SLASHES));
    }


    /**
     * @test
     */
    public function backupStatementSuccessTest() 
    {
        $helper = $this->help();
        $uid = (string) Uuid::uuid1();
        Storage::fake('local');

        $header = $helper->createHeader($this->authentication());
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid), [], [], $header);

        $storageService = new StatementStorageService();
        $storedFile = Storage::files(HelperTest::STORAGE_PATH.DIRECTORY_SEPARATOR.'lrs_test'); 

        $content    = Storage::disk('local')->get($storedFile[0]);

        $storageService->storeBackup($content, 'lrs_test');
        $fileName = Storage::files(HelperTest::STORAGE_BACKUP_PATH.DIRECTORY_SEPARATOR.'lrs_test');
        $actualContent = Storage::disk('local')->get($fileName[0]);

        $this->assertEquals($content, $actualContent);
        HelperTest::deleteTestingFolders();
    }
    
}