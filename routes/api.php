<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware('throttle:api')->group(function (): void {
    // Public auth & reference endpoints
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth')->name('register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth')->name('login');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

    // Protected resident & authenticated endpoints
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update.post');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/my-reports', [ReportController::class, 'index'])->name('reports.mine');
        Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:uploads')->name('reports.store');
        Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');
    });
});
