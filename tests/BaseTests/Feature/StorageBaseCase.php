<?php

use App\Models\Client;
use Rhumsaa\Uuid\Uuid;
use App\Constants\Scope;
use App\Locker\HelperTest;
use function GuzzleHttp\json_encode;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\xapiRepositories\StatementRepository;
use App\Services\StatementStorageService;
use Laravel\Lumen\Testing\DatabaseMigrations;

abstract class StorageBaseCase extends TestCase
{
    use DatabaseMigrations;

    public abstract function authentication();
    /**
     * A setup method launched at the beginning of test
     *
     * @return void
     */
    public function setup(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->artisan('db:seed');
        Client::where('api_basic_key', env('CLIENT_ID'))
            ->update(['scopes'  => '["' . Scope::STATEMENTS_READ . '", "' . Scope::STATEMENTS_WRITE . '"]']);

        Storage::fake('local');
    }

    /**
     * A setup method launched at the end of test
     *
     * @return void
     */
    public function tearDown()
    {
        HelperTest::deleteTestingFolders();
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

    /**
     * creation class
     *
     * @return HelperTest
     */
    public function  help()
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
        $repo = new StatementRepository();

        $header = $this->authentication();
        $res = $this->call('POST', HelperTest::URL, $helper->getStatement(), [], [], $header);
        $id = json_decode($res->getContent())[0];
        $statement = $repo->find('lrs_test', $id);

        Storage::disk('local')->assertExists(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test' . DIRECTORY_SEPARATOR . $id . '.json');
        $this->assertEquals($id, $statement->statement->id);
    }

    /**
     * @test
     */
    public function storageStatementsSuccessTest()
    {
        $helper = $this->help();
        $uid1 = (string) Uuid::uuid1();
        $uid2 = (string) Uuid::uuid1();
        $repo = new StatementRepository();

        $header = $this->authentication();
        $res = $this->call('POST', HelperTest::URL, [$helper->getStatementWithUuid($uid1), $helper->getStatementWithUuid($uid2)], [], [], $header);

        $id1 = json_decode($res->getContent())[0];
        $id2 = json_decode($res->getContent())[1];

        $statement = $repo->find('lrs_test', $id1);
        $statement2 = $repo->find('lrs_test', $id2);

        Storage::disk('local')->assertExists(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test' . DIRECTORY_SEPARATOR . $uid1 . '.json');
        $this->assertEquals($id1, $statement->statement->id);
        $this->assertEquals($id2, $statement2->statement->id);
    }

    /**
     * @test
     */
    public function storageFailureTest()
    {
        $uid = (string) Uuid::uuid1();

        $header = $this->authentication();
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

        $header = $this->authentication();
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid), [], [], $header);

        $storageService = new StatementStorageService();
        $content  = $storageService->read('lrs_test');

        $statement = [
            'lrs_id' => '1234567890',
            'client_id' => env('CLIENT_ID'),
            'statement' => $helper->getStatementWithUuid($uid)
        ];
        $expected = '[' . json_encode($statement, JSON_UNESCAPED_SLASHES) . ']';

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

        $header = $this->authentication();
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid),  [], [], $header);
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid2), [], [], $header);

        $storageService = new StatementStorageService();
        $content  = $storageService->read('lrs_test');
        $statement = [
            'lrs_id' => '1234567890',
            'client_id' => env('CLIENT_ID'),
            'statement' => $helper->getStatementWithUuid($uid)
        ];
        $statement2 = [
            'lrs_id' => '1234567890',
            'client_id' => env('CLIENT_ID'),
            'statement' => $helper->getStatementWithUuid($uid2)
        ];
        $expected = [json_encode($statement, JSON_UNESCAPED_SLASHES), json_encode($statement2, JSON_UNESCAPED_SLASHES)];

        $filteredContent = json_decode($content);
        foreach ($filteredContent as $statement) {
            unset($statement->statement->stored);
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

        $header = $this->authentication();
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid), [], [], $header);

        $storageService = new StatementStorageService();
        $storedFile = Storage::files(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test');

        $content    = Storage::disk('local')->get($storedFile[0]);

        $storageService->storeBackup($content, 'lrs_test');
        $fileName = Storage::files(HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test');
        $actualContent = Storage::disk('local')->get($fileName[0]);

        $this->assertEquals($content, $actualContent);
    }

    /**
     * @test
     */
    public function backupStatementCreateDirTest()
    {
        $helper = $this->help();
        $uid = (string) Uuid::uuid1();

        $header = $this->authentication();
        $this->call('POST', HelperTest::URL, $helper->getStatementWithUuid($uid), [], [], $header);

        if (Storage::exists(HelperTest::STORAGE_BACKUP_PATH)) {
            Storage::deleteDirectory(HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test');
        }

        $storageService = new StatementStorageService();
        $storedFile = Storage::files(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test');

        $content = Storage::disk('local')->get($storedFile[0]);

        $storageService->storeBackup($content, 'lrs_test');
        $fileName = Storage::files(HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test');
        $actualContent = Storage::disk('local')->get($fileName[0]);

        $this->assertEquals($content, $actualContent);
    }

    /**
     * @test
     */
    public function storeEmptyStatementFailTest()
    {

        $storageService = new StatementStorageService();

        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';

        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);
        $content = [];

        $val = $storageService->store($content, 'lrs_test');

        $this->assertTrue($val);
    }
}
