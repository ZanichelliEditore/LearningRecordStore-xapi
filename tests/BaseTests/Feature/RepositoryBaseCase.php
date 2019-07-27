<?php

use App\Models\Client;
use Rhumsaa\Uuid\Uuid;
use App\Constants\Scope;
use App\Locker\HelperTest;
use Illuminate\Http\UploadedFile;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Http\Repositories\xapiRepositories\StatementRepository;


abstract class RepositoryBaseCase extends TestCase
{
    use DatabaseMigrations;

    public abstract function authentication();
    /**
     * A setup method launched at the beginning of test
     *
     * @return void
    */
    public function setup():void
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

    private const LIMIT = 10;

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
     * @return void
     */
    public function getAllStatementsTest()
    {
        $helper = $this->help();
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';

        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);

        $this->call('POST', HelperTest::URL, $helper->getStatement(), [], [], $this->authentication());

        $statements = $repo->all('lrs_test', self::LIMIT);
        $statement = $statements['statements'][0]->statement;
        unset($statement->id);
        unset($statement->stored);

        $this->assertEquals(json_encode($helper->getStatement()), json_encode($statement));
    }

    /**
     * @test
     * @return void
     */
    public function getAllStatementsEmptyTest()
    {
        $repo = new StatementRepository();

        $statements = $repo->all('lrs_test', self::LIMIT);
        
        $this->assertNull($statements);
    }

     /**
     * @test
     * @return void
     */
    public function getAllStatementsBackupTest()
    {
        $helper = $this->help();
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $filePath = HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
      
        Storage::makeDirectory(HelperTest::STORAGE_PATH);
        Storage::makeDirectory($filePath);

        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);

        UploadedFile::fake()->create($filePathBackup . 'test.json', 1);

        Storage::put($filePathBackup . '/test.json', $helper->createStatementFileContent("ciccio"));

        $statements = $repo->all('lrs_test', self::LIMIT);

        $statement = $statements['statements'][0]->statement;
        unset($statement->id);
        unset($statement->stored);

        $this->assertEquals(json_encode($helper->getStatement("ciccio")), json_encode($statement));
    }

        /**
     * @test
     * @return void
     */
    public function getAllStatementsBothContentTest()
    {
        $helper = $this->help();
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $filePath = HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
      
        Storage::makeDirectory(HelperTest::STORAGE_PATH);
        Storage::makeDirectory($filePath);

        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);

        UploadedFile::fake()->create($filePathBackup . 'test.json', 1);
        UploadedFile::fake()->create($filePath . 'test.json', 1);

        $pathStatementAttrName = 'eve';
        $pathBackupStatementAttrName = 'alice';

        Storage::put($filePath . '/test.json', $helper->createStatementFileContent($pathStatementAttrName));
        Storage::put($filePathBackup . '/test.json', $helper->createStatementFileContent($pathBackupStatementAttrName));

        $statements = $repo->all('lrs_test', self::LIMIT);

        $this->assertCount(2,$statements['statements']);

        $statement = $statements['statements'][0]->statement;
        $this->assertEquals(json_encode($helper->getStatement($pathStatementAttrName)), json_encode($statement));

        $statement = $statements['statements'][1]->statement;
        $this->assertEquals(json_encode($helper->getStatement($pathBackupStatementAttrName)), json_encode($statement));

    }

    /**
     * @test
     * @return void
     */
    public function getAllStatementsWithVerbTest()
    {
        $helper = $this->help();
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $verb = 'logged';
        $statement1 = $helper->getStatement();
        $statement2 = $helper->getStatement();
        $statement2['verb']['id'] = 'https://w3id.org/xapi/adl/verbs/logged';

        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);
        
        $this->call('POST', HelperTest::URL, [$statement1, $statement2], [], [], $this->authentication())->getContent();

        $statements = $repo->all('lrs_test', self::LIMIT, $verb);

        $statement = $statements['statements'][0]->statement;
        unset($statement->id);
        unset($statement->stored);     
        $this->assertTrue(!isset($statements[0]));
        $this->assertEquals(json_encode($statement2), json_encode($statement));

        $statements = $repo->all('lrs_test', self::LIMIT, "test");
        $this->assertTrue(empty($statements['statements']));
    }

    /**
     * @test
     * @return void
     */
    public function getAllStatementsWithPageTest()
    {
        $helper = $this->help();
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $statement1 = $helper->getStatement();

        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);
        
        $this->call('POST', HelperTest::URL, [$statement1], [], [], $this->authentication())->getContent();
        $statements = $repo->all('lrs_test', self::LIMIT, null, 1); 
        $this->assertTrue(isset($statements['statements'][0]));

        $statements = $repo->all('lrs_test', self::LIMIT, null, 20); 
        $this->assertEquals(null, $statements);
    }

    /**
     * @test
     * @return void
    */
    public function getStatementTest()
    {
        $helper = $this->help();
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';

        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);

        $content = json_decode($this->call('POST', HelperTest::URL, $helper->getStatement(), [], [], $this->authentication())->getContent());

        $statement = $repo->find('lrs_test', $content[0]);
        $statement = $statement->statement;
        unset($statement->id);
        unset($statement->stored);

        $this->assertEquals(json_encode($helper->getStatement()), json_encode($statement));
    }

        /**
     * @test
     * @return void
     */
    public function getStatementNoIdFailTest()
    {
        $helper = $this->help();
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';

        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);

        $this->call('POST', HelperTest::URL, $helper->getStatement(), [], [], $this->authentication());

        $statement = $repo->find('lrs_test', 'verylongid');
        $this->assertNull($statement);
    }

    /**
     * @test
     * @return void
     */
    public function getAllStatementsFailTest()
    {
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $filePath = HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test';

        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);
        Storage::makeDirectory($filePath);

        $statements = $repo->all('lrs_test', self::LIMIT);

        $this->assertEquals(null, $statements);
    }

    /**
     * @test
     * @return void
     */
    public function getStatementFailTest()
    {
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $filePath = HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $uid = (string) Uuid::uuid1();

        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);
        Storage::makeDirectory($filePath);


        $statement = $repo->find('lrs_test', $uid);

        $this->assertEquals(null, $statement);
    }
    
    /**
     * @test
     * @return void
     */
    public function getStatementFailFindTest()
    {
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $filePath = HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $uid = (string) Uuid::uuid1();

        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);
        Storage::makeDirectory($filePath);


        $statement = $repo->find('lrs_test', $uid);

        $this->assertEquals(null, $statement);
    }
}