<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\WebsiteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('websites')->group(function (){
    Route::get('index', [WebsiteController::class, 'index']);
    Route::post('save', [WebsiteController::class, 'store']);
    Route::post('subscribe', [WebsiteController::class, 'subscribe']);
});


Route::prefix('posts')->group(function (){

    Route::get('index', [PostController::class, 'index']);
    Route::post('save', [PostController::class, 'store']);

});
