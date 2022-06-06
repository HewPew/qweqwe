<?php

use Illuminate\Http\Request;

use App\Http\Middleware\{
    VerifyApiToken
};

use App\User;

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

Route::middleware('auth:api')->group(function () {
    Route::post('/anketa', 'AnketsController@ApiAddForm');
    Route::put('/anketa/{id}', 'AnketsController@Update');
    Route::get('/anketa/{id}', 'AnketsController@GetApiForm');

    Route::get('/fields/{model}', 'ApiController@GetFields');

    Route::put('/edit-listks/{anketa_id}/{product_id}/{types}', 'IndexController@ApiEditListKs')->name('api.ApiEditListKs');
    Route::post('/elements/{type}', 'IndexController@ApiAddElement')->name('api.addElement');
    Route::get('/recommend-ks/{count}/{product_id}', 'ListKsController@getRecommendKs')->name('getRecommendKs');

    Route::get('/check-prop/{prop}/{model}/{val}', 'ApiController@CheckProperty');
});
