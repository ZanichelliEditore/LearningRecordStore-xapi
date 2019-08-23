<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\xapiRepositories\StatementRepository;
use App\Services\StatementStorageService;


/**
 * Class sendFileToS3Command
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class SendFileToS3 extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "lrs:send-statements {--folder=} {--v}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Send statements from a specific folder 'applicationName' (e.g. 'losai') ";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(StatementRepository $statementRepository, StatementStorageService $statementService)
    {

        if (!$this->option('folder')) {
            $this->error("The folder is required");
            return 1;
        }
        $folder = (string) $this->option('folder');
        $filePath = env('STORAGE_PATH','').DIRECTORY_SEPARATOR.$folder;

        if (!Storage::exists($filePath)) {
            $this->warn("No directory found with the given name: cannot read from an unreal folder");
            return 1;
        }

        try {
            $this->info("Begin");
            $content = $statementService->read($folder);
            if (empty($content)) {
                $this->warn("The given folder is empty, no statement found");
                return 0;
            }

            $statementService->storeBackup($content, $folder);
            $statementRepository->store($content, $folder);

            $this->info("File successfully sent");
        } catch (Exception $e) {
            $message = ($this->option('v')) ? ': ' . $e->getMessage() : ', please add --v for more details';
            $this->error("An error occurred" . $message);
            Log::info($e->getMessage());
            return 1; 
        }
        return 0;
    
    }
}