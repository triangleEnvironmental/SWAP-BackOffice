<?php

use App\Http\Controllers\API\Citizen\V1\AuthController;
use App\Http\Controllers\API\Citizen\V1\CommentController;
use App\Http\Controllers\API\Citizen\V1\FaqController;
use App\Http\Controllers\API\Citizen\V1\NotificationController;
use App\Http\Controllers\API\Citizen\V1\PageController;
use App\Http\Controllers\API\Citizen\V1\ProfileController;
use App\Http\Controllers\API\Citizen\V1\ReportController;
use App\Http\Controllers\API\Citizen\V1\SectorController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('firebase-login', [AuthController::class, 'firebase_login']);
    Route::post('firebase-demo-login', [AuthController::class, 'firebase_demo_login']);
    Route::post('register', [AuthController::class, 'register']);
});

Route::group(['prefix' => 'sectors'], function () {
    Route::get('available', [SectorController::class, 'get_available']);
});

Route::group(['prefix' => 'page'], function () {
    Route::get('home', [PageController::class, 'home_data']);
    Route::get('about', [PageController::class, 'about']);
    Route::get('terms', [PageController::class, 'terms']);
    Route::get('policy', [PageController::class, 'policy']);
});

Route::group(['prefix' => 'reports'], function () {
    Route::get('', [ReportController::class, 'list']);
    Route::post('', [ReportController::class, 'create']);
    Route::get('map', [ReportController::class, 'map_query']);
    Route::get('filter-options', [ReportController::class, 'filter_options']);
    Route::get('history', [ReportController::class, 'list_history'])
        ->middleware('auth:sanctum');
    Route::get('{id}', [ReportController::class, 'detail']);
    Route::get('{id}/comments', [ReportController::class, 'get_comments']);
});

Route::group(['prefix' => 'faqs'], function () {
    Route::get('', [FaqController::class, 'list']);
    Route::get('{id}', [FaqController::class, 'detail']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::group(['prefix' => 'comments'], function () {
        Route::post('', [CommentController::class, 'create']);
        Route::delete('{id}', [CommentController::class, 'delete']);
    });

    Route::group(['prefix' => 'profile'], function () {
        Route::get('', [ProfileController::class, 'get_profile']);
        Route::post('', [ProfileController::class, 'update_name']);
        Route::post('photo', [ProfileController::class, 'update_photo']);
        Route::post('address', [ProfileController::class, 'update_address']);
    });

    Route::group(['prefix' => 'notification'], function () {
        Route::get('', [NotificationController::class, 'list']);
        Route::get('count-unread', [NotificationController::class, 'count_unread']);
        Route::post('save-token', [NotificationController::class, 'save_token']);
        Route::put('read', [NotificationController::class, 'read_all']);
        Route::get('{id}', [NotificationController::class, 'detail']);
        Route::delete('{id}', [NotificationController::class, 'delete']);
        Route::put('{id}/read', [NotificationController::class, 'read']);
    });
});
