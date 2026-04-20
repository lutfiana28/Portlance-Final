<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Order;
use App\Models\Template;
use App\Models\Package;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\OrderController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'API backend portfolio ordering berjalan',
        'status' => true
    ]);
});

Route::get('/check-models', function () {
    return response()->json([
        'users' => User::count(),
        'orders' => Order::count(),
        'templates' => Template::count(),
        'packages' => Package::count(),
    ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/admin/login', [AdminAuthController::class, 'login']);

Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/templates/{id}', [TemplateController::class, 'show']);
Route::get('/packages', [PackageController::class, 'index']);
Route::get('/packages/{id}', [PackageController::class, 'show']);

Route::middleware(['auth:sanctum', 'user.role'])->prefix('user')->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('/templates', [TemplateController::class, 'store']);
    Route::put('/templates/{id}', [TemplateController::class, 'update']);
    Route::delete('/templates/{id}', [TemplateController::class, 'destroy']);

    Route::post('/packages', [PackageController::class, 'store']);
    Route::put('/packages/{id}', [PackageController::class, 'update']);
    Route::delete('/packages/{id}', [PackageController::class, 'destroy']);
});