<?php

use Illuminate\Support\Facades\Route;
use Modules\CA\Http\Controllers\CAKnowledgeBaseController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', 'verified', \Modules\CA\Http\Middleware\RequireCAModule::class])
    ->prefix('ca')
    ->name('ca.')
    ->group(function () {
        
    Route::get('/knowledge-base', [CAKnowledgeBaseController::class, 'index'])->name('knowledge-base.index');
    
    // CA Clients
    Route::get('/clients', \Modules\CA\Livewire\ClientIndex::class)->name('clients.index');
    Route::get('/clients/onboard', \Modules\CA\Livewire\ClientOnboardingWizard::class)->name('clients.onboard');
    Route::get('/clients/{clientId}', \Modules\CA\Livewire\ClientShow::class)->name('clients.show');
    Route::get('/clients/{clientId}/compliance/{clientComplianceId}', \Modules\CA\Livewire\ComplianceWorkspace::class)->name('clients.compliance.workspace');
    
    // Dashboards & Operations
    Route::get('/dashboard', \Modules\CA\Livewire\OperationsDashboard::class)->name('dashboard');
    Route::get('/calendar', \Modules\CA\Livewire\ComplianceCalendar::class)->name('calendar');
    Route::get('/reporting', \Modules\CA\Livewire\ComplianceReporting::class)->name('reporting');

    // Secure Document Download
    Route::get('/documents/{document}/download', [\Modules\CA\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
});
