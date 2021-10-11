<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Dashboard\VoteController;
use App\Http\Controllers\Dashboard\UserVoteController;
use App\Http\Controllers\Dashboard\LinkController;

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
Route::post('register', [RegisterController::class, 'create']);
Route::post('login', [LoginController::class, 'check']);

// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/me', [LoginController::class, 'show']);
    Route::post('/logout', [LoginController::class, 'destroy']);

    Route::prefix('votes')->group(function () {

        Route::get('/', [VoteController::class, 'index']);
        Route::post('/', [VoteController::class, 'store']);
        Route::post('/delete', [VoteController::class, 'bulkDestroy']);

        Route::get('/{id}', [VoteController::class, 'show']);
        Route::post('/{id}/update', [VoteController::class, 'edit']);
        Route::post('/{id}/delete', [VoteController::class, 'destroy']);
        Route::post('/{id}/delete_user_votes', [UserVoteController::class, 'destroy']);

    });

    Route::prefix('links')->group(function () {

        Route::get('/', [LinkController::class, 'index']);
        Route::post('/', [LinkController::class, 'store']);
        Route::post('/delete', [LinkController::class, 'bulkDestroy']);

        Route::get('/{id}', [LinkController::class, 'show']);
        Route::post('/{id}/update', [LinkController::class, 'edit']);
        Route::post('/{id}/delete', [LinkController::class, 'destroy']);

    });

    Route::prefix('{key}')->group(function () {
        Route::get('/', [UserVoteController::class, 'show']);
        Route::post('/', [UserVoteController::class, 'store']);
    });
});

