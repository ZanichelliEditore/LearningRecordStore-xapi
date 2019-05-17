<?php

use App\Locker\HelperTest;
use Illuminate\Support\Facades\Storage;
use App\Console\Commands\SendAllDirectoriesFiles;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application as ConsoleApplication;
use function GuzzleHttp\json_encode;

class SendAllDirectoriesFilesCommandTest extends TestCase
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

        $testedCommand = $this->app->make(SendAllDirectoriesFiles::class);
        $testedCommand->setLaravel(app());
        $application->add($testedCommand);

        $this->command = $application->find('lrs:send-all-statements');

        $this->commandTester = new CommandTester($this->command);
    }

    /** 
     * @test
     * @return void
     */
    public function successCaseTest()
    {
        $statement = json_encode($this->help()->getStatementWithSubstatement(), JSON_UNESCAPED_SLASHES);

        Storage::fake('local');

        Storage::makeDirectory(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR .'example_e1');
        Storage::put(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'example_e1' . DIRECTORY_SEPARATOR . 'example.json', $statement);
        Storage::makeDirectory(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR .'example_e2');

        $this->commandTester->execute([]);

        $outputs = explode(PHP_EOL, $this->commandTester->getDisplay());        
        $this->assertArraySubset(["Begin", 
            "Directory " . HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'example_e1', 
            "Backup succesfully created.",
            "Data successfully sent.",
            "Directory " . HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'example_e2', 
            "this directory is empty"
        ], $outputs);
        
        HelperTest::deleteTestingFolders();
    }

}