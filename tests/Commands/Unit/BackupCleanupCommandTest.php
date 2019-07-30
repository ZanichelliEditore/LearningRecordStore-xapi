<?php

use App\Locker\HelperTest;
use App\Console\Commands\BackupCleanup;
use Illuminate\Support\Facades\Storage;


class BackupCleanupCommandTest extends TestCase
{

    /** 
     * @test
     * @return void
     */
    public function unrealFolderTest()
    {
        Storage::fake('local');

        $statusCode = $this->artisan('lrs:backup-cleanup', [
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
        $filePath = HelperTest::STORAGE_BACKUP_PATH . DIRECTORY_SEPARATOR . 'example_1';

        Storage::fake('local');
        Storage::makeDirectory($filePath);

        $statusCode = $this->artisan('lrs:backup-cleanup', [
            '--folder'  => 'example_1'
        ]);
        $this->assertEquals(0, $statusCode);
    }

}