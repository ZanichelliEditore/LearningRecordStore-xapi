<?php

use Rhumsaa\Uuid\Uuid;
use App\Locker\HelperTest;
use function GuzzleHttp\json_encode;
use Illuminate\Support\Facades\Storage;
use App\Repositories\StatementRepository;
use App\Services\StatementStorageService;

class StorageStatementTest extends TestCase
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
     * @test
    */
    public function storageStatementSuccessTest() 
    {
        $helper = $this->help();
        $uid = (string) Uuid::uuid1();
        $repo = new StatementRepository();
        Storage::fake('local');
        
        $header = HelperTest::createBasicHeader();
        $res = $this->call('POST', HelperTest::URL, $helper->getStatement(), [], [], $header);
        $id = json_decode($res->getContent())[0];
        $statement = $repo->find('lrs_test', $id);

        Storage::disk('local')->assertExists(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test' . DIRECTORY_SEPARATOR . $id . '.json');
        $this->assertEquals($id, $statement->id);

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

        $header = HelperTest::createBasicHeader();
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

        $header = HelperTest::createBasicHeader();
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
        
        $header = HelperTest::createBasicHeader();
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid), [], [], $header);
        
        $storageService = new StatementStorageService();
        $content  = $storageService->read('lrs_test');
        $expected = '['.json_encode($helper->getStatementWithUuid($uid), JSON_UNESCAPED_SLASHES).']';

        $filteredContent = json_decode($content);
        unset($filteredContent[0]->stored);

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

        $header = HelperTest::createBasicHeader();
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid),  [], [], $header);
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid2), [], [], $header);
        
        $storageService = new StatementStorageService();
        $content  = $storageService->read('lrs_test');
        $expected = [json_encode($helper->getStatementWithUuid($uid), JSON_UNESCAPED_SLASHES),json_encode($helper->getStatementWithUuid($uid2), JSON_UNESCAPED_SLASHES)];

        $filteredContent = json_decode($content);
        foreach ($filteredContent as $statement) {
            unset($statement->stored);
        }
        $this->assertContains($expected[0], json_encode($filteredContent, JSON_UNESCAPED_SLASHES));
        $this->assertContains($expected[1], json_encode($filteredContent, JSON_UNESCAPED_SLASHES));
        $this->assertCount(2, $filteredContent);
    }


    /**
     * @test
     */
    public function backupStatementSuccessTest() 
    {
        $helper = $this->help();
        $uid = (string) Uuid::uuid1();
        Storage::fake('local');

        $header = HelperTest::createBasicHeader();
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