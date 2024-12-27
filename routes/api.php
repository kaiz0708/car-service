<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;




Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
});


Route::post('/test', function (Request $request) {
    return response()->json([
        'message' => 'Received data successfully'
    ]);
});

Route::post('/auth/token', [AuthController::class, 'getToken']);

// Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize']);
// Route::post('/oauth/token', [AccessTokenController::class, 'issueToken']);
