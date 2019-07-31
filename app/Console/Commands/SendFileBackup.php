<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\xapiRepositories\StatementRepository;



/**
 * Class sendFileBackupCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class SendFileBackup extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "lrs:send-backup-files {--folder=} {--v}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Send file from a specific backup folder 'applicationName' (e.g. 'losai') ";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(StatementRepository $statementRepository)
    {
        
        if (!$this->option('folder')) {
            $this->error("The folder is required");
            return 1;
        }
        $path = env('STORAGE_BACKUP_PATH', '');
        $folder = (string) $this->option('folder');

        if (!Storage::exists($path . DIRECTORY_SEPARATOR  . $folder)) {
            $this->warn("No directory found with the given name: cannot read from an unreal folder");
            return 1;
        }

        try {
            $this->info("Begin");
            $files = Storage::files($path . DIRECTORY_SEPARATOR . $folder);

            if (empty($files)) {
                $this->warn('The given folder is empty');
                return 0;
            }

            foreach ($files as $file) {
                $content = Storage::disk('local')->get($file);
                $statementRepository->store($content, $folder);
                $this->info(DIRECTORY_SEPARATOR . $file . ' sent');
                Storage::delete($file);
            }
            
        } catch (Exception $e) {
            $message = ($this->option('v')) ? ': ' . $e->getMessage() : ', please add --v for more details';
            $this->error("An error occurred" . $message);
            Log::info($e->getMessage());
            return 1;        
        }
        return 0;
    
    }
}