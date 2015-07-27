<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::get('/all/{length?}', 'HomeController@all')->where('length', '[0-9]+');;
Route::get('/{length?}', 'HomeController@index')->where('length', '[0-9]+');;