<?php

use \Mockery as Mockery;
use App\Locker\HelperTest;
use App\Http\Repositories\xapiRepositories\StatementRepository;
use App\Services\StatementStorageService;


class SendFileToS3CommandTest extends TestCase
{

    /** 
     * @test
     * @return void
     */
    public function noFolderGivenTest()
    {
        Storage::fake('local');

        $statusCode = $this->artisan('lrs:send-statements', [
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

        $statusCode = $this->artisan('lrs:send-statements', [
            '--folder'  => 'e839hje3i3'
        ]);
        $this->assertEquals(1, $statusCode);
    }

    /** 
     * @test
     * @return void
     */
    public function emptyFolderTest()
    {
        Storage::fake('local');
        Storage::makeDirectory(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'example_1');


        $mockService = Mockery::mock(StatementStorageService::class)->makePartial()
            ->shouldReceive(['read' => null])
            ->withAnyArgs()
            ->once()
            ->getMock();
        $this->app->instance('App\Services\StatementStorageService', $mockService);

        $statusCode = $this->artisan('lrs:send-statements', [
            '--folder'  => 'example_1'
        ]);
        $this->assertEquals(0, $statusCode);
        
    }

    /** 
     * @test
     * @return void
     */
    public function successCaseTest()
    {
        $filePath = HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'example_1';

        Storage::fake('local');
        Storage::makeDirectory($filePath);

        $mockRepository = Mockery::mock(StatementRepository::class)->makePartial()
            ->shouldReceive(['store' => true])
            ->withAnyArgs()
            ->once()
            ->getMock();
        $this->app->instance('App\Http\Repositories\xapiRepositories\StatementRepository', $mockRepository);

        $mockService = Mockery::mock(StatementStorageService::class)->makePartial()
            ->shouldReceive(['storeBackup' => true, 'read' => true])
            ->once()
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Services\StatementStorageService', $mockService);

        $statusCode = $this->artisan('lrs:send-statements', [
            '--folder'  => 'example_1'
        ]);
        $this->assertEquals(0, $statusCode);

    }

}