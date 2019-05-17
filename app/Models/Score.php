<?php

namespace App\Models;



class Score
{
    /**
    * Get valid fields
    * @return array
    */
    static public function getAdmittedFields()
    {
        return [
            "max" => "simple",
            "min" => "simple",
            "scaled" => "simple",
            "raw" => "simple"
        ];
    }

}