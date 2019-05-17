<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Services\StatementStorageService;



/**
 * Class CreateFileBackupCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class CreateFileBackup extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "lrs:create-file {--folder=} {--v}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create unique file of backup";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->statementService = new StatementStorageService;

        if (!$this->option('folder')) {
            $this->error("The folder is required");
            return false;
        }

        $folder = (string) $this->option('folder');
        $filePath = env('STORAGE_PATH','') . DIRECTORY_SEPARATOR .$folder;

        try {
            $this->info("Begin");

            if (!Storage::exists($filePath)) {
                $this->error("The given folder does not exists");
                return;
            }
            
            $content = $this->statementService->read($folder);
            if (empty($content)) {
                $this->warn("The given folder is empty");
                return;
            }
            $this->statementService->storeBackup($content, $folder);

            $this->info("File created");
        } catch (Exception $e) {
            $message = ($this->option('v')) ? ': ' . $e->getMessage() : ', please add --v for more details';
            $this->error("An error occurred" . $message);            
        }
    
    }
}
