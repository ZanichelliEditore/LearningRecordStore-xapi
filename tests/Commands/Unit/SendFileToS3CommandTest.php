<?php

use Rhumsaa\Uuid\Uuid;
use App\Locker\HelperTest;
use App\Console\Commands\SendFileToS3;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application as ConsoleApplication;
use function GuzzleHttp\json_encode;


class SendFileToS3CommandTest extends TestCase
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

        $testedCommand = $this->app->make(SendFileToS3::class);
        $testedCommand->setLaravel(app());
        $application->add($testedCommand);

        $this->command = $application->find('lrs:send-statements');

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
        Storage::makeDirectory(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'example_1');

        $this->commandTester->execute([
            '--folder'  => 'example_1',
        ]);

        $outputs = explode(PHP_EOL, $this->commandTester->getDisplay());        
        $this->assertArraySubset(["Begin", "The given folder is empty, no statement found"], $outputs);
        
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
        $filePath = HelperTest::STORAGE_PATH.DIRECTORY_SEPARATOR . 'example_1';

        Storage::fake('local');

        $statement = json_encode($helper->getStatementWithUuid($uid), JSON_UNESCAPED_SLASHES);

        Storage::makeDirectory($filePath);
        Storage::put($filePath . DIRECTORY_SEPARATOR . $uid . '.json', $statement);

        $this->commandTester->execute([
            '--folder' => 'example_1'
        ]);

        $outputs = explode(PHP_EOL, $this->commandTester->getDisplay());        
        $this->assertArraySubset(["Begin", "File successfully sent"], $outputs);
        
        HelperTest::deleteTestingFolders();
    }

}