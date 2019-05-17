<?php

namespace App\Http\Controllers;

use Exception;
use App\Locker\Helper;
use App\Models\Client;
use App\Locker\LockerLrs;
use App\Models\Statement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\XapiValidator;
use Illuminate\Support\Facades\Storage;
use App\Services\StatementStorageService;
use App\Repositories\StatementRepositoryInterface;

class StatementController extends Controller
{
    protected $statementRepository = null; 
    protected $statementService = null;

    public function __construct(StatementStorageService $statementService, StatementRepositoryInterface $statementRepository)
    {
        $this->statementService = $statementService;
        $this->statementRepository = $statementRepository;
    }
    
    /**
     * @OA\Post(
     *     path="/data/xAPI/statements",
     *     summary="Store xAPI statements",
     *     tags={"xAPI"},
     *     security={{"basicAuth":{}}},
     *     description="Use to store statements",
     *     operationId="StatementsController.store",
     *     @OA\RequestBody(
     *         description="General structure to follow to send a statement",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Statements")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         ref="#/components/responses/Success200"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         ref="#/components/responses/Error400"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         ref="#/components/responses/Error401"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         ref="#/components/responses/Error500"
     *     )
     * )
     */

    /**
     * Save the statements
     *
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        $xapiValidator = new XapiValidator();
        $statementIds = [];
        $computedStatement = [];
        $lrs = LockerLrs::getLrsFromAuth($request);        
        $authority = LockerLrs::getAuthorityFromAuth($request);
        $folder = $lrs->folder;

        if (!is_array($request->input('0'))) {
            $error = $xapiValidator->parserException($request);
            if (isset($error)) {
                return $error;
            }
            
            $this->statement = $request;
            $statementIds = $xapiValidator->validateStatement($request, $this->statement, $authority);           

            if (!is_string($statementIds)) {
                return $statementIds;
            }
            
            $computedStatement[] = (new Statement($this->statement->all()))->jsonSerialize();
        }        
        $statementIds = (array) $statementIds;

        for ($i = 0; $request->input($i); $i++) {
            $req = new Request;
            $req->replace($request->input($i));

            $error = $xapiValidator->parserException($req);
            if (isset($error)) {
                return $error;
            }

            $this->statement = $request->input($i);
            $validateResponse  = $xapiValidator->validateStatement($req, $this->statement, $authority);

            if (!is_string($validateResponse)) {
                $content = json_decode($validateResponse->getContent(), true);
                return Helper::getResponse(implode($content['message']));
            }
            $statementIds[] = $validateResponse;
            $computedStatement[] = (new Statement($this->statement))->jsonSerialize();
        }

        try {
            if (!$this->statementService->store($computedStatement, $folder)) {
                return Helper::getResponse('Storage access exception: an internal error occurred while adding a new statements file to the proper path', 500);
            }
        } catch(Exception $e) {
            Log::error($e->getMessage());
            return Helper::getResponse($e->getMessage(), $e->getCode());
        }

        return response()->json($statementIds);
    }

    /**
     * @OA\Get(
     *     path="/data/xAPI/statements",
     *     summary="List of statements",
     *     tags={"xAPI"},
     *     security={{"basicAuth":{}}},
     *     description="Use to get statement objects",
     *     operationId="StatementController.getList",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="maximum number of results to return",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="verb",
     *         in="query",
     *         description="action registered in the statement",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="maximum number of results to return",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         ref="#/components/responses/Success200"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         ref="#/components/responses/Success204"
     *     )
     * )
    */
    /**
     * Get the statement with the unique id.
     * @param Request $request
     * @return array
     */
    public function getList(Request $request)
    {   
        
        $limit = $request->input('limit') ? (int) $request->input('limit') : self::PAGINATION;
        $verb = $request->input('verb') ? $request->input('verb') : null;
        $page = $request->input('page');
        $page = isset($page) ? $request->input('page') : 1;
        $lrs = LockerLrs::getLrsFromAuth($request);
        $folder = $lrs->folder;
        if (isset($page) && !is_numeric($page)) {
            return Helper::getResponse("The page parameter must be integer.", 400);
        } else { 
            $page = (int) $page; 
        }
        
        $content = $this->statementRepository->all($folder, $limit, $verb, $page);

        if ($content) {
            return response()->json($content);
        }

        return Helper::getResponse("No statement found", 204);
    }

    /**
     * @OA\Get(
     *     path="/data/xAPI/statements/{id}",
     *     summary="List of statements",
     *     tags={"xAPI"},
     *     security={{"basicAuth":{}}},
     *     description="Use to get statement object",
     *     operationId="StatementController.get",
     *     @OA\Parameter(
     *        in="path",
     *        required=true,
     *        description="search statement id (uuid)",
     *        name="id",
     *        @OA\Schema(
     *            type="string"
     *        )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         ref="#/components/responses/Success200"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         ref="#/components/responses/Success204"
     *     )
     * )
    */
    /**
     * Get the statement with the unique id.
     * @param Request $request
     * @param string $id Statement's UUID
     * @return Statement
     */
    public function get(Request $request, string $id)
    {   
        $lrs = LockerLrs::getLrsFromAuth($request);
        $folder = $lrs->folder;

        $statement = $this->statementRepository->find($folder, $id);

        if ($statement) {
            return response()->json($statement);
        }

        return Helper::getResponse("No statement found", 204);
    }

}