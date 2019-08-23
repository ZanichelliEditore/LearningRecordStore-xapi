<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Class createFileBackupCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class BackupCleanup extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "lrs:backup-cleanup {--folder=  : The folder backup to delete} {--v}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Delete all backup files stored seven or more days ago";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = env('STORAGE_BACKUP_PATH');
        
        if ($this->option('folder')) {
            $folders = [$path . DIRECTORY_SEPARATOR . $this->option('folder')];
            
            if (!Storage::exists($folders[0])) {
                $this->warn("No directory found with the given name");
                return 1;
            }

        } else {
            $this->warn("No folder given, all backup directories will be cleaned up");
            $folders = Storage::directories(env('STORAGE_BACKUP_PATH'));            
        }
        
        $timeLimit = strtotime('-7 days', strtotime('today')); 

        $this->info('Begin');

        try {
            foreach ($folders as $folder) {      
                foreach (Storage::files($folder) as $file) {
                    if (Storage::lastModified($file) <= $timeLimit) {
                        Storage::delete($file);
                        $this->info('File ' . $file . ' deleted');
                    }
                }
            }
        } catch (Exception $e) {
            $message = ($this->option('v')) ? ': ' . $e->getMessage() : ', please add --v for more details';
            $this->error("An error occurred" . $message);
            Log::info($e->getMessage());
            return 1;
        }
        $this->info('Backup data cleaned up succesfully.');
        return 0;
    }
}