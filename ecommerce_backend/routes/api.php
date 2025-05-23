<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SubCategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-reset-password-email', [AuthController::class, 'sendPasswordResetLink']);
Route::post('/reset-password/{token}', [AuthController::class, 'resetPassword']);

// New routes for email verification
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/user', [AuthController::class, 'user']);

    // categories
    Route::group(['prefix' => 'category'], function () {
        Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/', [CategoryController::class, 'store'])->name('categories.store');
        Route::delete('/multiple', [CategoryController::class, 'deleteMultiple'])->name('categories.deleteMultiple');
        Route::get('/active', [CategoryController::class, 'activeCategories']);
        Route::get('/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // sub categories
    Route::get('/sub_category/active', [SubCategoryController::class, 'activeSubCategories']);
    Route::apiResource('sub_category', SubCategoryController::class);

    // brands
    Route::get('/brands/active', [BrandController::class, 'activeBrands']);
    Route::delete('/brands/multiple', [BrandController::class, 'deleteMultiple'])->name('brands.deleteMultiple');
    Route::apiResource('brands',BrandController::class);

    // colors
    Route::get('/colors/active', [ColorController::class, 'activeColors']);
    Route::apiResource('colors',ColorController::class);

    // products
    Route::apiResource('products', ProductController::class);
});
