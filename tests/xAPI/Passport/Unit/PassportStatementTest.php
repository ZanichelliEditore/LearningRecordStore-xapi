<?php


use App\Constants\Scope;
use App\Locker\HelperTest;
use Illuminate\Http\Request;
use \StatementBaseCase as StatementBase;
use App\Http\Controllers\StatementController;

class PassportStatementTest extends StatementBase
{
    /**
     * Request oauth token
     * @return  array
    */
    public function authentication(array $scopes = [])
    {
        $helper = $this->help();
        $scopes = !empty($scopes) ? $scopes : [Scope::STATEMENTS_READ, Scope::STATEMENTS_WRITE];
        $bodyParams = [
            "client_id" => env('CLIENT_ID'),
            "client_secret" => env('CLIENT_SECRET'),
            "grant_type" => HelperTest::GRANT_TYPE,
            "scope" => $scopes
        ];

        $content = $this->call('POST', env("SWAGGER_LUME_CONST_HOST"), $bodyParams)->getContent();
        $body = json_decode($content);

        try {
            $access_token = $body->access_token;
        } catch (Exception $e) {
            Log::error('Test error: '. (string)$e);
            $access_token = '';
        }
        return $helper->createHeader($access_token);
    }
    
     /**
     * Get basic request with auth params
     *
     * @return Illuminate\Http\Request
     */
    public function getRequest() 
    {
        return new Request();
    }

    /**
     * @test
     * @return void
     */
    public function statementScopesAllTest()
    {
        $this->authentication([Scope::ALL]);
        parent::statementScopesAllTest();

    }

    /**
     * @test
     * @return void
     */
    public function baseStatementPostSuccessTest()
    {
        $statementRequest = $this->getRequest();        
        $statementController = new StatementController($this->getMock(), $this->getMockRepo(), $this->getMockLocker());

        $statement = $this->help()->getStatement();
        unset($statement['context']);
        unset($statement['result']);
        unset($statement['version']);
        unset($statement['timestamp']);
        unset($statement['authority']);
        unset($statement['actor']['name']);
        unset($statement['verb']['display']);
        unset($statement['object']['definition']);
        $statementRequest->replace($statement);

        $statementRequest->headers->set('Authorization', (string)$this->authentication()['HTTP_Authorization']);
        $response = $statementController->store($statementRequest);
        $this->assertEquals(200, $response->status());
    }

    /**
     * @test
     *
     * @return void
     */
    public function statementsPostSubstatementSuccessTest()
    {   
        $statementRequest = $this->getRequest();      
        $statementController = new StatementController($this->getMock(), $this->getMockRepo(), $this->getMockLocker());

        $statementRequest->replace($this->help()->getStatementWithSubstatement());
        $statementRequest->headers->set('Authorization', (string)$this->authentication()['HTTP_Authorization']);
        $response = $statementController->store($statementRequest);
        $this->assertEquals(200, $response->status());
    }

    /**
     * @test
     *
     * @return void
     */
    public function statementsFailedStoringTest()
    {   
        $statementRequest = $this->getRequest();        
        $statementController = new StatementController($this->getMock(false), $this->getMockRepo(), $this->getMockLocker());
        $statementRequest->replace($this->help()->getStatementWithSubstatement());
        $statementRequest->headers->set('Authorization', (string)$this->authentication()['HTTP_Authorization']);

        $response = $statementController->store($statementRequest);
        $this->assertEquals(500, $response->status());
    }

}