<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Register;
use App\Http\Controllers\Dashboard\VoteCRUD;
use App\Http\Controllers\Dashboard\LinkManager;

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
Route::post('register', [Register::class, 'create']);
Route::post('login', [Login::class, 'check']);

// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/getuser', [Login::class, 'show']);
    Route::post('/logout', [Login::class, 'destroy']);

    Route::post('add', [VoteCRUD::class, 'create']);
    Route::get('/AllVote', [VoteCRUD::class, 'show']);
    Route::get('/get/{id}', [VoteCRUD::class, 'index']);
    Route::post('/show/priv8', [VoteCRUD::class, 'indexUrl']);
    Route::post('/update', [VoteCRUD::class, 'update']);
    Route::get('/delete/{id}', [VoteCRUD::class, 'destroy']);
    Route::post('/bulkdelete', [VoteCRUD::class, 'bulkDestroy']);
    Route::post('/sendvote', [VoteCRUD::class, 'store']);
    Route::post('/deletevoter', [VoteCRUD::class, 'destroyVoter']);

    Route::post('/generate/private', [LinkManager::class, 'create']);
    Route::get('/getlink/{id}', [LinkManager::class, 'show']);
    Route::get('/deletelink/{id}', [LinkManager::class, 'destroy']);
    Route::post('/updatelink', [LinkManager::class, 'update']);
    Route::post('/bulkdeletelinks', [LinkManager::class, 'bulkDestroy']);
    Route::get('/links', [LinkManager::class, 'index']);
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
