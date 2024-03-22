<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
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
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });
    Route::middleware(['auth:sanctum'])->group(function (){
        Route::resource('posts', PostController::class);
        Route::prefix('users')->group( function () {
            Route::get('',[UserController::class, 'getUser']);
            Route::get('{username}', [UserController::class, 'getDetailUser']);
            Route::post('{username}/follow', [FollowController::class, 'follUser']);
            Route::delete('{username}/unfollow', [FollowController::class, 'unfollowUser']);

            Route::get('{username}/following', [FollowController::class, 'followingUser']);
            Route::get('{username}/followers', [FollowController::class, 'followersUser']);
            Route::put('{username}/accept', [FollowController::class, 'accept']);
        });
    });
});