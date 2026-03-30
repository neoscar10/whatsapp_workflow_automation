<div class="h-[calc(100vh-64px)] flex flex-col bg-[#061122] overflow-hidden select-none" 
    wire:poll.1s="runNextStep"
    x-data="{ 
        canvasScale: 1, 
        canvasX: 0, 
        canvasY: 0,
        isDragging: false,
        draggingNode: null,
        activeTab: @entangle('tab'),
        sessionStatus: @entangle('session.status'),
        currentNodeId: @entangle('session.current_node_id')
    }"
>
    <!-- TOP TOOLBAR -->
    <div class="h-16 flex-shrink-0 bg-[#0a1630] border-b border-white/5 px-6 flex items-center justify-between z-50 shadow-2xl">
        <div class="flex items-center gap-6">
            <a href="{{ route('automations.edit', $automation->id) }}" class="flex items-center gap-2 group text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                <span class="text-[10px] font-black uppercase tracking-widest group-hover:translate-x-1 transition-transform">Back to Builder</span>
            </a>
            <div class="h-8 w-px bg-white/5"></div>
            <div class="flex flex-col">
                <h1 class="text-sm font-black text-white tracking-tight flex items-center gap-3 decoration-primary decoration-2 underline-offset-4">
                    {{ $automation->name }}
                    <span class="px-2 py-0.5 rounded-full bg-primary/10 border border-primary/20 text-primary text-[9px] font-black uppercase tracking-widest">Simulation Mode</span>
                </h1>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">Safe Trial Workspace • No real messages will be sent</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            @if($session && $session->status === 'running')
                <button wire:click="pauseSimulation" class="h-10 px-5 bg-amber-500/10 border border-amber-500/20 rounded-xl flex items-center gap-2.5 text-amber-500 hover:bg-amber-500 hover:text-white transition-all group active:scale-95 shadow-lg shadow-amber-500/5">
                    <span class="material-symbols-outlined text-xl group-hover:scale-110 transition-transform">pause_circle</span>
                    <span class="text-[10px] font-black uppercase tracking-widest">Pause Simulation</span>
                </button>
            @elseif($session && $session->status === 'paused')
                <button wire:click="resumeSimulation" class="h-10 px-5 bg-green-500/10 border border-green-500/20 rounded-xl flex items-center gap-2.5 text-green-500 hover:bg-green-500 hover:text-white transition-all group active:scale-95 shadow-lg shadow-green-500/5">
                    <span class="material-symbols-outlined text-xl group-hover:scale-110 transition-transform">play_arrow</span>
                    <span class="text-[10px] font-black uppercase tracking-widest">Resume Simulation</span>
                </button>
            @else
                <button wire:click="startSimulation" class="h-10 px-5 bg-primary border border-primary/20 rounded-xl flex items-center gap-2.5 text-white hover:bg-primary/80 transition-all group active:scale-95 shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-xl group-hover:rotate-12 transition-transform">rocket_launch</span>
                    <span class="text-[10px] font-black uppercase tracking-widest">Start Simulation</span>
                </button>
            @endif

            @if($session && in_array($session->status, ['running', 'paused']))
                <button wire:click="stopSimulation" class="h-10 w-10 flex items-center justify-center bg-red-500/10 border border-red-500/20 rounded-xl text-red-500 hover:bg-red-500 hover:text-white transition-all active:scale-90 shadow-lg shadow-red-500/5">
                    <span class="material-symbols-outlined text-xl">stop</span>
                </button>
            @endif
        </div>
    </div>

    <!-- MAIN COLS -->
    <div class="flex-1 flex min-h-0 overflow-hidden">
        
        <!-- COL 1: TOOLS SIDEBAR -->
        <div class="w-72 flex-shrink-0 bg-[#0a1630] border-r border-white/5 flex flex-col overflow-hidden shadow-2xl z-40">
            <div class="flex-1 overflow-y-auto no-scrollbar p-6 space-y-8">
                
                <!-- RUN CONTROLS & STATUS -->
                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] px-1">Session Summary</label>
                    <div class="p-5 rounded-3xl bg-white/[0.03] border border-white/5 space-y-4 relative overflow-hidden group shadow-xl">
                        <div class="absolute -right-8 -top-8 w-24 h-24 bg-primary/5 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-1000"></div>
                        <div class="flex items-center justify-between relative z-10">
                            <span class="text-[10px] font-black text-slate-400 uppercase">Status</span>
                            <span class="px-2.5 py-1 rounded-lg bg-{{ $session ? ($session->status === 'running' ? 'green' : ($session->status === 'paused' ? 'amber' : 'slate')) : 'slate' }}-500/10 border border-{{ $session ? ($session->status === 'running' ? 'green' : ($session->status === 'paused' ? 'amber' : 'slate')) : 'slate' }}-500/20 text-{{ $session ? ($session->status === 'running' ? 'green' : ($session->status === 'paused' ? 'amber' : 'slate')) : 'slate' }}-500 text-[9px] font-black uppercase tracking-widest">
                                {{ $session->status ?? 'Ready' }}
                            </span>
                        </div>
                        <div class="space-y-1.5 relative z-10">
                            <p class="text-[9px] font-black text-slate-500 uppercase">Workflow Context</p>
                            <p class="text-[11px] font-bold text-white tracking-tight truncate">{{ $automation->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- TABS CONTENT -->
                <nav class="flex gap-1 bg-[#101d39]/40 border border-white/5 rounded-2xl p-1 shadow-inner">
                    @foreach(['execution' => 'bolt', 'variables' => 'data_object', 'breakpoints' => 'ads_click'] as $id => $icon)
                        <button 
                            wire:click="setTab('{{ $id }}')"
                            class="flex-1 h-10 rounded-xl flex items-center justify-center transition-all group"
                            :class="activeTab === '{{ $id }}' ? 'bg-primary text-white shadow-lg' : 'text-slate-500 hover:text-slate-300'"
                        >
                            <span class="material-symbols-outlined text-lg" :class="activeTab === '{{ $id }}' ? 'text-white' : 'text-slate-500 group-hover:text-slate-400'">{{ $icon }}</span>
                        </button>
                    @endforeach
                </nav>

                @if($tab === 'execution')
                    <div class="space-y-6 animate-in fade-in slide-in-from-left-4 duration-300">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] px-1">Simulation History</label>
                            @foreach($history as $hist)
                                <div class="p-4 bg-white/[0.02] border border-white/5 rounded-2xl hover:bg-white/[0.04] transition-all group cursor-pointer border-l-2 {{ $hist->id === ($session->id ?? 0) ? 'border-l-primary' : 'border-l-transparent' }}">
                                    <div class="flex justify-between items-start mb-1">
                                        <p class="text-[11px] font-black text-slate-300 group-hover:text-white transition-colors">Session #{{ $hist->id }}</p>
                                        <span class="text-[8px] font-bold text-slate-600 uppercase">{{ $hist->created_at->diffForHumans() }}</span>
                                    </div>
                                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-tighter">{{ count($hist->steps) }} Steps • {{ $hist->status }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($tab === 'variables')
                    <div class="space-y-6 animate-in fade-in slide-in-from-left-4 duration-300">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] px-1">Runtime Variables</label>
                            @if($session && !empty($session->context))
                                <div class="space-y-2">
                                    @foreach($session->context as $key => $value)
                                        <div class="p-3.5 bg-[#101d39]/40 border border-white/5 rounded-2xl group hover:border-primary/20 transition-all">
                                            <div class="flex items-center gap-3">
                                                <span class="material-symbols-outlined text-sm text-slate-500 group-hover:text-primary transition-colors">variable_insert</span>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="text-[10px] font-black text-slate-300 truncate">{{ $key }}</span>
                                                    <span class="text-[9px] font-bold text-slate-600 truncate">{{ is_array($value) ? 'Object' : $value }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-[10px] font-bold text-slate-600 uppercase text-center py-8">No variables populated yet.</p>
                            @endif
                        </div>
                    </div>
                @elseif($tab === 'breakpoints')
                    <div class="space-y-6 animate-in fade-in slide-in-from-left-4 duration-300">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] px-1">Active Breakpoints</label>
                            @forelse($nodes->whereIn('id', $breakpoints) as $bpNode)
                                <div class="flex items-center justify-between p-4 bg-red-500/5 border border-red-500/10 rounded-2xl group">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-sm text-red-500">ads_click</span>
                                        <span class="text-[11px] font-black text-slate-300">{{ $bpNode->config['label'] ?? $bpNode->subtype }}</span>
                                    </div>
                                    <button wire:click="toggleBreakpoint('{{ $bpNode->id }}')" class="text-slate-600 hover:text-red-500 transition-colors">
                                        <span class="material-symbols-outlined text-sm text-red-500">close</span>
                                    </button>
                                </div>
                            @empty
                                <p class="text-[10px] font-bold text-slate-600 uppercase text-center py-8">No breakpoints set.</p>
                            @endforelse
                            <p class="text-[9px] font-bold text-slate-600 uppercase tracking-tight text-center px-4 leading-relaxed">
                                Toggle breakpoints in the Canvas by clicking the debug icon on any node.
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- BOT SIDEBAR ACTION -->
            <div class="p-6 border-t border-white/5 space-y-4">
                <button wire:click="setTab('triggers')" class="w-full py-4 bg-white/5 border border-white/10 rounded-2xl text-[10px] font-black text-slate-400 uppercase tracking-widest hover:bg-primary/10 hover:border-primary/30 hover:text-primary transition-all flex items-center justify-center gap-2 group shadow-inner">
                    <span class="material-symbols-outlined text-lg group-hover:rotate-12 transition-transform">input</span>
                    Simulation Payload
                </button>
            </div>
        </div>

        <!-- COL 2: CENTER CANVAS -->
        <div class="flex-1 relative bg-[radial-gradient(#ffffff0a_1px,transparent_1px)] bg-[length:32px_32px] overflow-hidden" 
             @mousemove="if(isDragging) { canvasX += $event.movementX; canvasY += $event.movementY }"
             @mousedown="if($event.target.closest('.canvas-bg')) isDragging = true"
             @mouseup="isDragging = false"
             @mouseleave="isDragging = false"
             @wheel.prevent="canvasScale = Math.min(Math.max(0.2, canvasScale - $event.deltaY * 0.001), 2)"
        >
            <div class="canvas-bg absolute inset-0 cursor-grab active:cursor-grabbing"></div>

            <div class="absolute inset-0 pointer-events-none transition-transform duration-75" 
                 :style="`transform: translate(${canvasX}px, ${canvasY}px) scale(${canvasScale})`"
                 style="transform-origin: center center;"
            >
                <!-- SVG CONNECTIONS -->
                <svg class="absolute inset-0 w-full h-full overflow-visible pointer-events-none">
                    <defs>
                        <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="9" refY="3.5" orientation="auto">
                            <polygon points="0 0, 10 3.5, 0 7" fill="#3b82f6" />
                        </marker>
                    </defs>
                    @foreach($connections as $connection)
                        @php
                            $source = $nodes->firstWhere('id', $connection->source_node_id);
                            $target = $nodes->firstWhere('id', $connection->target_node_id);
                        @endphp
                        @if($source && $target)
                            <path 
                                d="M {{ $source->position_x + 220 }} {{ $source->position_y + 40 }} C {{ $source->position_x + 300 }} {{ $source->position_y + 40 }}, {{ $target->position_x - 80 }} {{ $target->position_y + 40 }}, {{ $target->position_x }} {{ $target->position_y + 40 }}" 
                                stroke="#1e293b" 
                                stroke-width="2.5" 
                                fill="none"
                                class="transition-all duration-500"
                                :class="sessionStatus === 'running' ? 'opacity-20' : 'opacity-100'"
                            />
                        @endif
                    @endforeach
                </svg>

                <!-- NODES -->
                @php
                    $sessionStepsNodeIds = $session ? $session->steps->pluck('node_id')->toArray() : [];
                @endphp
                @foreach($nodes as $node)
                    @php
                        $isCurrent = $session && $session->current_node_id == $node->id;
                        $hasExecuted = in_array($node->id, $sessionStepsNodeIds);
                        $step = $session ? $session->steps->firstWhere('node_id', $node->id) : null;
                    @endphp
                    <div 
                        class="absolute w-[220px] pointer-events-auto rounded-3xl border-2 transition-all duration-500 shadow-2xl"
                        :class="{
                            'border-primary bg-[#0f1d3a] ring-4 ring-primary/20 scale-105 z-30': {{ $isCurrent ? 'true' : 'false' }},
                            'border-green-500/50 bg-[#0a1630]/90 z-20': {{ $hasExecuted && !$isCurrent ? 'true' : 'false' }},
                            'border-white/5 bg-[#0a1630]/60 z-10 opacity-60': {{ !$hasExecuted && !$isCurrent ? 'true' : 'false' }} 
                        }"
                        style="left: {{ $node->position_x }}px; top: {{ $node->position_y }}px;"
                    >
                        <!-- Node Content -->
                        <div class="p-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-white/5 flex items-center justify-center border border-white/10 shadow-lg">
                                <span class="material-symbols-outlined text-primary text-xl">
                                    {{ $node->type === 'trigger' ? 'rocket_launch' : ($node->type === 'action' ? 'bolt' : ($node->type === 'condition' ? 'rule' : 'hourglass_empty')) }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-500 mb-0.5 truncate">{{ $node->type }}</p>
                                <p class="text-xs font-black text-white truncate tracking-tight">{{ $node->config['label'] ?? $node->subtype }}</p>
                            </div>
                            
                            <button wire:click="toggleBreakpoint('{{ $node->id }}')" class="p-1.5 rounded-lg hover:bg-white/5 transition-colors group">
                                <span class="material-symbols-outlined text-sm" :class="{{ in_array($node->id, $breakpoints) ? 'true' : 'false' }} ? 'text-red-500 animate-pulse' : 'text-slate-600 group-hover:text-slate-400'">
                                    ads_click
                                </span>
                            </button>
                        </div>

                        <!-- Execution Overlay -->
                        @if($isCurrent)
                            <div class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-primary border-4 border-[#061122] flex items-center justify-center shadow-lg animate-bounce">
                                <span class="material-symbols-outlined text-[14px] text-white">play_arrow</span>
                            </div>
                        @elseif($hasExecuted)
                           <div class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-green-500 border-4 border-[#061122] flex items-center justify-center shadow-lg">
                                <span class="material-symbols-outlined text-[14px] text-white">check</span>
                            </div>
                        @endif

                        @if($isCurrent)
                            <div class="h-1 bg-white/5 overflow-hidden rounded-b-3xl">
                                <div class="h-full bg-primary w-full animate-progress-indefinite"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- COL 3: LOGS & INSPECTOR -->
        <div class="w-80 flex-shrink-0 bg-[#0a1630] border-l border-white/5 flex flex-col overflow-hidden shadow-2xl z-40">
            <div class="flex-1 flex flex-col min-h-0">
                <div class="p-6 border-b border-white/5">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Execution Logs</label>
                </div>
                
                <div class="flex-1 overflow-y-auto no-scrollbar p-1">
                    @forelse($steps as $step)
                        <div 
                            wire:click="selectStep('{{ $step->id }}')"
                            class="p-4 mx-2 my-1 rounded-2xl cursor-pointer transition-all border group"
                            :class="activeStepId == '{{ $step->id }}' ? 'bg-primary/10 border-primary/30' : 'bg-transparent border-transparent hover:bg-white/[0.03]'"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full shadow-lg {{ $step->status === 'success' ? 'bg-green-500 shadow-green-500/20' : ($step->status === 'running' ? 'bg-primary animate-pulse' : 'bg-red-500') }}"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-[11px] font-black text-slate-200 uppercase tracking-tight group-hover:text-white transition-colors truncate">
                                            {{ $node->find($step->node_id)?->config['label'] ?? $step->node_type }}
                                        </p>
                                        <span class="text-[8px] font-bold text-slate-600 uppercase shrink-0">{{ $step->created_at->format('H:i:s') }}</span>
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-500 truncate mt-0.5">{{ $step->log_message }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center p-8 text-center opacity-40">
                            <span class="material-symbols-outlined text-4xl mb-3">manage_search</span>
                            <p class="text-[10px] font-black uppercase tracking-widest leading-relaxed">No logs generated yet.<br>Start the simulation to begin.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- STEP INSPECTOR (DYNAMIC BOTTOM PANEL) -->
            @if($activeStep)
                <div class="h-1/2 flex-shrink-0 bg-[#0a1630] border-t border-white/10 flex flex-col overflow-hidden shadow-2xl animate-in slide-in-from-bottom duration-300">
                    <div class="p-4 bg-white/[0.03] border-b border-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-primary">data_object</span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-white">Step Inspector</span>
                        </div>
                        <button wire:click="$set('activeStepId', null)" class="text-slate-500 hover:text-white transition-colors">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-5 space-y-6 no-scrollbar">
                        <div class="space-y-3">
                            <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest px-1">Input Context</label>
                            <pre class="bg-[#061122] border border-white/5 rounded-2xl p-4 text-[10px] font-mono text-primary/80 overflow-x-auto shadow-inner">{{ json_encode($activeStep->input_snapshot, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        <div class="space-y-3">
                            <label class="text-[9px] font-black text-slate-600 uppercase tracking-widest px-1">Output Result</label>
                            <pre class="bg-[#061122] border border-white/5 rounded-2xl p-4 text-[10px] font-mono text-green-500/80 overflow-x-auto shadow-inner">{{ json_encode($activeStep->output_snapshot, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- TRIGGER PAYLOAD OVERLAY -->
    @if($tab === 'triggers')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-[#061122]/90 backdrop-blur-xl animate-in fade-in duration-300" 
             @click.self="activeTab = 'execution'">
            <div class="w-full max-w-2xl bg-[#0a1630] border border-white/10 rounded-[40px] shadow-2xl overflow-hidden flex flex-col max-h-[80vh] animate-in zoom-in-95 duration-300">
                <div class="p-8 border-b border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/20">
                            <span class="material-symbols-outlined text-primary text-2xl">input</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-white tracking-tight">Simulation Payload</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">Define the trigger data to start the simulation</p>
                        </div>
                    </div>
                    <button @click="activeTab = 'execution'" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/5 transition-colors group">
                        <span class="material-symbols-outlined text-slate-500 group-hover:text-white">close</span>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-8 no-scrollbar">
                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] px-1">Initial JSON Payload</label>
                        <textarea 
                            wire:model.defer="initialPayload"
                            class="w-full h-96 bg-[#061122] border border-white/10 rounded-3xl p-6 text-xs font-mono text-primary/80 focus:ring-2 focus:ring-primary shadow-inner resize-none scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent"
                            placeholder="{ ... }"
                        ></textarea>
                        <div class="p-5 rounded-2xl bg-primary/5 border border-primary/10 flex gap-4">
                            <span class="material-symbols-outlined text-primary text-lg shrink-0">info</span>
                            <p class="text-[10px] font-bold text-slate-400 leading-relaxed uppercase tracking-tight">
                                These variables will be injected into the simulation entry point. You can reference them using <span class="text-white">@{{ trigger.field }}</span> in your node configurations.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-8 border-t border-white/5 bg-[#0d1b38]/40 flex justify-end gap-3">
                    <button @click="activeTab = 'execution'" class="px-8 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-white transition-all">Cancel</button>
                    <button @click="activeTab = 'execution'" class="px-8 py-3.5 bg-primary text-white rounded-2xl text-[10px] font-black text-primary uppercase tracking-widest hover:bg-primary/80 transition-all shadow-lg shadow-primary/20">Save Payload</button>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes progress-indefinite {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .animate-progress-indefinite {
            animation: progress-indefinite 1.5s infinite linear;
        }
        [x-cloak] { display: none !important; }
    </style>
</div>
