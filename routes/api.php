<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PatientAllergyController;
use Illuminate\Support\Facades\Route;

Route::middleware('tenant')->group(function (): void {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

/*
| Rutas habilitadas para verificación funcional del Sprint 2.
*/
Route::get('/patients/{patient}/allergies', [PatientAllergyController::class, 'index']);
Route::post('/patients/{patient}/allergies', [PatientAllergyController::class, 'store']);
Route::get('/patient-allergies/{allergy}', [PatientAllergyController::class, 'show']);
Route::put('/patient-allergies/{allergy}', [PatientAllergyController::class, 'update']);
Route::delete('/patient-allergies/{allergy}', [PatientAllergyController::class, 'destroy']);

Route::middleware(['tenant', 'jwt.auth'])->group(function (): void {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::middleware(['tenant', 'jwt.refresh'])->group(function (): void {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});
