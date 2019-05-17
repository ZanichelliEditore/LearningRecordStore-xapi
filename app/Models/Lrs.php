<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lrs extends Model
{
    protected $table = "lrs";

    public function clients()
    {
        return $this->hasMany('App\Models\Client');
    }
}