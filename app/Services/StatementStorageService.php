<?php

namespace App\Services;

use App\Exceptions\StorageException;
use Illuminate\Support\Facades\Storage;
use App\Services\StatementServiceInterface;

class StatementStorageService implements StatementServiceInterface
{
    private $baseStorageUrl;
    private $backupStorageUrl;
    private $storagePermissionLevel;

    public function __construct($storagePermissionLevel = null)
    {   
        $this->baseStorageUrl = env('STORAGE_PATH', '');
        $this->backupStorageUrl = env('STORAGE_BACKUP_PATH', '');
        $this->storagePermissionLevel = $storagePermissionLevel;
    }

    /**
     * Saves multiple statements to local storage
     *
     * @param  array $statements
     * @return boolean
     * @throws StorageException
     */
    public function store(array $statements, string $folder)
    {   
        $completeStringData = '';
        $filePath = $this->baseStorageUrl . DIRECTORY_SEPARATOR . $folder;
        $filePathBackup = $this->backupStorageUrl . DIRECTORY_SEPARATOR . $folder;
        if (!count($statements)) {
            return true;
        }

        if (!Storage::exists($filePath)) {
            Storage::makeDirectory($filePath, $this->storagePermissionLevel ?: 0744, true, true); 
        }
        if (!Storage::exists($filePathBackup)) {
            Storage::makeDirectory($filePathBackup, $this->storagePermissionLevel ?: 0744, true, true);
        }
        
        $filename = $statements[0]['statement']['id'] . '.json';
        
        $glue = '';
        foreach ($statements as $statement) {
            $completeStringData .= $glue . json_encode($statement, JSON_UNESCAPED_SLASHES);
            $glue = ', ';
        }
        
        if (!Storage::put($filePath . DIRECTORY_SEPARATOR . $filename, $completeStringData)) {
            throw new StorageException('an internal error occurred while adding a new statements file to the proper path');
        }
        
        return true;
    }

    /**
     * Gets all data saved in a given folder
     *
     * @param string $folder
     * @param bool $delete
     * @param bool $backup
     * @throws StorageException
     * @return string
     */
    public function read(string $folder, $delete = true, $backup = false) {
        $content = '';
        $path = !$backup ? $this->baseStorageUrl . DIRECTORY_SEPARATOR . $folder : $this->backupStorageUrl . DIRECTORY_SEPARATOR . $folder;
        
        if (!Storage::exists($path)) {
            throw new StorageException('The folder ' . $folder . ' doesn\'t exists. Cannot access path '. $path);
        }
        $glue = '';
        $files = array_sort(Storage::files($path), function($f) {   
            return -(Storage::lastModified($f));
        });
        foreach ($files as $file) {
            $content .= $glue . Storage::disk('local')->get($file);
            $glue = ', ';
            if ($delete) {
                Storage::delete($file);
            }
        }   
        if (empty($content)) {
            return null;
        }
        
        return '['. $content . ']'; 
    }


    /**
     * Backup a given string of data from a specific folder
     *
     * @param string $content
     * @param string $folder
     * @return void
     * @throws StorageException
     */
    public function storeBackup(string $content, string $folder)
    {   
        $path_backup = $this->backupStorageUrl . DIRECTORY_SEPARATOR . $folder;
        $fileName = date("YmdHis_", time()) . substr(md5(mt_rand()), 0, 5); 

        if (!Storage::exists($path_backup)) {
            Storage::makeDirectory($path_backup, $this->storagePermissionLevel ?: 0744, true, true);
        }
        
        if (!Storage::put($path_backup . DIRECTORY_SEPARATOR . $fileName . '.json', $content)) { 
            throw new StorageException('an internal error occurred while saving a backup of all data in '. $folder . ' folder.');
        }  
    }


}