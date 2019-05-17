<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     * @codeCoverageIgnore
     * @var array
     */
    protected $commands = [
        Commands\SendFileToS3::class,
        Commands\SendFileBackup::class,
        Commands\CreateFileBackup::class,
        Commands\DeleteDirectory::class,
        Commands\SendAllDirectoriesFiles::class,
        Commands\BackupCleanup::class
    ];

    /**
     * Define the application's command schedule.
     * @codeCoverageIgnore
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
