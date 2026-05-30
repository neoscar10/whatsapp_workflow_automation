@props(['status'])

@php
    $statusString = $status instanceof \BackedEnum ? $status->value : $status;
    
    $classes = match ($statusString) {
        'pending' => 'badge bg-warning-subtle text-warning',
        'successful', 'success' => 'badge bg-success-subtle text-success',
        'failed' => 'badge bg-danger-subtle text-danger',
        'reversed' => 'badge bg-dark-subtle text-dark',
        'processing' => 'badge bg-info-subtle text-info',
        'abandoned' => 'badge bg-secondary-subtle text-secondary border border-slate-200',
        'expired' => 'badge bg-light text-slate-500 border border-dashed border-slate-300',
        default => 'badge bg-secondary-subtle text-secondary',
    };
@endphp

<span class="{{ $classes }} text-uppercase">
    {{ $statusString }}
</span>
