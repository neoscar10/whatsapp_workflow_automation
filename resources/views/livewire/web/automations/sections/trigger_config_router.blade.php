@php
    $type = $nodeConfig['trigger_type'] ?? $nodeConfig['trigger_category'] ?? $activeNode->subtype ?? '';
    // Unify webhook naming for routing
    if ($type === 'webhook') $type = 'webhook_api';
    $defKey = $nodeConfig['trigger_definition_key'] ?? '';
@endphp

<div class="space-y-6">
    @if($type === 'webhook_api' || $type === 'webhook')
        @include('livewire.web.automations.sections.trigger_webhook_url')
        @include('livewire.web.automations.sections.trigger_api_key')
        @include('livewire.web.automations.sections.trigger_webhook_instructions')
        @include('livewire.web.automations.sections.trigger_webhook_output_variables')
    
    @elseif($type === 'time_based')
        @include('livewire.web.automations.sections.trigger_time_based_form')
    
    @elseif($type === 'conditional')
        @include('livewire.web.automations.sections.trigger_conditional_rule_builder')

    @elseif($type === 'event_based')
        <div class="space-y-4">
            <div class="p-6 bg-emerald-500/5 border border-emerald-500/20 rounded-[2rem] relative overflow-hidden group">
                <div class="absolute -right-8 -top-8 w-24 h-24 bg-emerald-500/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                
                <div class="flex items-start gap-4 relative z-10">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center border border-emerald-500/20">
                        <span class="material-symbols-outlined text-emerald-500 text-xl">notifications_active</span>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-black text-white uppercase tracking-[0.2em]">System Event Subscription</p>
                        <p class="text-[11px] font-bold text-emerald-500/80 uppercase tracking-tight">Active Listener</p>
                    </div>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-white/[0.03] border border-white/5">
                    <p class="text-[11px] font-bold text-slate-400 leading-relaxed uppercase tracking-tight">
                        This trigger fires automatically when a customer sends a WhatsApp message. No additional configuration is required to receive standard inbound events.
                    </p>
                </div>
            </div>

            <div class="p-6 bg-[#0c1833] border border-white/10 rounded-[2rem] space-y-4">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Available Payload Variables</label>
                
                <div class="grid grid-cols-1 gap-2">
                    @foreach(['message_body', 'phone_number', 'sender_name', 'received_at'] as $var)
                        <div class="flex items-center justify-between p-3 bg-white/[0.02] border border-white/5 rounded-xl group hover:bg-white/[0.04] transition-all">
                            <code class="text-[10px] font-black text-primary tracking-tight group-hover:text-white transition-colors">trigger.{{ $var }}</code>
                            <span class="text-[9px] font-bold text-slate-600 uppercase tracking-tighter">String</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 p-4 rounded-2xl bg-primary/5 border border-primary/10">
                    <p class="text-[9px] font-bold text-slate-500 leading-relaxed uppercase tracking-tight">
                        Use these variables in downstream condition nodes or message templates using the 
                        <span class="text-primary italic">@{{ trigger.field }}</span> syntax.
                    </p>
                </div>
            </div>
        </div>
        <!-- Add event filters if needed here -->

    @elseif($type === 'behavior_based')
        <div class="p-4 bg-amber-500/5 border border-amber-500/20 rounded-2xl">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-amber-500 text-xl">monitoring</span>
                <div class="space-y-1">
                    <p class="text-xs font-bold text-white uppercase tracking-wider">Behavior Monitoring</p>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Our behavioral engine will scan for <b>{{ str_replace('_', ' ', $defKey) }}</b> patterns and trigger this flow.
                    </p>
                </div>
            </div>
        </div>
        <!-- Add behavioral parameters/thresholds here -->
    
    @else
        <div class="py-10 text-center space-y-3 opacity-50">
            <span class="material-symbols-outlined text-4xl text-slate-600">tune</span>
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Select a definition to configure</p>
        </div>
    @endif
</div>
