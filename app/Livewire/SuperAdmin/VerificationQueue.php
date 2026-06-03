<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Company;
use App\Models\CompanyVerification;
use App\Models\CompanyVerificationDocument;
use App\Models\CompanyVerificationDocumentVersion;
use Livewire\Component;
use Livewire\WithPagination;

class VerificationQueue extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $countryFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'countryFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCountryFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        // 1. Build Query
        $query = CompanyVerification::with(['company', 'documents.latestVersion']);

        if (!empty($this->search)) {
            $query->whereHas('company', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->countryFilter)) {
            $query->whereHas('company', function ($q) {
                $q->where('country', $this->countryFilter);
            });
        }

        $verifications = $query->orderBy('last_activity_at', 'desc')->paginate(10);

        // 2. Compute Top Summary Widgets
        $totalCompanies = Company::count();
        $verifiedCompanies = CompanyVerification::where('status', 'verified')->count();
        $pendingReviews = CompanyVerification::where('status', 'under_review')
            ->orWhere('status', 'partially_approved')
            ->count();
        $rejectedDocsCount = CompanyVerificationDocumentVersion::where('status', 'rejected')->count();

        // 3. Get all available countries for filtering
        $countries = Company::whereNotNull('country')->distinct()->pluck('country');

        return view('livewire.super-admin.verification-queue', [
            'verifications' => $verifications,
            'totalCompanies' => $totalCompanies,
            'verifiedCompanies' => $verifiedCompanies,
            'pendingReviews' => $pendingReviews,
            'rejectedDocsCount' => $rejectedDocsCount,
            'countries' => $countries,
        ])
        ->layout('layouts.super-admin', [
            'title' => 'Verification Queue',
            'activeNav' => 'verification-queue',
        ]);
    }
}
