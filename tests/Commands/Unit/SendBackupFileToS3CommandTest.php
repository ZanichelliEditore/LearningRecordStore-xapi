<?php

use Rhumsaa\Uuid\Uuid;
use App\Locker\HelperTest;
use Illuminate\Support\Facades\Storage;
use App\Console\Commands\SendFileBackup;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application as ConsoleApplication;

class SendBackupFileToS3CommandTest extends TestCase
{
    private $command;
    private $commandTester;

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
     * Set up test variables and environment
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $application = new ConsoleApplication();

        $testedCommand = $this->app->make(SendFileBackup::class);
        $testedCommand->setLaravel(app());
        $application->add($testedCommand);

        $this->command = $application->find('lrs:send-backup-files');

        $this->commandTester = new CommandTester($this->command);
    }

    /** 
     * @test
     * @return void
     */
    public function noFolderGivenTest()
    {
        Storage::fake('local');
        $this->commandTester->execute([
            '--folder' => '',
        ]);

        $outputs = explode(PHP_EOL, $this->commandTester->getDisplay());        
        $this->assertArraySubset(["The folder is required"], $outputs);
        $this->assertEquals(false, $this->commandTester->getStatusCode());
    }

    /** 
     * @test
     * @return void
     */
    public function unrealFolderTest()
    {
        Storage::fake('local');
        $this->commandTester->execute([
            '--folder'  => 'e839hje3i3',
        ]);

        $outputs = explode(PHP_EOL, $this->commandTester->getDisplay());        
        $this->assertArraySubset(["No directory found with the given name: cannot read from an unreal folder"], $outputs);
    }

    /** 
     * @test
     * @return void
     */
    public function emptyFolderTest()
    {
        Storage::fake('local');
        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'example_1');

        $this->commandTester->execute([
            '--folder'  => 'example_1',
        ]);

        $outputs = explode(PHP_EOL, $this->commandTester->getDisplay());        
        $this->assertArraySubset(["Begin", "The given folder is empty"], $outputs);
        
        HelperTest::deleteTestingFolders();
    }

    /** 
     * @test
     * @return void
     */
    public function successCaseTest()
    {
        $helper = $this->help();
        $uid = (string) Uuid::uuid1();
        $filePath = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'example_1';

        Storage::fake('local');

        $statement = json_encode($helper->getStatementWithUuid($uid), JSON_UNESCAPED_SLASHES);

        Storage::makeDirectory($filePath);
        Storage::put($filePath . DIRECTORY_SEPARATOR . $uid . '.json', $statement);

        $this->commandTester->execute([
            '--folder'  => 'example_1',
        ]);

        $outputs = explode(PHP_EOL, $this->commandTester->getDisplay());        
        $this->assertArraySubset(["Begin", $filePath . DIRECTORY_SEPARATOR . $uid . ".json sent"], $outputs);
        
        HelperTest::deleteTestingFolders();
    }
}