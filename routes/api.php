<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Endpoint for external apps to verify user credentials (SSO Provider)
// Protected by Bearer Token (API Key provided in settings)
Route::post('/sso/user', [ApiController::class, 'ssoUser']);
Route::post('/user', [ApiController::class, 'ssoUser']); // Alias for compatibility
