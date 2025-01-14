<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function (){
    Route::Post('register','register');
    Route::Post('login','login');
});
Route::middleware('auth:sanctum')->group( function () {
    Route::resource('products', ProductController::class);
    Route::post('logout',[AuthController::class,'logout']);
    Route::post('logout-all',[AuthController::class,'logoutFromAll']); // optional
});
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');