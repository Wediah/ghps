<?php

use App\Http\Controllers\KnowledgeBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ChatbotController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Farms
    Route::get('/farms', [FarmController::class, 'index']);
    Route::post('/farms', [FarmController::class, 'store']);
    Route::get('/farms/nearby', [FarmController::class, 'nearby']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/store-products', [ProductController::class, 'store']);

    // Orders
    Route::post('/store-orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);

    // Knowledge Base
    // Knowledge Base Routes
    Route::get('/knowledge', [KnowledgeBaseController::class, 'index']);
    Route::get('/knowledge/{id}', [KnowledgeBaseController::class, 'show']);
    Route::get('/knowledge/search', [KnowledgeBaseController::class, 'search']);

// Chatbot Route
    Route::post('/chatbot', [ChatbotController::class, 'ask'])->middleware('auth:sanctum');

// Admin Knowledge Base Routes
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/knowledge', [KnowledgeBaseController::class, 'store']);
    });
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/farms/{id}/approve', [FarmController::class, 'approve']);
    Route::post('/products/{id}/approve', [ProductController::class, 'approve']);
    Route::get('/admin/farms', [FarmController::class, 'pending']);
    Route::get('/admin/products', [ProductController::class, 'pending']);
});
