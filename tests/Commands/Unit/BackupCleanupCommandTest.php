<?php

use App\Console\Commands\BackupCleanup;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application as ConsoleApplication;


class BackupCleanupCommandTest extends TestCase
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

        $testedCommand = $this->app->make(BackupCleanup::class);
        $testedCommand->setLaravel(app());
        $application->add($testedCommand);

        $this->command = $application->find('lrs:backup-cleanup');

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
        $this->assertArraySubset(["No folder given, all backup directories will be cleaned up", "Begin", "Backup data cleaned up succesfully."], $outputs);
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

}