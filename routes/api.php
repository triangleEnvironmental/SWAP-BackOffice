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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return get_user();
});

Route::group(['prefix' => 'citizen/v1'], function () {
    require('api/citizen/v1.php');
});

Route::group(['prefix' => 'moderator/v1'], function () {
    require('api/moderator/v1.php');
});
