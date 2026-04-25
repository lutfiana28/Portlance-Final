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
use App\Http\Controllers\Api\PortfolioFileController;
use App\Http\Controllers\Api\UserPaymentController;
use App\Http\Controllers\Api\AdminPaymentController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\AdminOrderProcessController;
use App\Http\Controllers\Api\UserReviewController;
use App\Http\Controllers\Api\AdminRevisionController;
use App\Http\Controllers\Api\AdminFinalResultController;
use App\Http\Controllers\Api\UserAccountController;
use App\Http\Controllers\Api\AdminAccountController;

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
    Route::get('/profile', [UserAccountController::class, 'profile']);
    Route::post('/logout', [UserAccountController::class, 'logout']);

    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/files', [PortfolioFileController::class, 'store']);
    Route::post('/orders/{id}/payment', [UserPaymentController::class, 'store']);
    Route::post('/orders/{id}/revisions', [UserReviewController::class, 'submitRevision']);
    Route::post('/orders/{id}/approve', [UserReviewController::class, 'approve']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('/templates', [TemplateController::class, 'store']);
    Route::put('/templates/{id}', [TemplateController::class, 'update']);
    Route::delete('/templates/{id}', [TemplateController::class, 'destroy']);

    Route::post('/packages', [PackageController::class, 'store']);
    Route::put('/packages/{id}', [PackageController::class, 'update']);
    Route::delete('/packages/{id}', [PackageController::class, 'destroy']);

    Route::get('/payments', [AdminPaymentController::class, 'index']);
    Route::post('/payments/{id}/verify', [AdminPaymentController::class, 'verify']);

    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [AdminOrderController::class, 'show']);

    Route::put('/orders/{id}/status', [AdminOrderProcessController::class, 'updateStatus']);
    Route::post('/orders/{id}/preview', [AdminOrderProcessController::class, 'storePreview']);
    Route::put('/orders/{id}/preview', [AdminOrderProcessController::class, 'updatePreview']);

    Route::get('/revisions', [AdminRevisionController::class, 'index']);
    Route::put('/revisions/{id}', [AdminRevisionController::class, 'update']);

    Route::post('/orders/{id}/final-result', [AdminFinalResultController::class, 'store']);
});Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/profile', [AdminAccountController::class, 'profile']);
    Route::post('/logout', [AdminAccountController::class, 'logout']);

    Route::post('/templates', [TemplateController::class, 'store']);
    Route::put('/templates/{id}', [TemplateController::class, 'update']);
    Route::delete('/templates/{id}', [TemplateController::class, 'destroy']);

    Route::post('/packages', [PackageController::class, 'store']);
    Route::put('/packages/{id}', [PackageController::class, 'update']);
    Route::delete('/packages/{id}', [PackageController::class, 'destroy']);

    Route::get('/payments', [AdminPaymentController::class, 'index']);
    Route::post('/payments/{id}/verify', [AdminPaymentController::class, 'verify']);

    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [AdminOrderController::class, 'show']);

    Route::put('/orders/{id}/status', [AdminOrderProcessController::class, 'updateStatus']);
    Route::post('/orders/{id}/preview', [AdminOrderProcessController::class, 'storePreview']);
    Route::put('/orders/{id}/preview', [AdminOrderProcessController::class, 'updatePreview']);

    Route::get('/revisions', [AdminRevisionController::class, 'index']);
    Route::put('/revisions/{id}', [AdminRevisionController::class, 'update']);

    Route::post('/orders/{id}/final-result', [AdminFinalResultController::class, 'store']);
});