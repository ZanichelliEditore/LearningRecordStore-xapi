<?php


use App\Locker\HelperTest;
use Illuminate\Http\Request;
use \StatementBaseCase as StatementBase;


class StatementTest extends StatementBase
{
    /**
     * Concrete method that create authentication
     * @return array
    */
    public function authentication(){
        return HelperTest::createBasicHeader();
    }

     /**
     * Get basic request with auth params
     *
     * @return Illuminate\Http\Request
     */
    public function getRequest() 
    {
        $request = new Request();
        $request->headers->set(HelperTest::BASIC_HEADER_AUTH_NAME, HelperTest::BASIC_HEADER_AUTH_TYPE . ' ' . base64_encode(env('CLIENT_ID') . ":" . env('CLIENT_SECRET')));
        $request->headers->set('PHP_AUTH_USER', env('CLIENT_ID'));
        $request->headers->set('PHP_AUTH_PW', env('CLIENT_SECRET'));
        return $request;
    }
    
}