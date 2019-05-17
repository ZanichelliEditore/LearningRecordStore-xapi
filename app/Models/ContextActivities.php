<?php
    
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ContextActivities extends Model
{
    /**
    * Get first valid fields
    * @return array
    */
    static public function getAdmittedFields()
    {
        return [
            "parent"   => "simple",
            "grouping" => "simple",
            "category" => "simple",
            "other"    => "simple"
        ];
    }
}