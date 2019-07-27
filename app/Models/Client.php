<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = "clients";

     /**
     * Get the lrs record associated with the clients.
     */
    public function lrs()
    {
        return $this->belongsTo('App\Models\Lrs', 'lrs_id',  '_id');
    }

    /**
     * Get the original client infoassociated in table clients.
     */
    public function oauthClient()
    {
        return $this->hasOne('App\Models\OauthClient', 'id', 'api_basic_key');
    }
    
    protected static function boot()
    {
        parent::boot();

        static::deleting(function($model)
        {
            $model->oauthClient->delete();
        });
    }

    /**
     * Retrieve client's scopes
     *
     * @param string $username
     * @return array
    */
    public function retrieveClientScopes(string $username) {
        $client = Client::where('api_basic_key',$username)->first();
        if (isset($client)) {
            $array_scopes = json_decode($client->scopes);
            return array_map('trim', $array_scopes);
        }

        return [];
    }
}