<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/ruta-de-ejemplo', function () {
    return Inertia::render('TuVendor/TuPaquete/Pages/TuComponente');
})->name('tu.ruta');
// Hello world route from Hello.vue
Route::get('/corporate-priority', function () {
    return Inertia::render('Amx/CorporatePriority/CorporatePriority');
})->name('corporate-priority');
