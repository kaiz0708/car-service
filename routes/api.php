<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Psr\Http\Message\ServerRequestInterface;
use App\Http\Middleware\CustomAuthenticate;
use App\Http\Middleware\CustomCheckScope;




Route::prefix('products')->group(function () {
    global $customAuth, $customCheckScope;
    Route::get('/', [ProductController::class, 'index'])
        ->middleware([new CustomAuthenticate(), new CustomCheckScope('product:list')]);
});

Route::post('/test', function (Request $request) {
    return response()->json([
        'message' => 'Received data successfully'
    ]);
})->middleware(['auth:api', 'scope:product:list']);


Route::post('/oauth/token', function (ServerRequestInterface $serverRequest) {
    return app(AccessTokenController::class)->issueToken($serverRequest);
})->middleware('throttle');
