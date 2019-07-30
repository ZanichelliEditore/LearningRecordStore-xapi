<?php


use App\Models\Client;
use Rhumsaa\Uuid\Uuid;
use \Mockery as Mockery;
use App\Constants\Scope;
use App\Locker\LockerLrs;
use App\Models\Authority;
use \stdClass as stdClass;
use App\Locker\HelperTest;
use function GuzzleHttp\json_decode;
use App\Services\StatementStorageService;
use App\Http\Controllers\StatementController;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Http\Repositories\xapiRepositories\StatementRepository;

abstract class StatementBaseCase extends TestCase
{

    use DatabaseMigrations;

    /**
     * A setup method launched at the beginning of test
     *
     * @return void
    */
    public function setup():void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->artisan('db:seed');

        Client::where('api_basic_key', env('CLIENT_ID'))
                ->update(['scopes'  => '["' . Scope::STATEMENTS_WRITE . '"]']);

        Storage::fake('local');
    }

    /**
     * A setup method launched at the end of test
     *
     * @return void
    */
    public function tearDown()
    {
        HelperTest::deleteTestingFolders();
        $this->artisan('migrate:reset');
        parent::tearDown();
    }

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

    public abstract function authentication();

    public abstract function getRequest();

    /**
     * send statement in test
     *
     * @param array $body
     * @return Response response
    */
    private function sendStatements($body)
    {
        return $this->call('POST', HelperTest::URL, $body, [], [],  $this->authentication());
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
     * Validate message response
     *
     * @param array $message
     * @return void 
     */
    public function validateResponse($message)
    {
        $content = [
            "error" => true,
            "success" => false,
            "message" => $message,
            "code" => 400
        ];

        $this->seeJson($content);
    }

    /**
     * @param boolean $savingSuccess
     * @param boolean $reading
     * @return Mockery\MockInterface $mock
     */
    public function getMock(bool $savingSuccess = true, bool $reading = false)
    {        
        $mock = $reading ? Mockery::mock(StatementStorageService::class) :
        Mockery::mock(StatementStorageService::class)
            ->makePartial()
            ->shouldReceive([
                'store' => $savingSuccess
            ])        
            ->withAnyArgs()
            ->once()
            ->getMock();
        $this->app->instance('App\Services\StatementStorageService', $mock);
        return $mock;
    }

    /**
     * @param boolean $savingSuccess
     * @param array|null $allSuccess
     * @param string|null $findSuccess
     * @return Mockery\MockInterface $mock
     */
    public function getMockRepo(bool $reading = false, $allSuccess = null, $findSuccess = null)
    {
        $mock = !$reading ? Mockery::mock(StatementRepository::class) : 
        Mockery::mock(StatementRepository::class)
            ->makePartial()
            ->shouldReceive([
                'all' => $allSuccess,
                'find' => $findSuccess
            ])        
            ->withAnyArgs()
            ->getMock();
        $this->app->instance('App\Http\Repositories\xapiRepositories\StatementRepository', $mock);
        return $mock;
    }

    /**
     * @param boolean $savingSuccess
     * @param boolean $reading
     * @return Mockery\MockInterface $mock
     */
    public function getMockLocker(array $classes = [])
    {        
        $object_lrs = new stdClass;
        $object_lrs->folder = 'test';
        $object_lrs->_id = 'object_id';
        
        $mocked = [
            'getLrsFromAuth' => $object_lrs,
            'getAuthorityFromAuth' => $this->getObjAuth(),
            'getClientId' => 'clientId'
        ];
        $classes =  !empty($classes) ? $classes : $mocked;   

        $classesMocked = array_intersect_key($mocked, $classes);
        $mock = Mockery::mock(LockerLrs::class)
            ->makePartial()
            ->shouldReceive($classesMocked)        
            ->withAnyArgs()
            ->once()
            ->getMock();
        $this->app->instance('App\Locker\LockerLrs', $mock);
        return $mock;
    }

    /**
     * @test
     * @return void
     */
    public function statementErrorAuthenticationTest()
    {
        $helper   = $this->help();
        $header = [];
        $response = $this->call('POST', HelperTest::URL, $helper->getStatement(), [], [], $header);
        $this->assertEquals(401, $response->status());
    }


    /**
     * @test
     * @return void
     */
    public function statementScopesFailTest()
    {

        Client::where('api_basic_key', env('CLIENT_ID'))
                ->update(['scopes'  => '["fail_test"]']);

        $helper   = $this->help();
        $response = $this->call('GET', HelperTest::URL, $helper->getStatement(), [], [], $this->authentication());

        $this->assertEquals(403, $response->status());
    }

    /**
     * @test
     * @return voidfolder
     */
    public function statementScopesSuccessTest()
    {
 
        $helper   = $this->help();

        $response = $this->call('POST', HelperTest::URL, $helper->getStatement(), [], [], $this->authentication());
        $this->assertEquals(200, $response->status());

        Client::where('api_basic_key', env('CLIENT_ID'))
                ->update(['scopes'  => '["' . Scope::STATEMENTS_READ . '"]']);

        $response = $this->call('GET', HelperTest::URL, [], [], [], $this->authentication());
        $this->assertEquals(200, $response->status());
    }

    /**
     * @test
     * @return void
     */
    public function statementScopesAllTest()
    {

        Client::where('api_basic_key', env('CLIENT_ID'))
                ->update(['scopes'  => '["' . Scope::ALL . '"]']);

        $helper   = $this->help();

        $response = $this->call('POST', HelperTest::URL, $helper->getStatement(), [], [], $this->authentication());
        $this->assertEquals(200, $response->status());

        $response = $this->call('GET', HelperTest::URL, [], [], [], $this->authentication());
        $this->assertEquals(200, $response->status());

    }

     /**
     * @test
     * @return void
     */
    public function statementPostSuccessTest()
    {
        $statementRequest = $this->getRequest();
        $statementController = new StatementController($this->getMock(), $this->getMockRepo(), $this->getMockLocker());
        $statementRequest->replace($this->help()->getStatement());

        $response = $statementController->store($statementRequest);

        $this->assertEquals(200, $response->status());
    }

     /**
     * @test
     * @return void
     */
    public function baseStatementPostSuccessTest()
    {

        $statement = $this->help()->getStatement();
        unset($statement['context']);
        unset($statement['result']);
        unset($statement['version']);
        unset($statement['timestamp']);
        unset($statement['authority']);
        unset($statement['actor']['name']);
        unset($statement['verb']['display']);
        unset($statement['object']['definition']);

        $response = $this->sendStatements($statement);
        $this->assertEquals(200, $response->status());
    }

    /**
     * @test
     * @return void
     */
    public function statementsPostSuccessTest()
    {

        $statementRequest = $this->getRequest();        
        $statementController = new StatementController($this->getMock(), $this->getMockRepo(), $this->getMockLocker());
        $helper = $this->help();
        $statementRequest->replace([$helper->getStatement(), $helper->getStatement()]);
        
        $response = $statementController->store($statementRequest);
        $this->assertEquals(200, $response->status());
    }

     /**
     * @test
     * @return void
     */
    public function statementSuccessIdFileTest()
    {
        $statementRequest = $this->getRequest();        
        $statementController = new StatementController($this->getMock(), $this->getMockRepo(),$this->getMockLocker());
        $uid = (string) Uuid::uuid1();
        $statementRequest->replace($this->help()->getStatementWithUuid($uid));

        $response = $statementController->store($statementRequest);
        $uidFile = json_decode($response->getContent())[0];
        $this->assertEquals($uid, $uidFile);
    }

     /**
     * @test
     * @return void
     */
    public function statementReadAllSuccessTest()
    {      
        Client::where('api_basic_key', env('CLIENT_ID'))
                ->update(['scopes'  => '["' . Scope::STATEMENTS_READ . '"]']);

        
        $statementRequest = $this->getRequest();
        $statement = array(0 => $this->help()->getStatement());
        $statementController = new StatementController($this->getMock(true, true), $this->getMockRepo(true, $statement), $this->getMockLocker(['getLrsFromAuth' => $this->getObjAuth()]));
        
        $response = $statementController->getList($statementRequest);
        $this->assertEquals(200, $response->status());
    }

     /**
     * @test
     * @return void
     */
    public function statementReadSuccessTest()
    {
        Client::where('api_basic_key', env('CLIENT_ID'))
                ->update(['scopes'  => '["' . Scope::STATEMENTS_READ . '"]']);

        $id = (string) Uuid::uuid1();
        $statementRequest = $this->getRequest();        
        $statementController = new StatementController($this->getMock(true, true),  $this->getMockRepo(true, null, $this->help()->getStatementWithUuid($id)), $this->getMockLocker(['getLrsFromAuth' => $this->getObjAuth()]));        

        $response = $statementController->get($statementRequest, $id);
        $this->assertEquals(200, $response->status());
    }

     /**
     * @test
     * @return void
     */
    public function statementReadAllEmptyTest()
    {      
        Client::where('api_basic_key', env('CLIENT_ID'))
                ->update(['scopes'  => '["' . Scope::STATEMENTS_READ . '"]']);

        $statementRequest = $this->getRequest();     
        $statementController = new StatementController($this->getMock(true, true), $this->getMockRepo(true, null), $this->getMockLocker(['getLrsFromAuth' => $this->getObjAuth()]));

        $response = $statementController->getList($statementRequest);
        $this->assertEquals(204, $response->status());
    }

    /**
     * @test
     * @return void
     */
    public function statementReadEmptyTest()
    {      
        Client::where('api_basic_key', env('CLIENT_ID'))
                ->update(['scopes'  => '["' . Scope::STATEMENTS_READ . '"]']);

        $statementRequest = $this->getRequest();
        $statementController = new StatementController($this->getMock(true, true),  $this->getMockRepo(true, null, null), $this->getMockLocker(['getLrsFromAuth' => $this->getObjAuth()]));
        $id = (string) Uuid::uuid1();

        $response = $statementController->get($statementRequest, $id);
        $this->assertEquals(204, $response->status());
    }

    /**
    * @test
    * @return void
    */
    public function sendingStatementsWithFailureTest()
    {
        $helper = $this->help();
        $statement = $helper->getStatement();
        $otherStatement = $helper->getStatement();
        $otherStatement['object']['id'] = "test";

        $response = $this->sendStatements([$statement, $otherStatement]);
        $this->assertEquals(400, $response->status());

        $message = ["The object.id format is invalid."];
        $this->validateResponse($message);
    }

    /**
     * @test
     * @return void
     */
    public function statementObjectIdErrorValidationTest()
    {   
        $statement = $this->help()->getStatement();
        unset($statement['object']['id']);
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = ["The object.id field is required unless object.object type is in SubStatement."];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementActorObjectTypeErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['actor']['objectType'] = 'Group';
        $statement['object']['objectType'] = 'Agent';
        unset($statement['context']['platform']);
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = [
            "The selected actor.object type is invalid."
        ];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementObjObjectTypeErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['object']['objectType'] = 'Test';
        unset($statement['context']['platform']);
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = [
            "The selected object.object type is invalid."
        ];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementContextErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['object']['objectType'] = 'Agent';
        $statement['context']['revision'] = 'string';        
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = [
            "The platform property MUST only be used if the Statement Object is an Activity."
        ];
        $this->validateResponse($message);
    }

        /**
    * @test
    * @return void
    */
    public function statementContextRevisionErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['object']['objectType'] = 'Agent';
        unset($statement['context']['platform']);
        $statement['context']['revision'] = 'string';        
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = [
            "The revision property MUST only be used if the Statement Object is an Activity."
        ];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementVerbIdErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        unset($statement['verb']['id']);
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = ["The verb.id field is required."];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementAccountHPErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        unset($statement['actor']['account']['homePage']);
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = ["The actor.account.home page field is required when actor.account is present."];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementAccountNameErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        unset($statement['actor']['account']['name']);
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = ["The actor.account.name field is required when actor.account is present."];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementExtensionsErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['context']['extensions']['test'] = $statement['context']['extensions']["https://example.com/xapi/activities"];
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = ["The keys of an extensions map MUST be IRIs."];
        $this->validateResponse($message);

        $statement['context']['extensions'] = [];
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementLangMapErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['verb']['display']['12'] = $statement['verb']['display']['en-US'];
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = ["The property MUST be used to illustrate the meaning which is already determined by the Verb IRI"];
        $this->validateResponse($message);

        $statement['verb']['display'] = 'test';
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());
        $this->validateResponse($message);

        $statement['verb']['display'] = [];
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());
        $this->validateResponse($message);
    }

    /**
     * @test
     * @return void
     */
    public function statementActorErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        unset($statement['actor']);
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = [
            "The actor field is required.",
            "The actor.account field is required when none of actor.mbox / actor.open id / actor.mbox sha1sum are present.",
            "The actor.mbox field is required when none of actor.account / actor.open id / actor.mbox sha1sum are present.",
            "The actor.mbox sha1sum field is required when none of actor.mbox / actor.open id / actor.account are present.",
            "The actor.openid field is required when none of actor.mbox / actor.account / actor.mbox sha1sum are present."
        ];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementStringErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['actor']['name'] = 12345;
        $statement['actor']['account']['name'] = 12345;
        $statement['actor']['account']['homePage'] = 12345;
        $statement['object']['objectType'] = 12345;
        $statement['context']['language'] = 12345;
        $statement['result']['response'] = 12345;
        $statement['authority']['objectType'] = 12345;
        $statement['authority']['name'] = 12345;
        $statement['authority']['mbox'] = 12345;
        $statement['version'] = 1;
        unset($statement['context']['platform']);
        unset($statement['context']['revision']);
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = [
            "The actor.account.home page must be a string.", 
            "The actor.account.name must be a string.",
            "The actor.name must be a string.",
            "The authority.mbox must be a string.The authority.mbox format is invalid.",
            "The authority.name must be a string.",
            "The authority.object type must be a string.The selected authority.object type is invalid.",
            "The context.language must be a string.",
            "The object.object type must be a string.The selected object.object type is invalid.",
            "The result.response must be a string.",
            "The version must be a string.The version format is invalid."
        ];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementBooleanErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['result']['completion'] = "false";
        $statement['result']['success'] = "false";
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = [
            "The result.completion field must be true or false.",
            "The result.success field must be true or false."
        ];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementUuidErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['id'] = "12321jgifjdgidjosd-sadsdsa";
        $statement['context']['registration'] = "aasdasdas-2134fef9w9f-42f";
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = [
            "validation.uuid",
            "validation.uuid"
        ];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementIRIErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['verb']['id'] = "test";
        $statement['object']['definition']['type'] = "test";
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = [
            "The object.definition.type format is invalid.",
            "The verb.id format is invalid."
        ];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementArrayErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['context']['contextActivities']['parent'] = "test";
        $statement['object']['member'] = "test";
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = [
            "The context.contextActivities.parent must be an array.",
            "The object.member must be an array."
        ];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementMboxErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['authority']['mbox'] = "test@applicazione";
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = [
            "The authority.mbox format is invalid."
        ];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementScoreErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['result']['score']['max'] = '4';
        $statement['result']['score']['min'] = '1';
        $statement['result']['score']['raw'] = '6';
        $statement['result']['score']['scaled'] = '2.5';
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = [
            "The result.score.raw may not be greater than 4.",
            "The result.score.scaled must be between -1 and 1."
        ];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementDurationErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['result']['duration'] = "00:15:30.20";
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = ["The duration must be espressed in ISO8601 format, e.g. PT4H35M59.14S"];
        $this->validateResponse($message);
    }

    /**
    * @test
    * @return void
    */
    public function statementStatementRefErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['object']['objectType'] = "StatementRef";
        unset($statement['context']['platform']);
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = ["validation.uuid"];
        $this->validateResponse($message);
    }

    /**
     * @test
     * @return void
     */
    public function statementTimestampErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['timestamp'] = "2019-01-29";
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = ["The timestamp format is invalid."];
        $this->validateResponse($message);
    }

    /**
     * @test
     * @return void
     */
    public function statementVersionErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['version'] = "2.0.0";
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = ["The version format is invalid."];
        $this->validateResponse($message);
    }

     /**
     * @test
     * @return void
     */
    public function statementSubStatementIdErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['object']['objectType'] = "SubStatement";
        unset($statement['context']);
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = ["Invalid field inside object. SubStatement must not have id, authority, version, stored"];
        $this->validateResponse($message);
    }

     /**
     * @test
     * @return void
     */
    public function sendingInvalidKeyErrorValidationTest()
    {
        $statement = $this->help()->getStatement();
        $statement['test'] = "Prova";
        $response = $this->sendStatements($statement);
        $this->assertEquals(400, $response->status());

        $message = ["Incorrect statement structure: unprocessable fields found."];
        $this->validateResponse($message);
    }

    /**
     * @test
     *
     * @return void
     */
    public function statementsPostSubstatementSuccessTest()
    {   
        $response = $this->sendStatements($this->help()->getStatementWithSubstatement());
        $this->assertEquals(200, $response->status());
    }

    /**
     * @test
     *
     * @return void
     */
    public function statementsFailedStoringTest()
    {   
        $statementController = new StatementController($this->getMock(false), $this->getMockRepo(), $this->getMockLocker());
        $response = $this->sendStatements($this->help()->getStatementWithSubstatement());
        $this->assertEquals(500, $response->status());
    }

    /**
     * @test
     *
     * @return void
     */
    public function sendingSubstatementWithObjectIdValidationTest()
    {
        
        $statement = $this->help()->getStatementWithSubstatement();
        $statement['object']['id'] = "https://w3id.org/xapi/keys/object-id";
        
        $response = $this->sendStatements($statement);
        
        $this->assertEquals(400, $response->status());
        $this->validateResponse([ 'Invalid field inside object. SubStatement must not have id, authority, version, stored']);
    }

     /**
     * @test
     *
     * @return void
     */
    public function sendingSubstatementWithObjectAuthorityValidationTest()
    {  
        
        $statement = $this->help()->getStatementWithSubstatement();
        $statement['object']['authority'] = [
            "objectType" => "Agent",
            "name" => "Client",
            "mbox" => "mailto:mail@test.com"
        ];

        $response = $this->sendStatements($statement);
        
        $this->assertEquals(400, $response->status());
        $this->validateResponse(['Incorrect statement structure: unprocessable fields found.']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function sendingNestedSubstatementValidationTest()
    {
        $statement = $this->help()->getStatementWithSubstatement();
        $statement['object']['object']['objectType'] = "SubStatement";
        
        $response = $this->sendStatements($statement);
        
        $this->assertEquals(400, $response->status());
        $this->validateResponse(['The selected object.object.object type is invalid.']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function sendingInvalidExtensionWithinSubstatementValidationTest()
    {
        $statement = $this->help()->getStatementWithSubstatement();
        $statement['object']['context'] =  [
            "platform" => "APP",
            "contextActivities" => [
                "parent" => ["id" => "http://www.example.com/xapi/activities/else"]
            ],
            "registration" => "0ab5f76e-3389-11e9-873f-6aa43c3ec3b2"
        ];
        $statement['object']['context']['extensions'] =  ['test' => "https://example.com/xapi/activities"];
        
        $response = $this->sendStatements($statement);
        
        $this->assertEquals(400, $response->status());
        $this->validateResponse(['The keys of an extensions map MUST be IRIs.']);
    }

}