<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = "clients";

    public function lrs()
    {
        return $this->belongsTo('App\Models\Lrs');
    }
}