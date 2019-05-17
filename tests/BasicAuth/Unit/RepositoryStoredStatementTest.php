<?php

use Rhumsaa\Uuid\Uuid;
use App\Locker\HelperTest;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Repositories\StatementRepository;

class RepositoryStoredStatementTest extends TestCase
{
   
    private const FOLDER = '_Test';
    private const LIMIT = 10;

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
     * @return void
     */
    public function storeSingleStatementTest()
    {
        $repo = new StatementRepository();
        $statementRequest = new Request();
        $statementRequest->headers->set(HelperTest::BASIC_HEADER_AUTH_NAME, HelperTest::BASIC_HEADER_AUTH_TYPE . ' ' . base64_encode(env('CLIENT_ID') . ":" . env('CLIENT_SECRET')));
        $statementRequest->headers->set('PHP_AUTH_USER', env('CLIENT_ID'));
        $statementRequest->headers->set('PHP_AUTH_PW', env('CLIENT_SECRET'));
        $statement = json_encode($this->help()->getStatementWithUuid(), JSON_UNESCAPED_SLASHES);

        $response = $repo->store($statement, self::FOLDER);
        $this->assertEquals(true, $response);

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

        Storage::fake('local');
        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);
        
        $content = $this->call('POST', HelperTest::URL, $helper->getStatement(), [], [], HelperTest::createBasicHeader());

        $statements = $repo->all('lrs_test', self::LIMIT);
        $statement = $statements['statements'][0];
        unset($statement->id);
        unset($statement->stored);

        $this->assertEquals(json_encode($helper->getStatement()), json_encode($statement));
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

        Storage::fake('local');
        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);
        
        $this->call('POST', HelperTest::URL, [$statement1, $statement2], [], [], HelperTest::createBasicHeader())->getContent();

        $statements = $repo->all('lrs_test', self::LIMIT, $verb);
        $statement = $statements['statements'][0];
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

        Storage::fake('local');
        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);
        
        $this->call('POST', HelperTest::URL, [$statement1], [], [], HelperTest::createBasicHeader())->getContent();
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

        Storage::fake('local');
        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);

        $content = json_decode($this->call('POST', HelperTest::URL, $helper->getStatement(), [], [], HelperTest::createBasicHeader())->getContent());

        $statement = $repo->find('lrs_test', $content[0]);
        unset($statement->id);
        unset($statement->stored);

        $this->assertEquals(json_encode($helper->getStatement()), json_encode($statement));
    }

    /**
     * @test
     * @return void
     */
    public function getAllStatementsFailTest()
    {
        $helper = $this->help();
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $filePath = HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test';

        Storage::fake('local');
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
        $helper = $this->help();
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $filePath = HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $uid = (string) Uuid::uuid1();

        Storage::fake('local');
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
        $helper = $this->help();
        $repo = new StatementRepository();
        $filePathBackup = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $filePath = HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'lrs_test';
        $uid = (string) Uuid::uuid1();

        Storage::fake('local');
        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH);
        Storage::makeDirectory($filePathBackup);
        Storage::makeDirectory($filePath);

        $content = json_decode($this->call('POST', HelperTest::URL, $helper->getStatement(), [], [], HelperTest::createBasicHeader())->getContent());

        $statement = $repo->find('lrs_test', $uid);

        $this->assertEquals(null, $statement);
    }   
}