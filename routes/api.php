<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\KrsController;

Route::prefix('v1')->middleware('api.key')->group(function () {
    Route::get('/courses', [KrsController::class, 'courses']);
    Route::get('/krs/{student_id}', [KrsController::class, 'krs']);
    Route::post('/krs/submit', [KrsController::class, 'submit'])->middleware('sso.jwt');
});
