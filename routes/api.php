<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\Api\V1\InspectionApiController;
use App\Http\Controllers\Api\V1\MasterDataApiController;
use App\Http\Controllers\Api\V1\ReportApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1 (V1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public Auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Authenticated API Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Dashboard
        Route::get('/dashboard', [DashboardApiController::class, 'index']);

        // Master Data
        Route::prefix('master')->group(function () {
            Route::get('/business-areas', [MasterDataApiController::class, 'businessAreas']);
            Route::get('/units', [MasterDataApiController::class, 'units']);
            Route::get('/employees', [MasterDataApiController::class, 'employees']);
            Route::get('/devices', [MasterDataApiController::class, 'devices']);
        });

        // Inspections
        Route::prefix('inspections')->group(function () {
            Route::get('/', [InspectionApiController::class, 'index']);
            Route::get('/{inspection}', [InspectionApiController::class, 'show']);
            Route::post('/{inspection}/checklist', [InspectionApiController::class, 'saveChecklist']);
            Route::post('/{inspection}/submit', [InspectionApiController::class, 'submit']);
            Route::post('/{inspection}/attachments', [InspectionApiController::class, 'uploadAttachment']);
            Route::get('/{inspection}/history', [InspectionApiController::class, 'history']);
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/summary', [ReportApiController::class, 'summary']);
        });
    });
});
