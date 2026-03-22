<div class="flex-1 overflow-y-auto p-8">
<!-- Breadcrumbs -->
<nav class="flex items-center gap-2 text-sm text-slate-500 mb-6">
<a href="{{ route('whatsapp.templates.index') }}" class="hover:text-slate-900 dark:hover:text-slate-100 transition-colors">Templates</a>
<span class="material-symbols-outlined text-xs">chevron_right</span>
<span class="text-slate-900 dark:text-slate-100 font-medium whitespace-nowrap overflow-hidden text-ellipsis max-w-[200px]" title="{{ $template->display_title }}">{{ $template->display_title }}</span>
</nav>

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
<div>
<div class="flex items-center gap-3 mb-2">
@if($template->status === 'approved')
    <span class="bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-bold px-2 py-0.5 rounded uppercase tracking-wider">Approved</span>
@elseif($template->status === 'rejected')
    <span class="bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 text-xs font-bold px-2 py-0.5 rounded uppercase tracking-wider">Rejected</span>
@elseif(in_array($template->status, ['pending', 'in_appeal']))
    <span class="bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 text-xs font-bold px-2 py-0.5 rounded uppercase tracking-wider">Pending</span>
@else
    <span class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 text-xs font-bold px-2 py-0.5 rounded uppercase tracking-wider">Draft</span>
@endif
<span class="text-xs text-slate-400">• Updated {{ $template->updated_at ? $template->updated_at->diffForHumans() : 'N/A' }}</span>
</div>
<h1 class="text-3xl font-black text-slate-900 dark:text-slate-100">{{ $template->display_title }}</h1>
<p class="text-slate-500 mt-1 capitalize">{{ $template->category }} • {{ $template->remote_template_name }} • {{ strtoupper($template->language_code) }}</p>
</div>
<div class="flex gap-3">
@if(in_array($template->status, ['rejected', 'draft']))
    <a href="{{ route('whatsapp.templates.edit', $template->id) }}" class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-xl font-bold text-sm hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20">
    <span class="material-symbols-outlined text-lg">edit</span>
        Edit Template
    </a>
@else
    <button wire:click="duplicateTemplate" class="flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
    <span class="material-symbols-outlined text-lg">content_copy</span>
        Duplicate
    </button>
@endif
</div>
</div>

@if($template->rejection_reason)
    <div class="rounded-xl bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800/30 mb-8">
        <h3 class="text-sm font-medium text-red-800 dark:text-red-400 mb-1">Meta Rejection Reason:</h3>
        <p class="text-sm text-red-700 dark:text-red-300">{{ $template->rejection_reason }}</p>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
<!-- Preview Section -->
<div class="lg:col-span-1">
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden flex flex-col sticky top-0">
<div class="bg-slate-50 dark:bg-slate-800/50 p-4 border-b border-slate-200 dark:border-slate-800">
<h3 class="font-bold text-sm flex items-center gap-2">
<span class="material-symbols-outlined text-primary">visibility</span>
                                    Phone Preview
                                </h3>
</div>
<div class="p-8 flex justify-center bg-slate-100 dark:bg-slate-950">
<!-- Phone UI Wrapper -->
<div class="w-full max-w-[280px] bg-[#e5ddd5] dark:bg-slate-900 rounded-[2.5rem] border-[8px] border-slate-800 dark:border-slate-700 aspect-[9/18] overflow-hidden relative shadow-2xl">
<div class="absolute top-0 w-full h-12 bg-[#075e54] flex items-center px-4 gap-2">
<div class="w-8 h-8 rounded-full bg-slate-200/20"></div>
<div class="flex-1">
<div class="h-2 w-16 bg-white/40 rounded-full mb-1"></div>
<div class="h-1.5 w-10 bg-white/20 rounded-full"></div>
</div>
</div>
<!-- Message Bubble -->
<div class="mt-16 p-3">
    <div class="bg-white dark:bg-slate-800 rounded-lg p-3 shadow-sm relative text-[11px] leading-snug">
    <div class="mb-2">
    @if($template->header_type && $template->header_type !== 'none')
        @if($template->header_type === 'text')
            <p class="font-bold mb-1">{{ $template->header_text }}</p>
        @else
            <div class="rounded-lg overflow-hidden mb-2">
            <div class="bg-slate-200 dark:bg-slate-700 aspect-video flex items-center justify-center text-slate-400" data-alt="Abstract minimal geometric background pattern for template header">
            <span class="material-symbols-outlined">{{ strtolower($template->header_type) === 'video' ? 'videocam' : 'image' }}</span>
            </div>
            </div>
        @endif
    @endif
    <div class="text-slate-600 dark:text-slate-400 whitespace-pre-wrap">{{ $template->body_text }}</div>
    @if($template->footer_text)
        <p class="text-[10px] text-slate-500 mt-2">{{ $template->footer_text }}</p>
    @endif
    </div>
    <div class="flex justify-end items-center gap-1">
    <span class="text-[9px] text-slate-400">10:42 AM</span>
    <span class="material-symbols-outlined text-[10px] text-blue-400">done_all</span>
    </div>
    @if($template->button_count > 0)
    <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-700 flex flex-col gap-2">
        @foreach($template->buttons as $btn)
        <div class="bg-slate-50 dark:bg-slate-700/50 py-1.5 rounded text-center text-primary font-bold flex items-center justify-center gap-1">
            @if($btn->type === 'url')
                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
            @elseif($btn->type === 'phone_number')
                <span class="material-symbols-outlined text-[14px]">call</span>
            @elseif($btn->type === 'quick_reply')
                <span class="material-symbols-outlined text-[14px]">reply</span>
            @endif
            {{ $btn->text }}
        </div>
        @endforeach
    </div>
    @endif
    </div>
