<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lrs extends Model
{
    protected $table = "lrs";

    /**
     * Get the clients records associated with lrs.
    */
    public function clients()
    {
        return $this->hasMany('App\Models\Client', 'lrs_id', '_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($model)
        {
            foreach ($model->clients as $client) {
                $client->delete();
            }
        });
    }
}