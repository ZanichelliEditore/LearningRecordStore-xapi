<?php

use App\Locker\HelperTest;
use Illuminate\Support\Facades\Storage;
use App\Console\Commands\DeleteDirectory;


class DeletingDirectoryCommandTest extends TestCase
{

    /** 
     * @test
     * @return void
     */
    public function noFolderGivenTest()
    {
        Storage::fake('local');

        $statusCode = $this->artisan('lrs:delete-directory', [
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

        $statusCode = $this->artisan('lrs:delete-directory', [
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
        Storage::makeDirectory(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'example_e2');

        $statusCode = $this->artisan('lrs:delete-directory', [
            '--folder'  => 'example_e2'
        ]);
        
        $this->assertFalse(Storage::exists(HelperTest::STORAGE_PATH . DIRECTORY_SEPARATOR . 'example_e2')==1?true:false);
        $this->assertEquals(0, $statusCode);
    }

}