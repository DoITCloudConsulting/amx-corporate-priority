<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/corporate-priority', function () {
    return Inertia::render('Amx/CorporatePriority/CorporatePriority');
})->name('corporate-priority');
