<?php

namespace App\Models;



class Result
{
    /**
    * Get valid fields
    * @return array
    */
    static public function getAdmittedFields()
    {
        return [
            "completion" => "simple",
            "success"    => "simple",
            "response"   => "simple",
            "score"      => "complex",
            "duration"   => "simple",
            "extensions" => "simple"
        ];
    }
}