</div>
</div>
</div>
</div>
</div>

<!-- Configuration Details -->
<div class="lg:col-span-2 space-y-6">
<!-- Template Structure Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
<div class="p-6 border-b border-slate-200 dark:border-slate-800">
<h3 class="font-bold text-lg">Template Structure</h3>
</div>
<div class="p-6 space-y-6">
<div class="space-y-2">
<label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Header</label>
<div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
@if($template->header_type === 'none')
<div class="flex items-center gap-3">
    <span class="text-sm font-medium italic text-slate-400">None</span>
</div>
@elseif($template->header_type === 'text')
<div class="flex items-center gap-3">
    <span class="material-symbols-outlined text-slate-400">match_case</span>
    <span class="text-sm font-medium">Text ({{ $template->header_text }})</span>
</div>
@else
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-slate-400">{{ strtolower($template->header_type) === 'video' ? 'videocam' : 'image' }}</span>
<span class="text-sm font-medium">Media ({{ ucfirst($template->header_type) }})</span>
@if(!in_array($template->header_type, ['text', 'none']))
<span class="text-xs bg-slate-200 dark:bg-slate-700 px-2 py-0.5 rounded">Optional</span>
@endif
</div>
@endif
</div>
</div>
<div class="space-y-2">
<label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Body Message</label>
<div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800 font-mono text-sm leading-relaxed whitespace-pre-wrap">
{{ $template->body_text }}
</div>
<div class="flex flex-wrap gap-4 mt-2">
@php $varCount = preg_match_all('/\{\{\d+\}\}/', $template->body_text); @endphp
@if($varCount > 0)
<div class="text-xs text-slate-500 flex items-center gap-1">
<span class="material-symbols-outlined text-sm">info</span>
Found {{ $varCount }} variables required during sending.
</div>
@else
<div class="text-xs text-slate-500 flex items-center gap-1">
<span class="material-symbols-outlined text-sm">info</span>
No variables required.
</div>
@endif
</div>
</div>

@if($template->button_count > 0)
<div class="space-y-2">
<label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Buttons</label>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
@foreach($template->buttons as $btn)
<div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800 flex justify-between items-center">
<div class="flex flex-col">
<span class="text-xs text-slate-400">
    @if($btn->type === 'url') Call to Action
    @elseif($btn->type === 'phone_number') Call to Action
    @elseif($btn->type === 'quick_reply') Quick Reply
    @endif
</span>
<span class="text-sm font-bold">{{ $btn->text }}</span>
</div>
<span class="material-symbols-outlined text-slate-400">
    @if($btn->type === 'url') link
    @elseif($btn->type === 'phone_number') call
    @elseif($btn->type === 'quick_reply') open_in_new
    @endif
</span>
</div>
@endforeach
</div>
</div>
@endif

</div>
</div>
<!-- Meta Data Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
<div class="p-6 border-b border-slate-200 dark:border-slate-800">
<h3 class="font-bold text-lg">Platform Metadata</h3>
</div>
<div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-6">
<div>
<p class="text-xs text-slate-400 uppercase font-bold mb-1">Namespace</p>
<p class="text-sm font-medium">{{ optional(optional($template)->whatsappAccount)->whatsapp_business_namespace ?? 'N/A' }}</p>
</div>
<div>
<p class="text-xs text-slate-400 uppercase font-bold mb-1">Template ID</p>
<p class="text-sm font-medium">{{ $template->remote_template_id ?: 'Pending' }}</p>
</div>
<div>
<p class="text-xs text-slate-400 uppercase font-bold mb-1">Quality Rating</p>
<div class="flex items-center gap-1">
@if(strtolower($template->quality_rating) === 'high' || strtolower($template->quality_rating) === 'green')
    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
    <p class="text-sm font-medium text-emerald-600">{{ $template->quality_rating }}</p>
@elseif(strtolower($template->quality_rating) === 'medium' || strtolower($template->quality_rating) === 'yellow')
    <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
    <p class="text-sm font-medium text-yellow-600">{{ $template->quality_rating }}</p>
@elseif(strtolower($template->quality_rating) === 'low' || strtolower($template->quality_rating) === 'red')
    <div class="w-2 h-2 rounded-full bg-red-500"></div>
    <p class="text-sm font-medium text-red-600">{{ $template->quality_rating }}</p>
@else
    <p class="text-sm font-medium">{{ $template->quality_rating ?: 'N/A' }}</p>
@endif
</div>
</div>
<div>
<p class="text-xs text-slate-400 uppercase font-bold mb-1">Language</p>
<p class="text-sm font-medium">{{ strtoupper($template->language_code) }}</p>
</div>
</div>
</div>

<!-- Statistics Card -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl">
<p class="text-xs font-bold text-slate-400 uppercase mb-2">Total Sent</p>
<div class="flex items-end justify-between">
<span class="text-2xl font-black">--</span>
<span class="text-emerald-500 text-xs font-bold flex items-center"></span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl">
<p class="text-xs font-bold text-slate-400 uppercase mb-2">Read Rate</p>
<div class="flex items-end justify-between">
<span class="text-2xl font-black">--</span>
<span class="text-emerald-500 text-xs font-bold flex items-center"></span>
</div>
</div>
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl">
<p class="text-xs font-bold text-slate-400 uppercase mb-2">CTR</p>
<div class="flex items-end justify-between">
<span class="text-2xl font-black">--</span>
<span class="text-emerald-500 text-xs font-bold flex items-center"></span>
</div>
</div>
</div>

</div>
</div>
</div>
