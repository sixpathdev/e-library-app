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
*/




$router->group(['prefix' => 'api/v1'], function () use ($router) {
    $router->get('/', function () use ($router) {
        return $router->app->version();
    });

    $router->get('/forgotpassword-template', 'AuthController@template');

    $router->post('/login', 'AuthController@login');
    $router->post('/register', 'AuthController@register');
    $router->post('/forgotpassword', 'AuthController@forgotpassword');
    $router->post('/reset-password', 'AuthController@resetpassword');


    $router->group(['middleware' => 'auth'], function () use ($router) {

        //User
        $router->get('/profile', 'UserController@profile');

        //Books
        $router->get('/books', 'BookController@allBooks');
        $router->get('/user/{user_id}/books', 'BookController@userBooks');
        $router->get('/books/{id}', 'BookController@userBooks');
        $router->get('/book/{id}', 'BookController@showbook');
        $router->post('/books', 'BookController@uploadbook');
        $router->delete('/book/{id}', 'BookController@deletebook');
    });
});
