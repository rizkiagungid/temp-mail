<?php

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppController;

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

Route::get('cron/{password}', [AppController::class, 'cron']);

Route::get('domains/{key}', [APIController::class, 'domains']);
Route::get('email/{email}/{key}', [APIController::class, 'email']);
Route::get('messages/{email}/{key}', [APIController::class, 'messages']);
Route::delete('message/{message_id}/{key}', [APIController::class, 'delete']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
