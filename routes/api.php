<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DealController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public routes
Route::middleware('throttle:60,1')->group(function () {
    // Deal listings
    Route::get('/deals', [DealController::class, 'index']);
    Route::get('/deals/{deal}', [DealController::class, 'show']);
});

// Protected routes
Route::middleware(['auth:sanctum', 'throttle:4,1'])->group(function () {
    // User info and authentication
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Bookmarks
    Route::post('/deals/{deal}/bookmark', [DealController::class, 'bookmark']);
    Route::get('/user/bookmarks', [UserController::class, 'bookmarks']);
});
