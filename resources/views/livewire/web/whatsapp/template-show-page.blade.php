<div class="mx-auto w-full max-w-[1400px] space-y-8 p-10">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('whatsapp.templates.index') }}" class="group flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 transition-colors">
                <svg class="h-5 w-5 text-slate-500 group-hover:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                    {{ $template->display_title }}
                    @if($template->status === 'approved')
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Approved</span>
                    @elseif($template->status === 'rejected')
                        <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Rejected</span>
                    @elseif(in_array($template->status, ['pending', 'in_appeal']))
                        <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-sm font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Pending</span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-sm font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10">Draft</span>
                    @endif
                </h1>
                <p class="mt-2 text-sm text-slate-500 font-mono">{{ $template->remote_template_name }} &bull; {{ strtoupper($template->language_code) }} &bull; <span class="capitalize">{{ $template->category }}</span></p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if(in_array($template->status, ['rejected', 'draft']))
                <a href="{{ route('whatsapp.templates.edit', $template->id) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    Edit
                </a>
            @else
                {{-- Meta limits editing approved templates to 1 time per day. The safest workflow is often copying. --}}
                <button wire:click="duplicateTemplate" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                    Duplicate to Edit
                </button>
            @endif
        </div>
    </div>

    @if($template->rejection_reason)
        <div class="rounded-xl bg-red-50 p-4 border border-red-200">
            <h3 class="text-sm font-medium text-red-800 mb-1">Meta Rejection Reason:</h3>
            <p class="text-sm text-red-700">{{ $template->rejection_reason }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">
        
        {{-- Left: Meta Info Read-Only --}}
        <div class="space-y-8">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-6">Meta Configuration</h3>
                
                <dl class="space-y-6 text-sm">
                    <div class="grid grid-cols-3 gap-4 border-b border-slate-100 pb-4">
                        <dt class="font-medium text-slate-500">Remote ID</dt>
                        <dd class="col-span-2 text-slate-900 font-mono">{{ $template->remote_template_id ?: 'Pending' }}</dd>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4 border-b border-slate-100 pb-4">
                        <dt class="font-medium text-slate-500">Quality Rating</dt>
                        <dd class="col-span-2">
                            @if(strtolower($template->quality_rating) === 'high' || strtolower($template->quality_rating) === 'green')
                                <span class="text-green-600 font-semibold">{{ $template->quality_rating }}</span>
                            @elseif(strtolower($template->quality_rating) === 'medium' || strtolower($template->quality_rating) === 'yellow')
                                <span class="text-yellow-600 font-semibold">{{ $template->quality_rating }}</span>
                            @elseif(strtolower($template->quality_rating) === 'low' || strtolower($template->quality_rating) === 'red')
                                <span class="text-red-600 font-semibold">{{ $template->quality_rating }}</span>
                            @else
                                <span class="text-slate-500">{{ $template->quality_rating ?: 'N/A' }}</span>
                            @endif
                        </dd>
                    </div>

                    <div class="grid grid-cols-3 gap-4 border-b border-slate-100 pb-4">
                        <dt class="font-medium text-slate-500">Submitted At</dt>
                        <dd class="col-span-2 text-slate-900">{{ $template->submitted_at ? $template->submitted_at->format('M j, Y g:i A') : 'N/A' }}</dd>
                    </div>

                    <div class="grid grid-cols-3 gap-4 pb-2">
                        <dt class="font-medium text-slate-500">Last Synced</dt>
                        <dd class="col-span-2 text-slate-900">{{ $template->last_synced_at ? $template->last_synced_at->format('M j, Y g:i A') : 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
            
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                 <h3 class="text-lg font-bold text-slate-900 mb-4">Payload Elements</h3>
                 <div class="space-y-4">
                     <div>
                         <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Header</span>
                         <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-sm text-slate-700">
                             @if($template->header_type === 'none')
                                 <span class="italic text-slate-400">None</span>
                             @elseif($template->header_type === 'text')
                                 {{ $template->header_text }}
                             @else
                                 [{{ strtoupper($template->header_type) }} MEDIA EXPECTED]
                             @endif
                         </div>
                     </div>
                     <div>
                         <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Body Variables</span>
                         <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-sm text-slate-700">
                             @php $varCount = preg_match_all('/\{\{\d+\}\}/', $template->body_text); @endphp
                             @if($varCount > 0)
                                 Found {{ $varCount }} variables required during sending.
                             @else
                                 No variables required.
                             @endif
                         </div>
                     </div>
                 </div>
            </div>
        </div>

        {{-- Right: Preview Phone --}}
        <div class="border-4 border-slate-900/10 rounded-[2.5rem] bg-slate-50 relative h-auto shadow-2xl p-4 overflow-hidden max-w-sm mx-auto w-full">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-6 bg-slate-900/10 rounded-b-2xl"></div>
            
            <div class="bg-[#efeae2] rounded-3xl h-full shadow-inner overflow-hidden flex flex-col pt-12 min-h-[500px]">
                <div class="bg-[#008069] text-white p-3 flex items-center gap-3 shadow-sm z-10 shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-slate-200"></div>
                        <div class="font-semibold text-sm">Preview</div>
                    </div>
                </div>

                <div class="flex-1 p-4 overflow-y-auto space-y-4 pb-6 no-scrollbar">
                    {{-- User message --}}
                    <div class="bg-[#d9fdd3] p-3 rounded-lg rounded-tr-none shadow-sm max-w-[85%] ml-auto text-sm text-slate-800">
                        Triggered Business Workflow
                        <div class="text-[10px] text-slate-500 mt-1 text-right">Just now</div>
                    </div>

                    {{-- Template View --}}
                    <div class="bg-white p-2 rounded-lg rounded-tl-none shadow-sm max-w-[90%] space-y-2">
                        @if($template->header_type && $template->header_type !== 'none')
                            <div class="font-bold text-slate-900 text-sm px-1 pt-1">
                                @if($template->header_type === 'text')
                                    {{ $template->header_text }}
                                @else
                                    <div class="w-full h-32 bg-slate-200 rounded flex items-center justify-center text-slate-400">
                                        [{{ strtoupper($template->header_type) }}]
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        <div class="px-1 text-sm text-slate-800 leading-relaxed whitespace-pre-wrap">{{ $template->body_text }}</div>
                        
                        @if($template->footer_text)
                            <div class="px-1 text-[11px] text-slate-500 mt-1">{{ $template->footer_text }}</div>
                        @endif

                        @if($template->button_count > 0)
                            <div class="space-y-1.5 pt-2 mt-2 border-t border-slate-100">
                                @foreach($template->buttons as $btn)
                                    <div class="w-full text-center text-[#00a884] font-semibold text-sm py-2">
                                        @if($btn->type === 'url')
                                            <svg class="inline-block h-4 w-4 mr-1 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        @elseif($btn->type === 'phone_number')
                                            <svg class="inline-block h-4 w-4 mr-1 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                        @elseif($btn->type === 'quick_reply')
                                            <svg class="inline-block h-4 w-4 mr-1 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                        @endif
                                        {{ $btn->text }}
                                    </div>
                                    @if(!$loop->last)
                                        <hr class="border-slate-100">
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
