<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\xapiRepositories\StatementRepository;
use App\Services\StatementStorageService;



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
    protected $description = "Send statements from all the current folders.\n Each file sent is deleted automatically.";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(StatementRepository $statementRepository, StatementStorageService $statementService)
    {
        $path = env('STORAGE_PATH');
        
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
            Log::info($e->getMessage());
            return 1;      
        }
        $this->info("Directories successfully inspected");
        return 0;
        
    }
    
}