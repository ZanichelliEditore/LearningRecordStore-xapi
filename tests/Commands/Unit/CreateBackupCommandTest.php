<?php

use \Mockery as Mockery;
use App\Locker\HelperTest;
use App\Services\StatementStorageService;
use App\Console\Commands\CreateFileBackup;


class CreateBackupCommandTest extends TestCase
{

    /** 
     * @test
     * @return void
     */
    public function noFolderGivenTest()
    {
        Storage::fake('local');

        $statusCode = $this->artisan('lrs:create-file', [
            '--folder'  => ''
        ]);
        $this->assertEquals(1, $statusCode);
    }

    /** 
     * @test
     * @return void
     */
    public function unrealFolderTest()
    {
        Storage::fake('local');

        $statusCode = $this->artisan('lrs:create-file', [
            '--folder'  => 'e839hje3i3'
        ]);
        $this->assertEquals(1, $statusCode);
    }

    /** 
     * @test
     * @return void
     */
    public function successCaseTest()
    {
        Storage::fake('local');
        Storage::makeDirectory(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'example_e1');

        $mockService = Mockery::mock(StatementStorageService::class)->makePartial()
            ->shouldReceive(['storeBackup' => true, 'read' => true])
            ->withAnyArgs()
            ->once()
            ->getMock();
        $this->app->instance('App\Services\StatementStorageService', $mockService);
        
        $statusCode = $this->artisan('lrs:create-file', [
            '--folder'  => 'example_e1'
        ]);
        $this->assertEquals(0, $statusCode);
    }
}