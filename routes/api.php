<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Configuration\AgenciesController;
use App\Http\Controllers\Configuration\BrandsController;
use App\Http\Controllers\Configuration\MandatesController;
use App\Http\Controllers\Configuration\ProductsController;
use App\Http\Controllers\General\CalendarController;
use App\Http\Controllers\General\DocumentsController;
use App\Http\Controllers\General\LinksController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UploadsController;
use App\Http\Controllers\Workflow\CustomersController;
use App\Http\Controllers\Workflow\PaperworksController;
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

Route::group(['prefix' => 'auth'], function () {
    Route::get('csrf', [AuthController::class, 'csrf']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::group(['middleware' => 'auth:sanctum'], function() {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});
Route::get('agents', [UsersController::class, 'agents']);

Route::group(['middleware' => 'auth:sanctum'], function() {

    Route::post('uploads', [UploadsController::class, 'index']);

    Route::get('agencies', [AgenciesController::class, 'index']);
    Route::post('agencies', [AgenciesController::class, 'store']);
    Route::put('agencies/{id}', [AgenciesController::class, 'update']);
    Route::delete('agencies/{id}', [AgenciesController::class, 'destroy']);

    Route::get('mandates', [MandatesController::class, 'index']);
    Route::post('mandates', [MandatesController::class, 'store']);
    Route::put('mandates/{id}', [MandatesController::class, 'update']);
    Route::delete('mandates/{id}', [MandatesController::class, 'destroy']);

    Route::get('brands', [BrandsController::class, 'index']);
    Route::post('brands', [BrandsController::class, 'store']);
    Route::put('brands/{id}', [BrandsController::class, 'update']);
    Route::delete('brands/{id}', [BrandsController::class, 'destroy']);

    Route::get('products', [ProductsController::class, 'index']);
    Route::post('products', [ProductsController::class, 'store']);
    Route::get('products/{id}', [ProductsController::class, 'show']);
    Route::put('products/{id}', [ProductsController::class, 'update']);
    Route::delete('products/{id}', [ProductsController::class, 'destroy']);

    Route::get('calendar', [CalendarController::class, 'index']);
    Route::post('calendar', [CalendarController::class, 'store']);
    Route::put('calendar/{id}', [CalendarController::class, 'update']);
    Route::delete('calendar/{id}', [CalendarController::class, 'destroy']);
    Route::get('appointments', [CalendarController::class, 'search']);

    Route::get('documents', [DocumentsController::class, 'index']);
    Route::post('documents', [DocumentsController::class, 'store']);
    Route::put('documents/{id}', [DocumentsController::class, 'update']);
    Route::delete('documents/{id}', [DocumentsController::class, 'destroy']);
    Route::get('documents/{id}/download', [DocumentsController::class, 'download']);

    Route::get('links', [LinksController::class, 'index']);
    Route::post('links', [LinksController::class, 'store']);
    Route::put('links/{id}', [LinksController::class, 'update']);
    Route::delete('links/{id}', [LinksController::class, 'destroy']);

    Route::get('roles', [RolesController::class, 'index']);
    Route::get('users', [UsersController::class, 'index']);
    // Route::get('agents', [UsersController::class, 'agents']);

    Route::get('customers', [CustomersController::class, 'index']);
    Route::get('customers/{id}', [CustomersController::class, 'show']);
    Route::post('customers', [CustomersController::class, 'store']);
    Route::put('customers/{id}', [CustomersController::class, 'update']);

    Route::get('paperworks', [PaperworksController::class, 'index']);
});

