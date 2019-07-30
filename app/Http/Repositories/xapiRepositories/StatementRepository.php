<?php

namespace App\Http\Repositories\xapiRepositories;

use App\Models\Link;
use App\Models\Meta;
use Aws\S3\S3Client;
use App\Locker\Helper;
use Illuminate\Support\Facades\Log;
use App\Exceptions\StorageException;
use App\Http\Controllers\Controller;
use App\Services\StatementStorageService;

class StatementRepository implements StatementRepositoryInterface
{
    private $client;
    private $statementService;

    public function __construct()
    {
        $this->client = S3Client::factory([
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
        ]);
    }

    /**
     * Storing statements as S3 objects
     *
     * @param array $statements
     * @param string folder
     * @return boolean
     */
    public function store(string $statements, string $folder)
    {
        $keyname = date("YmdHis_", time()) . substr(md5(mt_rand()), 0, 5); 

        $object = $this->client->putObject([
            'Bucket' => env('AWS_BUCKET'),
            'Key'    => env('AWS_PATH_PREFIX', '') . $folder . DIRECTORY_SEPARATOR . $keyname . '.json',
            'Body'   => $statements
        ]);

        return $object['@metadata']['statusCode'] === 200;
    }

    /**
     * Return all statements saved
     *
     * @param string $folder
     * @param string|null $verb
     * @param string|null $limit
     * @return array|null
     */
    public function all(string $folder, $limit, $verb = null, int $page = 1)
    {
        $this->statementService = new StatementStorageService();

        try {
            $content = $this->statementService->read($folder, $delete = false);
            $contentBackup = $this->statementService->read($folder, $delete = false, $backup = true);     
        } catch (StorageException $e) { 
            return null;
        }
           
        if (!$content && !$contentBackup) {
            return null;
        } elseif (!$content) {
            $merge = json_decode($contentBackup);
        } elseif (!$contentBackup) {
            $merge = json_decode($content);
        } else {
            $merge = array_merge(json_decode($content), json_decode($contentBackup));
        }

        if (isset($verb)) {
            foreach ($merge as $key => $val) {
                if (strpos($val->statement->verb->id, $verb) === false) {
                    unset($merge[$key]);
                } 
            }
        }
        
        $realPage = $page - 1;
        $pagedArray = array_chunk($merge, $limit, true);
        if (empty($pagedArray) || !isset($pagedArray[$realPage])) {
            return null;
        }

        $keys = array_keys($pagedArray);
        $last_key = end($keys) + 1;
        $first_key = $keys[0] + 1;
        $prev_page = ($page === $first_key) ? null : ($page - 1);
        $next_page = ($page === $last_key) ? null : ($page + 1);
        $total = count($merge);
        $from = array_keys($pagedArray[$realPage])[0] + 1;
        $to = ($page === $last_key) ? $total : count($pagedArray[$realPage]) * $page; 
        $links = new Link($first_key, $last_key, $prev_page, $next_page);
        $meta = new Meta($page, $from, $last_key, $limit, $to, $total);

        $res = [
            "statements" => array_values($pagedArray[$realPage]),
            "links" => $links->getFields(),
            "meta" => $meta->getFields()
        ];
        
        return $res;   
    }

    /**
     * Get statement by id
     *
     * @param string $folder
     * @param string $id
     * @return Statement|null 
     */
    public function find(string $folder, string $id) {

        $limit = Controller::PAGINATION;
        $res = $this->all($folder, $limit);             
        if (!$res) {
            return null;
        }
        
        $content = $res['statements'];
        
        foreach ($content as $ele) {   
            if ($ele->statement->id == $id) {
                return $ele;
            }
        }
        return null;
    }
}