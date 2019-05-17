<?php

namespace App\Models;


class Account
{
    /**
    * Get valid fields
    * @return array
    */
    static public function getAdmittedFields()
    {
        return [
            "name" => "simple",
            "homePage" => "simple",
        ];
    }
}