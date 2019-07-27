<?php


use App\Models\Lrs;
use \Mockery as Mockery;
use App\Locker\HelperTest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\ClientService;
use App\Http\Controllers\LrsController;
use Illuminate\Database\QueryException;
use App\Http\Repositories\LrsRepository;
use Laravel\Lumen\Testing\WithoutMiddleware;
use App\Http\Repositories\xapiRepositories\StatementRepository;

class LrsMethodTest extends TestCase
{
    use WithoutMiddleware;


    /**
     * creation class
     *
     * @return HelperTest
     */
    public function  help()
    {
        $helper = new HelperTest();
        return $helper;
    }

    /**
     * Create authority object
     * @return Authority
     */
    private function getObjAuth() {
        $object_auth = new Authority('Client', 'example@gmail.com');
        return $object_auth;
    }
    

    /**
     * @param boolean $savingSuccess
     * @param array|null $allSuccess
     * @param string|null $findSuccess
     * @return Mockery\MockInterface $mock
     */
    private function getMockRepo(bool $reading = false, $allSuccess = null, $findSuccess = null)
    {
        $mock = !$reading ? Mockery::mock(StatementRepository::class) : 
        Mockery::mock(StatementRepository::class)
            ->makePartial()
            ->shouldReceive([
                'all' => $allSuccess
            ])
            ->once()        
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\xapiRepositories\StatementRepository', $mock);
        return $mock;
    }

    /**
     * @param bool $client
     * @param bool $store
     * @return Mockery\MockInterface $mock
     */
    private function getMockService(bool $client = false, bool $store = true)
    {
        $exc = new QueryException("SQL contraints",[],new Exception);
        $mock = !$client ? Mockery::mock(ClientService::class) : 
        Mockery::mock(ClientService::class)
            ->makePartial()
            ->shouldReceive([
                'store' => $store
            ])        
            ->withAnyArgs()
            ->once()
            ->getMock();
        $this->app->instance('App\Service\ClientService', $mock);
        return $mock;
    }

     /**
     * @test
     * @return void
     */
    public function lrsStoreSuccessTest()
    {
        $lrs = factory(Lrs::class)->make();
        $mock = Mockery::mock(LrsRepository::class)
            ->makePartial()
            ->shouldReceive([
                'save' => true
            ])   
            ->once()     
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\LrsRepository', $mock);
        $lrsController = new LrsController($mock, $this->getMockRepo(), $this->getMockService(true));
        $lrsRequest = new Request($lrs->toArray());
        $response = $lrsController->store($lrsRequest);

        $this->assertEquals(201, $response->status());

    }

     /**
     * @test
     * @return void
     */
    public function lrsStoreFailLrsTest()
    {
        $exc = new QueryException("SQL contraints",[],new Exception);

        $lrs = factory(Lrs::class)->make();
        $mock = Mockery::mock(LrsRepository::class)
            ->makePartial()
            ->shouldReceive([
                'save' => false
            ])   
            ->once()     
            ->withAnyArgs()
            ->andThrow($exc)
            ->getMock();
        $this->app->instance('App\Http\Repositories\LrsRepository', $mock);

        $lrsController = new LrsController($mock, $this->getMockRepo(), $this->getMockService());
        
        $lrsRequest = new Request($lrs->toArray());
        $response = $lrsController->store($lrsRequest);

        $this->assertEquals(500, $response->status());

    }

     /**
     * @test
     * @return void
     */
    public function lrsStoreFailClientTest()
    {
        $exc = new QueryException("SQL contraints",[],new Exception);

        $lrs = factory(Lrs::class)->make();
        $mock = Mockery::mock(LrsRepository::class)
            ->makePartial()
            ->shouldReceive([
                'save' => true,
            ])   
            ->once()     
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\LrsRepository', $mock);

        $mock2 =Mockery::mock(ClientService::class)
            ->makePartial()
            ->shouldReceive([
                'store' => false
            ])        
            ->withAnyArgs()
            ->once()
            ->andThrow($exc)
            ->getMock();
        $this->app->instance('App\Service\ClientService', $mock2);

        $lrsController = new LrsController($mock, $this->getMockRepo(),$mock2);
        $lrsRequest = new Request($lrs->toArray());
        $response = $lrsController->store($lrsRequest);

        $this->assertEquals(500, $response->status());

    }

