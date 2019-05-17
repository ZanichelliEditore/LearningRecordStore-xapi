<?php

namespace App\Models;


class Actor
{
    /**
    * Get valid fields
    * @return array
    */
    static public function getAdmittedFields()
    {
        return [
            "name"       => "simple",
            "objectType" => "simple",
            "account"    => "complex",
            "openid"     => "simple",
            "mbox"       => "simple",
            "mbox_sha1sum" => "simple",
            "member"     => "simple"
        ];
    }
}