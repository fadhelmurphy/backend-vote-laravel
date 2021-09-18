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

    });

    // Route::prefix('{id}')->group(function () {
    //     Route::post('/index/priv8', [VoteController::class, 'showUrl']);
    //     Route::post('/sendvote', [UserVoteController::class, 'store']);
    //     Route::post('/deletevoter', [UserVoteController::class, 'destroy']);
    // });


    // Route::post('/generate/private', [LinkController::class, 'create']);
    // Route::get('/getlink/{id}', [LinkController::class, 'index']);
    // Route::get('/deletelink/{id}', [LinkController::class, 'destroy']);
    // Route::post('/updatelink', [LinkController::class, 'edit']);
    // Route::post('/bulkdeletelinks', [LinkController::class, 'bulkDestroy']);
    // Route::get('/links', [LinkController::class, 'show']);
});
