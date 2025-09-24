<?php

use Amx\CorporatePriority\Controllers\CorporatePriorityController;
use Illuminate\Support\Facades\Route;

Route::get('reservation', [CorporatePriorityController::class, 'getReservation'])->name("get-reservation");
Route::post('seat-map', [CorporatePriorityController::class, 'getSeatMap'])->name("get-seat-map");
Route::post('assign-seat', [CorporatePriorityController::class, 'assignSeat'])->name("assign-seat");
