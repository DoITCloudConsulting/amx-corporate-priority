<?php

use Amx\CorporatePriority\Controllers\CorporatePriorityController;
use Illuminate\Support\Facades\Route;

// The `api` group runs first so StartSession / Sanctum-stateful is available
// before `at_least_one` evaluates auth. Without it these routes have no session,
// so session-authenticated portal users (no jwt_token) always 401 (AMBIZ-605).
Route::middleware(['api', 'at_least_one'])->group(function () {
    Route::get('reservation',                       [CorporatePriorityController::class, 'getReservation'])->name("get-reservation");
    Route::post('seat-map',                         [CorporatePriorityController::class, 'getSeatMap'])->name("get-seat-map");
    Route::post('assign-seat',                      [CorporatePriorityController::class, 'assignSeat'])->name("assign-seat");
    Route::post('validate/corporate-priority',      [CorporatePriorityController::class, 'validateCorporate'])->name("validate-corporate");
    Route::post('condonate-reservation',            [CorporatePriorityController::class, 'condonateReservation'])->name("condonate-seats");

    Route::post('case-creation',            [CorporatePriorityController::class, 'createCase'])->name("createCase");
});
