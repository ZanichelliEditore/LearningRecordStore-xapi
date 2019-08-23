<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Class deletePostsCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class DeleteDirectory extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "lrs:delete-directory {--folder=} {--v}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "delete a folder with all files. Specify folder path from 'app'";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->option('folder')) {
            $this->error("The folder is required");
            return 1;
        }

        $folder   = (string) $this->option('folder');
        $filePath = env('STORAGE_PATH','').DIRECTORY_SEPARATOR.$folder;
        if (!Storage::exists($filePath)) {
            $this->warn("No directory found with the given name");
            return 1;
        }
        
        try {

            $this->info("Begin");
            Storage::deleteDirectory($filePath); 
            $this->info("Directory deleted successfully");

        } catch (Exception $e) {
            $message = ($this->option('v')) ? ': '.$e->getMessage() : ', please add --v for more details';
            $this->error("An error occurred". $message);
            Log::info($e->getMessage());
            return 1;        
        }
        return 0;
    
    }
}