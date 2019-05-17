<?php

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

$router->get('/', function () {
    return redirect('/api/documentation');
});

$router->post('/data/xAPI/statements', ['middleware' => 'auth.basic', 'uses' => 'StatementController@store']);
$router->get('/data/xAPI/statements', ['middleware' => 'auth.basic', 'uses' => 'StatementController@getList']);
$router->get('/data/xAPI/statements/{id}', ['middleware' => 'auth.basic', 'uses' => 'StatementController@get']);
