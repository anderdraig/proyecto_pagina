<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::post('/register', [AuthController::class, 'register']);
Route::middleware([Illuminate\Session\Middleware\StartSession::class])->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});
Route::post('/quotations', [QuotationController::class, 'createQuotation'])->middleware('auth:api');
Route::patch('/update',[AuthController::class, 'update'])->middleware('auth:api');
Route::delete('/destroy',[AuthController::class, 'destroy'])->middleware('auth:api');
Route::get('/store',[AuthController::class, 'store'])->middleware('auth:api');
Route::post('/upload-profile-imagen', [AuthController::class, 'uploadProfileImagen'])->middleware('auth:api');
Route::post('/products-insert', [ProductController::class, 'insertProduct']);
Route::get('/products', [ProductController::class, 'getProduct']);
Route::get('/products/{id}', [ProductController::class, 'getProductById']);
Route::post('/orders', [OrderController::class, 'placeOrder'])->middleware('auth:api');