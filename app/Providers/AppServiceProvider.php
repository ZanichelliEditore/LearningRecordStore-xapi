<?php

namespace App\Providers;

use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use \Dusterio\LumenPassport\LumenPassport;

/**
 * @codeCoverageIgnore
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Passport::ignoreMigrations();

        /*MAP SERVICES*/
        $this->app->bind('App\Services\StatementServiceInterface',   'App\Services\StatementStorageService.php');
        $this->app->bind('App\Services\ClientServiceInterface',   'App\Services\ClientService');

        /** MAP REPOSITORIES */
        $this->app->bind('App\Http\Repositories\xapiRepositories\StatementRepositoryInterface', 'App\Http\Repositories\xapiRepositories\StatementRepository');
        $this->app->bind('App\Http\Repositories\RepositoryInterface', 'App\Http\Repositories\LrsRepository');
    }

    public function boot()
    {
        LumenPassport::routes($this->app->router);
        Client::creating(function (Client $client) {
            $client->incrementing = false;
            $client->id = sha1(uniqid(mt_rand(), true));
        });
        Client::retrieved(function (Client $client) {
            $client->incrementing = false;
        });
    }
}
