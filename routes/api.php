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
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UploadsController;
use App\Http\Controllers\Workflow\CustomersController;
use App\Http\Controllers\Workflow\PaperworksController;
use App\Http\Controllers\Workflow\TicketsController;
use App\Http\Controllers\Workflow\CommunicationsController;
use App\Http\Controllers\StatementsController;
use App\Http\Controllers\ContractUploadsController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\DashboardController;
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
        Route::get('notifications', [UsersController::class, 'notifications']);
        Route::post('notifications/{id}/read', [UsersController::class, 'read']);
        Route::post('notifications/{id}/unread', [UsersController::class, 'unread']);
    });
});

Route::get('empty', function() {
    // Truncate table sessions
    DB::table('sessions')->truncate();
    DB::table('personal_access_tokens')->truncate();
    return response()->json(['message' => 'Session table truncated']);
});

Route::group(['middleware' => 'auth:sanctum'], function() {

    Route::post('contracts/upload', [ContractUploadsController::class, 'store'])->name('contracts.upload');

    Route::post('uploads', [UploadsController::class, 'index']);

    Route::put('profile', [UsersController::class, 'updateProfile']);
    Route::put('profile/password', [UsersController::class, 'updatePassword']);

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
    Route::get('products/{id}/feebands', [ProductsController::class, 'feebands']);
    Route::post('products/{id}/feebands', [ProductsController::class, 'addFeeband']);
    Route::delete('products/{id}/feebands/{feebandId}', [ProductsController::class, 'destroyFeeband']);
    Route::put('products/{id}/feebands/{feebandId}', [ProductsController::class, 'updateFeeband']);

    Route::get('calendar', [CalendarController::class, 'index']);
    Route::post('calendar', [CalendarController::class, 'store']);
    Route::put('calendar/{id}', [CalendarController::class, 'update']);
    Route::delete('calendar/{id}', [CalendarController::class, 'destroy']);
    Route::get('appointments', [CalendarController::class, 'search']);
    Route::get('calendar-users', [CalendarController::class, 'users']);
    Route::get('calendar-cities', [CalendarController::class, 'cities']);

    Route::get('documents', [DocumentsController::class, 'index']);
    Route::post('documents/new-folder', [DocumentsController::class, 'newFolder']);
    Route::post('documents/upload', [DocumentsController::class, 'upload']);
    Route::delete('documents/remove', [DocumentsController::class, 'destroy']);
    Route::get('documents/download', [DocumentsController::class, 'download']);

    Route::get('links', [LinksController::class, 'index']);
    Route::post('links', [LinksController::class, 'store']);
    Route::put('links/{id}', [LinksController::class, 'update']);
    Route::delete('links/{id}', [LinksController::class, 'destroy']);

    Route::get('roles', [RolesController::class, 'index']);
    Route::get('users', [UsersController::class, 'index']);
    Route::post('users', [UsersController::class, 'store']);
    Route::get('users/{id}', [UsersController::class, 'show']);
    Route::put('users/{id}', [UsersController::class, 'update']);
    Route::post('users/{id}/relationships', [UsersController::class, 'addRelationship']);
    Route::delete('users/{id}/relationships/{relatedId}', [UsersController::class, 'destroyRelationship']);

    Route::get('users/{id}/brands', [UsersController::class, 'brands']);
    Route::post('users/{id}/brands', [UsersController::class, 'addBrand']);
    Route::patch('users/{id}/brands/{brandId}', [UsersController::class, 'updateBrand']);
    Route::delete('users/{id}/brands/{brandId}', [UsersController::class, 'destroyBrand']);
    Route::get('users/{id}/appointments', [UsersController::class, 'appointments']);

    Route::get('agents', [UsersController::class, 'agents']);
    Route::get('structures', [UsersController::class, 'structures']);

    Route::get('customers', [CustomersController::class, 'index']);
    Route::get('customers-export', [CustomersController::class, 'export']);
    Route::get('customers/{id}', [CustomersController::class, 'show']);
    Route::post('customers', [CustomersController::class, 'store']);
    Route::put('customers/{id}', [CustomersController::class, 'update']);
    Route::put('customers/{id}/confirm', [CustomersController::class, 'confirm']);
    Route::get('cities', [CustomersController::class, 'cities']);

    Route::get('reports', [ReportsController::class, 'index']);
    Route::put('reports/{id}/update', [ReportsController::class, 'update']);
    Route::delete('reports/{id}/delete', [ReportsController::class, 'delete']);
    Route::get('reports/{id}/entries', [ReportsController::class, 'entries']);
    Route::post('reports/{id}/entries/add', [ReportsController::class, 'addEntry']);
    Route::put('reports/{id}/entries/{entryId}/payout-confirmed', [ReportsController::class, 'updatePayoutConfirmed']);
    Route::delete('reports/{id}/entries/{entryId}/delete', [ReportsController::class, 'deleteEntry']);
    Route::get('reports/admin', [ReportsController::class, 'admin']);
    Route::get('reports/production', [ReportsController::class, 'production']);
    Route::get('reports/appointments', [ReportsController::class, 'appointments']);

    Route::get('ai-paperworks', [AIController::class, 'paperworks']);
    Route::get('ai-paperworks/{id}', [AIController::class, 'show']);
    Route::get('ai-paperworks/{id}/download', [AIController::class, 'download']);
    Route::post('ai-paperworks/{id}/process', [AIController::class, 'process']);
    Route::put('ai-paperworks/{id}', [AIController::class, 'update']);
    Route::post('ai-paperworks/{id}/confirm', [AIController::class, 'confirm']);
    Route::post('ai-paperworks/{id}/cancel', [AIController::class, 'cancel']);

    Route::get('paperworks', [PaperworksController::class, 'index']);
    Route::get('paperworks/{id}', [PaperworksController::class, 'show']);
    // Route::get('paperworks/{id}/payout', [PaperworksController::class, 'calculatePayout']);
    Route::put('paperworks/{id}', [PaperworksController::class, 'update']);
    Route::post('paperworks/{id}/documents', [PaperworksController::class, 'documents']);
    Route::get('paperworks/{id}/documents/{documentId}/download', [PaperworksController::class, 'downloadDocument']);
    // Route::put('paperworks/{id}/confirm', [PaperworksController::class, 'confirm']);
    // Route::put('paperworks/{id}/confirm-partner-sent', [PaperworksController::class, 'confirmPartnerSent']);
    Route::post('paperworks', [PaperworksController::class, 'store']);
    Route::post('paperworks/bulk-update-statuses', [PaperworksController::class, 'bulkUpdate']);

    Route::get('tickets', [TicketsController::class, 'index']);
    Route::post('tickets', [TicketsController::class, 'store']);
    Route::get('tickets/{id}', [TicketsController::class, 'show']);
    Route::put('tickets/{id}/close', [TicketsController::class, 'close']);
    Route::post('tickets/{id}/comments', [TicketsController::class, 'comment']);

    Route::get('communications', [CommunicationsController::class, 'index']);
    Route::post('communications', [CommunicationsController::class, 'store']);
    Route::get('communications/{id}', [CommunicationsController::class, 'show']);
    Route::put('communications/{id}', [CommunicationsController::class, 'update']);

    Route::get('statements', [StatementsController::class, 'index']);

    Route::prefix('dashboard')->group(function () {
        Route::get('/paperworks', [DashboardController::class, 'searchPaperwork']);
        Route::get('/stats', [DashboardController::class, 'getPaperworkStats']);
        Route::get('/brand-stats', [DashboardController::class, 'getBrandStats']);
        Route::get('/time-series', [DashboardController::class, 'getTimeSeriesData']);
        Route::get('/agents', [DashboardController::class, 'getAgents']);
        Route::get('/customers', [DashboardController::class, 'getCustomers']);
    });
});
