<?php

use Amx\CorporatePriority\Controllers\CorporatePriorityController;
use Illuminate\Support\Facades\Route;


Route::get('reservation',                       [CorporatePriorityController::class, 'getReservation'])->name("get-reservation");
Route::post('seat-map',                         [CorporatePriorityController::class, 'getSeatMap'])->name("get-seat-map");
Route::post('assign-seat',                      [CorporatePriorityController::class, 'assignSeat'])->name("assign-seat");
Route::post('validate/corporate-priority',      [CorporatePriorityController::class, 'validateCorporate'])->name("validate-corporate");
Route::post('condonate-reservation',            [CorporatePriorityController::class, 'condonateReservation'])->name("condonate-seats");
