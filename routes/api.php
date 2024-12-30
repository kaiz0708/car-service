<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Psr\Http\Message\ServerRequestInterface;




Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
});


Route::post('/test', function (Request $request) {
    return response()->json([
        'message' => 'Received data successfully'
    ]);
});


Route::post('/oauth/token', function (ServerRequestInterface $serverRequest) {
    return app(AccessTokenController::class)->issueToken($serverRequest);
})->middleware('throttle');