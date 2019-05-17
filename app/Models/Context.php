<?php

namespace App\Models;



class Context
{
    /**
    * Get valid fields
    * @return array
    */
    static public function getAdmittedFields()
    {
        return [
            "platform" => "simple",
            "language" => "simple",
            "revision" => "simple",
            "registration" => "simple",
            "contextActivities" => "complex",
            "statement" => "complex",
            "instructor" => "simple",
            "team"       => "simple",
            "extensions" => "simple"
        ];
    }
}