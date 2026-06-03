@props(['company', 'showText' => false])

@php
    $verification = \App\Models\CompanyVerification::where('company_id', $company->id)->first();
    $status = $verification ? $verification->status : 'not_started';
@endphp

@if($status === 'verified')
    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-850 dark:bg-emerald-950 dark:text-emerald-400 align-middle" title="Verified Business">
        <span class="material-symbols-outlined text-sm font-bold text-emerald-600">verified</span>
        @if($showText)
            <span>Verified</span>
        @endif
    </span>
@else
    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-450 align-middle" title="Unverified Business">
        <span class="material-symbols-outlined text-sm text-slate-400">info</span>
        @if($showText)
            <span>Unverified</span>
        @endif
    </span>
@endif
