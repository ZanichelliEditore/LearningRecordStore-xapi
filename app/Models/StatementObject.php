<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class StatementObject extends Model
{
    /**
    * Get valid fields
    * @return array
    */
    static public function getAdmittedFields()
    {
        return [
            "id"         => "simple",
            "objectType" => "simple",
            "member"     => "simple",
            "definition" => "simple",
            "type"       => "simple",
            "name"       => "simple",
            "description" => "simple",
            "moreInfo"   => "simple",
            "extensions" => "simple",
            //Interaction Activities fields
            "interactionType" => "simple",
            "correctResponsesPattern" => "simple",
            "choices"   => "simple",
            "scale"     => "simple",
            "source"    => "simple",
            "target"    => "simple",
            "steps"     => "simple",
            //When the object is a sub-statement
            "timestamp" => "simple",
            "actor"     => "complex",
            "verb"      => "complex",
            "object"    => "complex",
            "context"   => "complex",
            "result"    => "complex"
        ];
    }
}