<?php

namespace App\Models;



class Verb
{
    /**
    * Get valid fields
    * @return array
    */
    static public function getAdmittedFields()
    {
        return [
            "id" => "simple",
            "display" => "simple"
        ];
    }
}