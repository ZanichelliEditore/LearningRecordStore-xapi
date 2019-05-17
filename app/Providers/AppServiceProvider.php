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

        /** MAP REPOSITORIES */
        $this->app->bind('App\Repositories\StatementRepositoryInterface', 'App\Repositories\StatementRepository');
    }

    public function boot()
    {
        LumenPassport::routes($this->app);
        Client::creating(function (Client $client) {
            $client->incrementing = false;
            $client->id = sha1(uniqid(mt_rand(), true));
        });
        Client::retrieved(function (Client $client) {
            $client->incrementing = false;
        });
    }
}
