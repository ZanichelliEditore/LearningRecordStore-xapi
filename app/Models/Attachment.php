<?php

namespace App\Models;


class Attachment
{
    /**
    * Get valid fields
    * @return array
    */
    static public function getAdmittedFields()
    {
        return [
            "usageType" => "simple",
            "display" => "simple",
            "description" => "simple",
            "contentType" => "simple",
            "length" => "simple",
            "sha2" => "simple",
            "fileUrl" => "simple"
        ];
    }

}