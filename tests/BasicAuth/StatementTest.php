<?php


use Exception as Exc;
use Rhumsaa\Uuid\Uuid;
use \Mockery as Mockery;
use App\Locker\HelperTest;
use Illuminate\Http\Request;
use function GuzzleHttp\json_decode;
use App\Repositories\StatementRepository;
use App\Services\StatementStorageService;
use App\Http\Controllers\StatementController;

class StatementTest extends TestCase
{
    /**
     * creation class
     *
     * @return HelperTest
     */
    private function  help()
    {
        $helper = new HelperTest();
        return $helper;
    }

    /**
     * send statement in test
     *
     * @param array $body
     * @return Response response
    */
    private function sendStatements($body)
    {
        $header = HelperTest::createBasicHeader();
        return $this->call('POST', HelperTest::URL, $body, [], [], $header);
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
     * @return Mockery\MockInterface $mock
     */
    private function getMock(bool $savingSuccess = true, bool $reading = false)
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
     * @return Mockery\MockInterface $mock
     */
    private function getMockRepo(bool $reading = false, $allSuccess = null, $findSuccess = null)
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
        $this->app->instance('App\Repositories\StatementRepository', $mock);
        return $mock;
    }

    /**
     * @test
     * @return void
     */
    public function statementErrorAuthenticationTest()
    {
        $helper   = $this->help();
        $header = HelperTest::createBasicHeader();
        $header['PHP_AUTH_USER'] = 'test';
        $response = $this->call('POST', HelperTest::URL, $helper->getStatement(), [], [], $header);
        $this->assertEquals(401, $response->status());
    }

    /**
     * Get basic request with auth params
     *
     * @return Illuminate\Http\Request
     */
    private function getRequest() 
    {
        $request = new Request();
        $request->headers->set(HelperTest::BASIC_HEADER_AUTH_NAME, HelperTest::BASIC_HEADER_AUTH_TYPE . ' ' . base64_encode(env('CLIENT_ID') . ":" . env('CLIENT_SECRET')));
        $request->headers->set('PHP_AUTH_USER', env('CLIENT_ID'));
        $request->headers->set('PHP_AUTH_PW', env('CLIENT_SECRET'));
        return $request;
    }

     /**
     * @test
     * @return void
     */
    public function statementPostSuccessTest()
    {
        $statementRequest = $this->getRequest();    
        $statementController = new StatementController($this->getMock(), $this->getMockRepo());
        $statementRequest->replace($this->help()->getStatement());

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
        $statementController = new StatementController($this->getMock(), $this->getMockRepo());
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
        $statementRequest = $this->getRequest();
        $statement = array(0 => $this->help()->getStatement());
        $statementController = new StatementController($this->getMock(true, true), $this->getMockRepo(true, $statement));
        
        $response = $statementController->getList($statementRequest);
        $this->assertEquals(200, $response->status());
    }

     /**
     * @test
     * @return void
     */
    public function statementReadSuccessTest()
    {
        $id = (string) Uuid::uuid1();
        $statementRequest = $this->getRequest();        
        $statementController = new StatementController($this->getMock(true, true),  $this->getMockRepo(true, null, $this->help()->getStatementWithUuid($id)));        

        $response = $statementController->get($statementRequest, $id);
        $this->assertEquals(200, $response->status());
    }

     /**
     * @test
     * @return void
     */
    public function statementReadAllEmptyTest()
    {      
        $statementRequest = $this->getRequest();     
        $statementController = new StatementController($this->getMock(true, true), $this->getMockRepo(true, null));

        $response = $statementController->getList($statementRequest);
        $this->assertEquals(204, $response->status());
    }

    /**
     * @test
     * @return void
     */
    public function statementReadEmptyTest()
    {      
        $statementRequest = $this->getRequest();
        $statementController = new StatementController($this->getMock(true, true),  $this->getMockRepo(true, null, null));
        $id = (string) Uuid::uuid1();

        $response = $statementController->get($statementRequest, $id);
        $this->assertEquals(204, $response->status());
    }

     /**
     * @test
     * @return void
     */
    public function baseStatementPostSuccessTest()
    {
        $statementRequest = new Request();        
        $statementController = new StatementController($this->getMock(), $this->getMockRepo());
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
        $statementController = new StatementController($this->getMock(), $this->getMockRepo());
        $helper = $this->help();
        $statementRequest->replace([$helper->getStatement(), $helper->getStatement()]);
        
        $response = $statementController->store($statementRequest);
        $this->assertEquals(200, $response->status());
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
        $statementRequest = new Request();        
        $statementController = new StatementController($this->getMock(), $this->getMockRepo());

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
        $statementRequest = new Request();        
        $statementController = new StatementController($this->getMock(false), $this->getMockRepo());

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