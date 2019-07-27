<?php

namespace App\Http\Controllers;

use App\Models\Lrs;
use App\Locker\Helper;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\LrsResource;
use Illuminate\Database\QueryException;
use App\Services\ClientServiceInterface;
use Illuminate\Support\Facades\Validator;
use App\Http\Repositories\RepositoryInterface;
use App\Http\Repositories\xapiRepositories\StatementRepositoryInterface;

class LrsController extends Controller
{
    protected $lrsRepository;
    protected $clientService;

    public function __construct(RepositoryInterface $lrsInterface, StatementRepositoryInterface $statementInterface, ClientServiceInterface $clientService)
    {
        $this->lrsRepository = $lrsInterface;
        $this->statementRepository = $statementInterface;
        $this->clientService = $clientService;
    }

    /**
     * @OA\Get(
     *     path="/lrs",
     *     summary="List of lrs",
     *     tags={"Lrs"},
     *     security={{"passport":{"lrs/read"}}},
     *     description="Use to get lrs objects",
     *     operationId="LrsController.getList",
     *     @OA\Parameter(
     *        name="q",
     *        in="query",
     *        required=false,
     *        description="search title or folder",
     *        @OA\Schema(
     *            type="string"
     *        )
     *     ),
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
     *         name="order",
     *         in="query",
     *         description="type of order: ASC, DESC",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="orderBy",
     *         in="query",
     *         description="field to order: title - lrs_id - description",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         ref="#/components/responses/Success200"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         ref="#/components/responses/Success204"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         ref="#/components/responses/Error403"
     *     )
     * )
    */
    /**
     * Get all lrs
     * @param Request $request
     * @return collection
    */
    public function getList(Request $request)
    {
        $limit = $request->input('limit', self::PAGINATION);
        $query = $request->input('q');
        $order = $request->input('order', 'DESC');
        $orderBy = $request->input('orderBy', 'updated_at');
        $lrs = $this->lrsRepository->all($query, $orderBy, $order, $limit);

        return LrsResource::collection($lrs);
    }

    /**
     * @OA\Get(
     *     path="/lrs/{id}/statements",
     *     summary="List of statements",
     *     tags={"Lrs"},
     *     security={{"passport":{"lrs/read", "statements/read"}}},
     *     description="Use to get statement objects",
     *     operationId="LrsController.getStatements",
     *     @OA\Parameter(
     *        in="path",
     *        required=true,
     *        description="Lrs id to find",
     *        name="id",
     *        @OA\Schema(
     *            type="string"
     *        )
     *     ),
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
     *     ),
     *     @OA\Response(
     *         response=400,
     *         ref="#/components/responses/Error400"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         ref="#/components/responses/Error403"
     *     )
     * )
    */
     /**
     * Display statements for this LRS
     * @param Request $request
     * @param string $id
     * @return Response
    */
    public function getStatements(Request $request, $id)
    {    
        $lrs = $this->lrsRepository->find($id);
        if (!$lrs) {
            return response("No Lrs found.", 204);
        }
        $folder = $lrs->folder;

        $limit = $request->input('limit') ? (int) $request->input('limit') : self::PAGINATION;
        $verb = $request->input('verb') ? $request->input('verb') : null;
        $page = $request->input('page');
        $page = isset($page) ? $request->input('page') : 1;
        if (isset($page) && !is_numeric($page)) {
            return Helper::getResponse("The page parameter must be integer.", 400);
        } else { 
            $page = (int) $page; 
        }
        
        $content = $this->statementRepository->all($folder, $limit, $verb, $page);

        if ($content) {
            return response()->json($content);
        }

        return response(['message' => "No statement found"], 204);

    }

    /**
     * @OA\Post(
     *     path="/lrs",
     *     summary="Create a new statement",
     *     tags={"Lrs"},
     *     security={{"passport":{"lrs/write"}}},
     *     description="Create new lrs",
     *     operationId="LrsController.store",
     *     @OA\RequestBody(
     *         description="Structure of an Lrs (Learning record store).",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Lrs")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         ref="#/components/responses/Success201"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         ref="#/components/responses/Error422"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         ref="#/components/responses/Error500"
     *     )
     * )
    */
    /**
     * Create new Lrs
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = $this->validator($request->all());
        if($validator->fails()){
            return response([
                'message' => 'The data are invalid.',
                'errors' => $validator->errors()
            ], 422);
        }
        $lrs = new Lrs();
        $lrs->title = $request->input('title');
        $lrs->folder = $request->input('folder');
        $lrs->description = $request->input('description');
        $lrs->_id = substr(md5(uniqid(mt_rand(), true)), 0, 25);

        try{
            DB::transaction(function () use(&$lrs){
                $this->lrsRepository->save($lrs);
                $this->clientService->store($lrs->_id, $lrs->title);
            });
        }
        catch (QueryException | Exception $e){ 
            return response(['message' => "Error on create lrs.", 'errors' => $e->getMessage()], 500);
        }

        $data = Client::join('lrs', 'clients.lrs_id', '=', 'lrs._id')
                        ->select('lrs.*', 'clients.lrs_id', 'clients.api_basic_key', 'clients.api_basic_secret', 'clients.scopes')
                        ->where('lrs_id', $lrs->_id)
                        ->get();
        
        return response([
            'message' => "Lrs successfully created.",
            'data' => $data
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/lrs/{id}",
     *     summary="Delete",
     *     tags={"Lrs"},
     *     security={{"passport":{"all"}}},
     *     description="Use to delete lrs infos with clients connected",
     *     operationId="LrsController.destroy",
     *     @OA\Parameter(
     *        in="path",
     *        required=true,
     *        description="Lrs id to find",
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
     *         response=403,
     *         ref="#/components/responses/Error403"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         ref="#/components/responses/Error404"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         ref="#/components/responses/Error500"
     *     )
     * )
    */
    /**
     * Delete Lrs and connected clients
     *
     * @param string $id
     * @return Response
     */
    public function destroy($id)
    {
        $lrs = $this->lrsRepository->find($id);
        if (!$lrs) {
            return response(['message' => "Not found Lrs to delete."], 404);
        }

        if (!$this->lrsRepository->delete($lrs)) {
            return response(['message' => "Error on delete lrs."], 500);
        }
        return response(['message' => "Lrs successfully deleted."], 200);
    }

    /*
     * Returns the validator for user data
     * @param array
     * @return Validator
     */
    private function validator(array $data){
        return Validator::make($data, [
            'title' => 'required|string|max:100',
            'folder' => 'required|string|max:100',
            'description' => 'sometimes|string|max:200'
        ]);
    }
}
