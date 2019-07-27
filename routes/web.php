<?php

use App\Constants\Scope;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
| Choose client_credentials middleware if you want to use passport authentication with client credential
*/

/*
 * Documentation
*/
$router->get('/', function () {
    return redirect('api/documentation');
});

/*
 * xAPI Routes
 * Basic Authentication
*/
$router->group(['prefix' => 'data/xAPI', 'middleware' => 'auth.basic'], function () use ($router) {

    $router->post('statements', [
        'middleware' => 'check_scopes:' . Scope::STATEMENTS_WRITE,
        'uses' => 'StatementController@store'
    ]);
    $router->get('statements', [
        'middleware' => 'check_scopes:' . Scope::STATEMENTS_READ,
        'uses' => 'StatementController@getList'
    ]);
    $router->get('statements/{id}', [
        'middleware' => 'check_scopes:' . Scope::STATEMENTS_READ,
        'uses' => 'StatementController@get'
    ]);

});

/*
 * Lrs Routes
 * Client Credential authentication
*/
$router->group(['middleware' => 'client_credentials'], function () use ($router) {

    $router->get('lrs', [
        'middleware' => 'check_scopes:' . Scope::LRS_READ,
        'uses' => 'LrsController@getList'
    ]);

    $router->get('lrs/{id}/statements', [
        'middleware' => ['check_scopes:' . Scope::LRS_READ, 'check_scopes:' . Scope::STATEMENTS_READ],
        'uses' => 'LrsController@getStatements'
    ]);

    $router->post('lrs', [
        'middleware' => 'check_scopes:' . Scope::LRS_WRITE,
        'uses' => 'LrsController@store'
    ]);

    $router->delete('lrs/{id}', [
        'middleware' => 'check_scopes:' . Scope::ALL,
        'uses' => 'LrsController@destroy'
    ]);


});