     /**
     * @test
     * @return void
     */
    public function lrsStoreValidationTest()
    {
        
        $mock = Mockery::mock(LrsRepository::class);
        $this->app->instance('App\Http\Repositories\LrsRepository', $mock);
        $lrsController = new LrsController($mock, $this->getMockRepo(), $this->getMockService());

        $lrs = factory(Lrs::class)->make([
            'title' => ''
        ]);
        $lrsRequest = new Request($lrs->toArray());
        $response = $lrsController->store($lrsRequest);

        $this->assertEquals(422, $response->status());

        $lrs->title = 'title1';
        $lrs->folder = '';
        $lrsRequest = new Request($lrs->toArray());
        $response = $lrsController->store($lrsRequest);

        $this->assertEquals(422, $response->status());

        $lrs->folder = 'folder1';
        $lrs->description = Str::random(201);
        $lrsRequest = new Request($lrs->toArray());
        $response = $lrsController->store($lrsRequest);

        $this->assertEquals(422, $response->status());

    }

    /**
     * @test
     * @return void
     */
    public function lrsGetStatementsSuccessTest()
    {
        $lrs = factory(Lrs::class)->make();
        $mock = Mockery::mock(LrsRepository::class)
            ->makePartial()
            ->shouldReceive([
                'find' => $lrs
            ])   
            ->once()     
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\LrsRepository', $mock);
        $lrsController = new LrsController($mock, $this->getMockRepo(true, ['key' => 'val']), $this->getMockService());
        $lrsRequest = new Request();
        $response = $lrsController->getStatements($lrsRequest, $lrs->_id);

        $this->assertEquals(200, $response->status());
    }

    /**
     * @test
     * @return void
     */
    public function lrsGetNotFoundLrsTest()
    {
        $lrs = factory(Lrs::class)->make();
        $mock = Mockery::mock(LrsRepository::class)
            ->makePartial()
            ->shouldReceive([
                'find' => null
            ])   
            ->once()     
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\LrsRepository', $mock);
        $lrsController = new LrsController($mock, $this->getMockRepo(), $this->getMockService());
        $lrsRequest = new Request();
        $response = $lrsController->getStatements($lrsRequest, $lrs->_id);

        $this->assertEquals(204, $response->status());
    }

    /**
     * @test
     * @return void
     */
    public function lrsGetNotFoundStatementsTest()
    {
        $lrs = factory(Lrs::class)->make();
        $mock = Mockery::mock(LrsRepository::class)
            ->makePartial()
            ->shouldReceive([
                'find' => $lrs
            ])   
            ->once()     
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\LrsRepository', $mock);
        $lrsController = new LrsController($mock, $this->getMockRepo(true, []), $this->getMockService());
        $lrsRequest = new Request();
        $response = $lrsController->getStatements($lrsRequest, $lrs->_id);

        $this->assertEquals(204, $response->status());
    }

     /**
     * @test
     * @return void
     */
    public function lrsDeleteSuccessTest()
    {
        $lrs = factory(Lrs::class)->make();
        $mock = Mockery::mock(LrsRepository::class)
            ->makePartial()
            ->shouldReceive([
                'find' => $lrs,
                'delete' => true
            ])
            ->once()   
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\LrsRepository', $mock);
        $lrsController = new LrsController($mock, $this->getMockRepo(), $this->getMockService());
        $response = $lrsController->destroy($lrs->_id);

        $this->assertEquals(200, $response->status());

    }

     /**
     * @test
     * @return void
     */
    public function lrsDeleteFailTest()
    {
        $lrs = factory(Lrs::class)->make();
        $mock = Mockery::mock(LrsRepository::class)
            ->makePartial()
            ->shouldReceive([
                'find' => $lrs,
                'delete' => false
            ])
            ->once()
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\LrsRepository', $mock);
        $lrsController = new LrsController($mock, $this->getMockRepo(), $this->getMockService());
        $response = $lrsController->destroy($lrs->_id);

        $this->assertEquals(500, $response->status());
    }

    /**
    * @test
    * @return void
    */
    public function lrsDeleteNotFoundTest()
    {
        $lrs = factory(Lrs::class)->make();
        $mock = Mockery::mock(LrsRepository::class)
            ->makePartial()
            ->shouldReceive([
                'find' => null
            ])
            ->once()      
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\LrsRepository', $mock);
        $lrsController = new LrsController($mock, $this->getMockRepo(), $this->getMockService());
        $response = $lrsController->destroy($lrs->_id);

        $this->assertEquals(404, $response->status());
    }

}