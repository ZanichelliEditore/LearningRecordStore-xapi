<?php

use App\Locker\HelperTest;
use App\Console\Commands\CreateFileBackup;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application as ConsoleApplication;


class CreateBackupCommandTest extends TestCase
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

        $testedCommand = $this->app->make(CreateFileBackup::class);
        $testedCommand->setLaravel(app());
        $application->add($testedCommand);

        $this->command = $application->find('lrs:create-file');

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
        $this->assertArraySubset(["Begin", "The given folder does not exists"], $outputs);
    }

    /** 
     * @test
     * @return void
     */
    public function successCaseTest()
    {
        Storage::fake('local');
        $statement = json_encode($this->help()->getStatementWithSubstatement(), JSON_UNESCAPED_SLASHES);

        Storage::makeDirectory(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'example_e1');
        Storage::put(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR .'example_e1' . DIRECTORY_SEPARATOR . 'example.json', $statement);

        $this->commandTester->execute([
            '--folder' => 'example_e1',
        ]);

        $outputs = explode(PHP_EOL, $this->commandTester->getDisplay());        
        $this->assertArraySubset(["Begin", "File created"], $outputs);
        HelperTest::deleteTestingFolders();
    }
}