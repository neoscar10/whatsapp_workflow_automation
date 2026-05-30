@props(['gateway', 'size' => 'sm'])

@php
    $gatewayString = $gateway instanceof \BackedEnum ? $gateway->value : strtolower((string)$gateway);
    
    $logo = match ($gatewayString) {
        'razorpay' => '<span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 rounded border border-blue-200 dark:border-blue-800 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400 animate-pulse"></span>Razorpay</span>',
        'cashfree' => '<span class="px-2 py-0.5 text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300 rounded border border-teal-200 dark:border-teal-800 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-teal-600 dark:bg-teal-400 animate-pulse"></span>Cashfree</span>',
        default => '<span class="px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-800 dark:bg-slate-900/40 dark:text-slate-300 rounded border border-slate-200 dark:border-slate-800 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-slate-600 dark:bg-slate-400"></span>' . ucfirst($gatewayString) . '</span>',
    };
@endphp

<div class="inline-flex items-center gap-1.5">
    {!! $logo !!}
</div>
