<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Services\StatementStorageService;
use App\Repositories\StatementRepository;
use Illuminate\Support\Facades\Storage;



/**
 * Class sendAllDirectoriesFiles Command
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class SendAllDirectoriesFiles extends Command
{    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "lrs:send-all-statements {--v}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Send statements from all the current folders. Each file sent is deleted automatically.";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $path = env('STORAGE_PATH');

        $statementRepository = new StatementRepository();
        $statementService    = new StatementStorageService();
        
        try{
            $this->info("Begin");
            
            foreach (Storage::directories($path) as $relativePathDirectory) {
                $this->info("Directory " . DIRECTORY_SEPARATOR . $relativePathDirectory);
                $splittedPath = explode(DIRECTORY_SEPARATOR, $relativePathDirectory);
                $folder = $splittedPath[count($splittedPath)-1];

                $content = $statementService->read($folder);

                if (empty($content)) {
                    $this->warn("this directory is empty");
                    continue;
                }

                $statementService->storeBackup($content, $folder);
                $this->info("Backup succesfully created.");
                $statementRepository->store($content, $folder);
                $this->info("Data successfully sent.");                
            }
        } catch (Exception $e) {
            $message = ($this->option('v')) ? ': ' . $e->getMessage() : ', please add --v for more details';
            $this->error("An error occurred" . $message);            
        }
        $this->info("Directories successfully inspected");
    }
}