<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', 'AuthController@logout');
    });
});

Route::prefix('epresence')->group(function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('epresence', 'EpresenceController@epresence');
        Route::get('history', 'EpresenceController@history');
        Route::get('approve/{id}', 'EpresenceController@approve');  //id = id table epresence
    });
});
