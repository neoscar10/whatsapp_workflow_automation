<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'module:ca'])->prefix('ca')->group(function () {
    Route::get('/dashboard', \Modules\CA\Livewire\CADashboard::class)->name('ca.dashboard');
});
