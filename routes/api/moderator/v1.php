<?php

use App\Http\Controllers\API\Moderator\V1\AssignmentController;
use App\Http\Controllers\API\Moderator\V1\AuthController;
use App\Http\Controllers\API\Moderator\V1\CommentController;
use App\Http\Controllers\API\Moderator\V1\ModerationController;
use App\Http\Controllers\API\Moderator\V1\NotificationController;
use App\Http\Controllers\API\Moderator\V1\PageController;
use App\Http\Controllers\API\Moderator\V1\ProfileController;
use App\Http\Controllers\API\Moderator\V1\ReportController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgot_password']);
    Route::post('change-password', [AuthController::class, 'change_password']);
});

Route::group(['prefix' => 'page'], function () {
    Route::get('home', [PageController::class, 'home_data'])->middleware('auth:sanctum');
    Route::get('about', [PageController::class, 'about']);
    Route::get('terms', [PageController::class, 'terms']);
    Route::get('policy', [PageController::class, 'policy']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'profile'], function () {
        Route::get('', [ProfileController::class, 'get_profile']);
        Route::post('', [ProfileController::class, 'update_info']);
        Route::post('photo', [ProfileController::class, 'update_photo']);
    });

    Route::group(['prefix' => 'reports'], function () {
        Route::get('', [ReportController::class, 'list']);
        Route::get('map', [ReportController::class, 'map_query']);
        Route::get('filter-options', [ReportController::class, 'filter_options']);
//        Route::get('history', [ReportController::class, 'list_history'])
//            ->middleware('auth:sanctum');
        Route::get('{id}', [ReportController::class, 'detail']);
        Route::delete('{id}', [ReportController::class, 'delete']);
        Route::get('{id}/comments', [ReportController::class, 'get_comments']);
        Route::post('{id}/moderate', [ReportController::class, 'moderate']);
    });

    Route::group(['prefix' => 'comments'], function () {
        Route::post('', [CommentController::class, 'create']);
        Route::delete('{id}', [CommentController::class, 'delete']);
    });

    Route::group(['prefix' => 'notification'], function () {
        Route::post('save-token', [NotificationController::class, 'save_token']);
        Route::get('preset-options', [NotificationController::class, 'list_preset']);
    });

    Route::group(['prefix' => 'assignment'], function () {
        Route::post('', [AssignmentController::class, 'assign']);
        Route::get('options', [AssignmentController::class, 'list_options']);
        Route::get('reports', [AssignmentController::class, 'assigned_reports']);
    });

    Route::group(['prefix' => 'moderation'], function () {
        Route::get('history', [ModerationController::class, 'history']);
    });
});
