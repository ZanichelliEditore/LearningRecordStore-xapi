<?php

namespace App\Models;


class ContextStatement
{
    /**
    * Get valid fields
    * @return array
    */
    static public function getAdmittedFields()
    {
        return [
            "id" => "simple",
            "objectType" => "simple",
        ];
    }
}