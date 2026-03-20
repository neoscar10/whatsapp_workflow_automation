<?php

use App\Livewire\Web\Auth\LoginPage;
use App\Livewire\Web\Auth\RegisterCompanyPage;
use App\Livewire\Web\Company\CompanyProfilePage;
use App\Livewire\Web\Dashboard\DashboardPage;
use Illuminate\Support\Facades\Route;

Route::get('/', RegisterCompanyPage::class)->name('home');
Route::get('/register', RegisterCompanyPage::class)->name('company.register');

Route::middleware('guest')->group(function () {
    Route::get('/login', LoginPage::class)->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardPage::class)->name('dashboard');
    Route::get('/company/profile', CompanyProfilePage::class)->name('company.profile');
    Route::get('/panel', function () {
        return redirect()->route('dashboard');
    })->name('panel.home');
});

Route::get('/debug-route', function () {
    return 'ok';
});
