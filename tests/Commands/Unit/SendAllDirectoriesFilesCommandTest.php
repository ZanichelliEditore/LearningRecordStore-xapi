<?php

use \Mockery as Mockery;
use App\Locker\HelperTest;
use function GuzzleHttp\json_encode;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\xapiRepositories\StatementRepository;
use App\Services\StatementStorageService;
use App\Console\Commands\SendAllDirectoriesFiles;

class SendAllDirectoriesFilesCommandTest extends TestCase
{

    /** 
     * @test
     * @return void
     */
    public function successCaseTest()
    {

        Storage::fake('local');
        Storage::makeDirectory(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR .'example_e1');
        
        $mockRepository = Mockery::mock(StatementRepository::class)->makePartial()
            ->shouldReceive(['store' => true])
            ->withAnyArgs()
            ->once()
            ->getMock();
        $this->app->instance('App\Http\Repositories\xapiRepositories\StatementRepository', $mockRepository);

        $mockService = Mockery::mock(StatementStorageService::class)->makePartial()
            ->shouldReceive(['storeBackup' => true, 'read' => true])
            ->withAnyArgs()
            ->once()
            ->getMock();
        $this->app->instance('App\Services\StatementStorageService', $mockService);

        $statusCode = $this->artisan('lrs:send-all-statements');
        $this->assertEquals(0, $statusCode);

        Storage::makeDirectory(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR .'example_e2');

        $mockRepository = Mockery::mock(StatementRepository::class)->makePartial()
            ->shouldReceive(['store' => true])
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\xapiRepositories\StatementRepository', $mockRepository);

        $mockService = Mockery::mock(StatementStorageService::class)->makePartial()
            ->shouldReceive(['storeBackup' => true, 'read' => true])
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Services\StatementStorageService', $mockService);

        $statusCode = $this->artisan('lrs:send-all-statements');
        $this->assertEquals(0, $statusCode);
        
    }

}