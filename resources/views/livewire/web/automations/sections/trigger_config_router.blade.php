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
        <div class="p-4 bg-primary/5 border border-primary/20 rounded-2xl">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary text-xl">info</span>
                <div class="space-y-1">
                    <p class="text-xs font-bold text-white uppercase tracking-wider">Event Subscription</p>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        This flow will automatically fire when the system detects a <b>{{ str_replace('_', ' ', $defKey) }}</b> event.
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
