<?php

use App\Locker\HelperTest;
use Illuminate\Support\Facades\Storage;
use App\Console\Commands\DeleteDirectory;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application as ConsoleApplication;


class DeletingDirectoryCommandTest extends TestCase
{
    private $command;
    private $commandTester;

    /**
     * Set up test variables and environment
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $application = new ConsoleApplication();

        $testedCommand = $this->app->make(DeleteDirectory::class);
        $testedCommand->setLaravel(app());
        $application->add($testedCommand);

        $this->command = $application->find('lrs:delete-directory');

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
        $this->assertArraySubset(["No directory found with the given name"], $outputs);
    }


    /** 
     * @test
     * @return void
     */
    public function successCaseTest()
    {
        Storage::fake('local');
        Storage::makeDirectory(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'example_e2');

        $this->commandTester->execute([
            '--folder'  => 'example_e2',
        ]);

        $outputs = explode(PHP_EOL, $this->commandTester->getDisplay());        
        $this->assertArraySubset(["Begin", "Directory deleted successfully"], $outputs);
        
        HelperTest::deleteTestingFolders();
    }

}