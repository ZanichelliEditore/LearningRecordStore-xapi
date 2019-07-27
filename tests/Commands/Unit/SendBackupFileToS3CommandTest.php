<?php

use Rhumsaa\Uuid\Uuid;
use \Mockery as Mockery;
use App\Locker\HelperTest;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\xapiRepositories\StatementRepository;

class SendBackupFileToS3CommandTest extends TestCase
{
    
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
    public function noFolderGivenTest()
    {
        Storage::fake('local');

        $statusCode = $this->artisan('lrs:send-backup-files', [
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

        $statusCode = $this->artisan('lrs:send-backup-files', [
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
        Storage::makeDirectory(HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'example_1');
        
        $statusCode = $this->artisan('lrs:send-backup-files', [
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
        $uid = (string) Uuid::uuid1();
        $filePath = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'example_1';

        Storage::fake('local');
        Storage::makeDirectory($filePath);

        $statement = json_encode($this->help()->getStatementWithUuid($uid), JSON_UNESCAPED_SLASHES);
        Storage::put($filePath . DIRECTORY_SEPARATOR . $uid . '.json', $statement);

        $mockRepository = Mockery::mock(StatementRepository::class)->makePartial()
            ->shouldReceive(['store' => true])
            ->withAnyArgs()
            ->once()
            ->getMock();
        $this->app->instance('App\Http\Repositories\xapiRepositories\StatementRepository', $mockRepository);

        $statusCode = $this->artisan('lrs:send-backup-files', [
            '--folder'  => 'example_1'
        ]);
        $this->assertEquals(0, $statusCode);

    }
}