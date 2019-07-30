<?php

use App\Locker\HelperTest;
use \StorageBaseCase as StorageBase;


class StorageStatementTest extends StorageBase
{
    /**
     * Concrete method that create authentication
     * @return array
    */
    public function authentication(){
        return HelperTest::createBasicHeader();
    }

}