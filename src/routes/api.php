<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\VideoController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\OptionalAuthMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| DevFinder API Routes - v1
|
*/

// Apply optional auth middleware to all routes
Route::middleware([OptionalAuthMiddleware::class])->group(function () {
    // App Info
    Route::get('/', function () {
        return response()->json(['appname' => 'DevFinder']);
    });

    // Dev Routes
    Route::get('/devs', [DevController::class, 'index']);
    Route::get('/devs/{username}', [DevController::class, 'show']);
    Route::get('/devs/{username}/likes', [DevController::class, 'likes']);
    Route::get('/me', [DevController::class, 'me'])->middleware(AuthMiddleware::class);

    // Dev Auth Routes (require authentication)
    Route::post('/devs', [DevController::class, 'store'])->middleware(AuthMiddleware::class);
    Route::post('/devs/{username}/like', [DevController::class, 'like'])->middleware(AuthMiddleware::class);
    Route::delete('/devs/{username}/like', [DevController::class, 'unlike'])->middleware(AuthMiddleware::class);
    Route::post('/devs/{username}/dislike', [DevController::class, 'dislike'])->middleware(AuthMiddleware::class);
    Route::delete('/devs/{username}/dislike', [DevController::class, 'undislike'])->middleware(AuthMiddleware::class);

    // Channel Routes
    Route::get('/channels', [ChannelController::class, 'index']);
    Route::get('/channels/{nameOrLink}', [ChannelController::class, 'show']);
    Route::post('/channels', [ChannelController::class, 'store'])->middleware(AuthMiddleware::class);
    Route::post('/channels/{channelId}/like', [ChannelController::class, 'like'])->middleware(AuthMiddleware::class);
    Route::delete('/channels/{channelId}/like', [ChannelController::class, 'unlike'])->middleware(AuthMiddleware::class);
    Route::post('/channels/{channelId}/follow', [ChannelController::class, 'follow'])->middleware(AuthMiddleware::class);
    Route::post('/channels/refresh', [ChannelController::class, 'refresh'])->middleware(AuthMiddleware::class);

    // Video Routes
    Route::get('/videos', [VideoController::class, 'index']);
    Route::get('/video/{videoId}', [VideoController::class, 'show']);
    Route::get('/trending', [VideoController::class, 'trending']);
    Route::get('/subscriptions', [VideoController::class, 'subscriptions'])->middleware(AuthMiddleware::class);
    Route::post('/video', [VideoController::class, 'store'])->middleware(AuthMiddleware::class);
    Route::post('/video/{videoId}/refresh', [VideoController::class, 'refresh'])->middleware(AuthMiddleware::class);
});
