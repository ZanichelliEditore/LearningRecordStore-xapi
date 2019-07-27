<?php

use App\Locker\HelperTest;
use \RepositoryBaseCase as RepositoryStatementBase;

class RepositoryStoredStatementTest extends RepositoryStatementBase
{
    /**
     * Concrete method that create authentication
     * @return array
    */
    public function authentication(){
        return HelperTest::createBasicHeader();
    }

}