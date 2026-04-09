<div class="flex h-[calc(100vh-64px)] flex-col bg-[#061122] text-slate-200 overflow-hidden" 
     x-data="{ 
        zoom: @entangle('canvasMeta.zoom'),
        panX: @entangle('canvasMeta.pan_x'),
        panY: @entangle('canvasMeta.pan_y'),
        selectedNodeId: @entangle('selectedNodeId'),
        draggingNodeId: null,
        offsetX: 0,
        offsetY: 0,
        
        panModeActive: false,
        isPanning: false,
        panStartX: 0,
        panStartY: 0,
        startPanX: 0,
        startPanY: 0,

        togglePanMode() {
            this.panModeActive = !this.panModeActive;
            if (!this.panModeActive) this.isPanning = false;
        },

        startPanning(e) {
            // 0 = left, 1 = middle
            if (!this.panModeActive && e.button !== 1) return;
            
            // Prevent browser autoscroll on middle click
            if (e.button === 1) e.preventDefault();
            
            this.isPanning = true;
            this.panStartX = e.clientX;
            this.panStartY = e.clientY;
            this.startPanX = this.panX;
            this.startPanY = this.panY;
        },

        onPan(e) {
            if (!this.isPanning) return;
            const scale = this.zoom / 100;
            const dx = (e.clientX - this.panStartX) / scale;
            const dy = (e.clientY - this.panStartY) / scale;
            this.panX = Math.round(this.startPanX + dx);
            this.panY = Math.round(this.startPanY + dy);
        },

        stopPanning() {
            this.isPanning = false;
        },
        
        startDrag(e, nodeId, x, y) {
             if (this.panModeActive) return;
             this.draggingNodeId = nodeId;
             this.selectedNodeId = nodeId;
             
             const scale = this.zoom / 100;
             this.offsetX = (e.clientX / scale) - x;
             this.offsetY = (e.clientY / scale) - y;
         },
        
        onDrag(e) {
            if (!this.draggingNodeId) return;
            const scale = this.zoom / 100;
            const newX = Math.round((e.clientX / scale) - this.offsetX);
            const newY = Math.round((e.clientY / scale) - this.offsetY);
            
            // Local update for smoothness
            const el = document.getElementById('node-' + this.draggingNodeId);
            if (el) {
                el.style.left = newX + 'px';
                el.style.top = newY + 'px';
                window.dispatchEvent(new CustomEvent('node-moved', { detail: { id: this.draggingNodeId, x: newX, y: newY } }));
            }
        },
        
        stopDrag(e) {
            if (!this.draggingNodeId) return;
            const scale = this.zoom / 100;
            const newX = Math.round((e.clientX / scale) - this.offsetX);
            const newY = Math.round((e.clientY / scale) - this.offsetY);
            
            @this.updateNodePosition(this.draggingNodeId, newX, newY);
            this.draggingNodeId = null;
        },

        // Connection logic
        connectingFromNodeId: null,
        connectingFromHandle: null,
        connectingConditionKey: null,
        mouseX: 0,
        mouseY: 0,
        
        startConnecting(e, nodeId, handle, conditionKey = null) {
            e.stopPropagation();
            this.connectingFromNodeId = nodeId;
            this.connectingFromHandle = handle;
            this.connectingConditionKey = conditionKey;
            this.updateMousePos(e);
        },
        
        updateMousePos(e) {
             if (!this.connectingFromNodeId) return;
             const canvas = this.$refs.canvas.getBoundingClientRect();
             this.mouseX = ((e.clientX - canvas.left) / (this.zoom / 100)) - this.panX;
             this.mouseY = ((e.clientY - canvas.top) / (this.zoom / 100)) - this.panY;
         },
        
        completeConnection(targetNodeId, targetHandle) {
            if (!this.connectingFromNodeId || this.connectingFromNodeId === targetNodeId) {
                this.connectingFromNodeId = null;
                return;
            }
            
            @this.connectNodes(this.connectingFromNodeId, targetNodeId, this.connectingFromHandle, targetHandle, this.connectingConditionKey);
            this.connectingFromNodeId = null;
            this.connectingConditionKey = null;
        },
        
        cancelConnection() {
            this.connectingFromNodeId = null;
        },

        getAnchorPoint(nodeId, handle, conditionKey = null) {
            const el = document.getElementById('node-' + nodeId);
            if (!el) return { x: 0, y: 0 };
            
            const x = parseInt(el.style.left);
            const y = parseInt(el.style.top);
            const width = el.offsetWidth;
            const height = el.offsetHeight;
            
            if (handle === 'bottom') {
                if (conditionKey === 'yes') return { x: x + width / 4, y: y + height };
                if (conditionKey === 'no') return { x: x + (width * 3) / 4, y: y + height };
                return { x: x + width / 2, y: y + height };
            } else if (handle === 'top') {
                return { x: x + width / 2, y: y };
            }
            
            return { x: x + width / 2, y: y + height / 2 };
        },

        getPath(x1, y1, x2, y2) {
            const dy = Math.abs(y2 - y1);
            const cp1y = y1 + dy * 0.5;
            const cp2y = y2 - dy * 0.5;
            return `M ${x1} ${y1} C ${x1} ${cp1y} ${x2} ${cp2y} ${x2} ${y2}`;
        },

        init() {
            // Force a recalculation of all connectors once the DOM is stable
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('node-moved'));
            }, 100);
        }
     }"
     @mousemove="onDrag($event); onPan($event); updateMousePos($event)"
     @mouseup="stopDrag($event); stopPanning(); cancelConnection()"
     @keydown.space.window="if (!panModeActive && !['INPUT', 'TEXTAREA'].includes($event.target.tagName)) { panModeActive = true; $event.preventDefault(); }"
     @keyup.space.window="if (panModeActive && !['INPUT', 'TEXTAREA'].includes($event.target.tagName)) panModeActive = false"
>
    {{-- Builder Toolbar --}}
    <div class="flex items-center justify-between gap-4 border-b border-white/10 bg-[#0a1630] px-6 py-3 z-50 shadow-[0_10px_40px_rgba(0,0,0,0.2)]">
        <div class="flex min-w-0 items-center gap-4">
            <a href="{{ route('automations.index') }}" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-white/5 hover:text-white transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Automation Builder</p>
                <div class="mt-0.5 flex items-center gap-3">
                    <input
                        type="text"
                        wire:model.lazy="workflowName"
                        class="min-w-[260px] border-0 bg-transparent p-0 text-lg font-black text-white focus:ring-0 placeholder:text-slate-600"
                        placeholder="Untitled workflow"
                    />
                    <span class="rounded-full bg-white/5 border border-white/10 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-400">Draft</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="mr-2 flex items-center gap-1 rounded-xl border border-white/10 bg-[#0d1a33] p-1">
                <button type="button" class="rounded-lg p-1.5 text-slate-500 hover:bg-white/5 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-xl">undo</span>
                </button>
                <button type="button" class="rounded-lg p-1.5 text-slate-500 hover:bg-white/5 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-xl">redo</span>
                </button>
                <div class="mx-1 h-4 w-px bg-white/5"></div>
                <button @click="zoom = Math.max(50, zoom - 10)" class="rounded-lg p-1.5 text-slate-500 hover:bg-white/5 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-xl">zoom_out</span>
                </button>
                <span class="w-12 text-center text-xs font-bold text-slate-400" x-text="zoom + '%'"></span>
                <button @click="zoom = Math.min(150, zoom + 10)" class="rounded-lg p-1.5 text-slate-500 hover:bg-white/5 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-xl">zoom_in</span>
                </button>
                <div class="mx-1 h-4 w-px bg-white/5"></div>
                <button @click="togglePanMode()" 
                        class="rounded-lg p-1.5 transition-colors"
                        :class="panModeActive ? 'bg-primary text-white' : 'text-slate-500 hover:bg-white/5 hover:text-white'"
                        title="Hand Tool (H / Space)">
                    <span class="material-symbols-outlined text-xl">pan_tool</span>
                </button>
            </div>

            @if($automation->id)
                <a href="{{ route('automations.simulate', $automation->id) }}" class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold text-slate-400 hover:bg-white/5 hover:text-white transition-all group">
                    <span class="material-symbols-outlined text-lg group-hover:scale-110 transition-transform">play_circle</span>
                    Simulate
                </a>
            @endif

            <button wire:click="saveDraft" wire:loading.attr="disabled" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-400 hover:bg-white/5 hover:text-white transition-all disabled:opacity-50">
                Save Draft
            </button>

            <button wire:click="publish" wire:loading.attr="disabled" class="rounded-xl bg-primary px-6 py-2.5 text-sm font-bold text-white shadow-xl shadow-primary/40 hover:bg-blue-600 transition-all disabled:opacity-50">
                Publish Flow
            </button>
        </div>
    </div>

    {{-- Builder Workspace --}}
    <div class="flex flex-1 overflow-hidden">
        {{-- Left Palette (Node Library) --}}
        <aside class="w-64 border-r border-white/10 bg-[#081427] flex flex-col z-40 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-1">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">hub</span>
                    <span class="text-xs font-black uppercase tracking-widest text-slate-500">Node Library</span>
                </div>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">Drag and drop components</p>
            </div>

            <div class="flex-1 overflow-y-auto pt-2 scrollbar-hide">
                <div class="space-y-6 pb-12">
                    {{-- 1. Triggers Group --}}
                    <div>
                        <div class="px-6 py-2 mb-1 flex items-center justify-between group cursor-default">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-600 group-hover:text-primary transition-colors">Triggers</label>
                            <span class="material-symbols-outlined text-xs text-slate-700">bolt</span>
                        </div>
                        <div class="space-y-0.5">
                            <button wire:click="addNode('trigger', 'webhook_api')" class="w-full px-6 py-3 flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/[0.03] transition-all group border-r-4 border-transparent hover:border-emerald-500">
                                <span class="material-symbols-outlined text-emerald-500/50 group-hover:text-emerald-500 group-hover:scale-110 transition-all text-xl">sensors</span>
                                <span class="text-[12px] font-black tracking-tight uppercase">Webhook / API</span>
                            </button>
                            <button wire:click="addNode('trigger', 'time_based')" class="w-full px-6 py-3 flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/[0.03] transition-all group border-r-4 border-transparent hover:border-emerald-500">
                                <span class="material-symbols-outlined text-emerald-500/50 group-hover:text-emerald-500 group-hover:scale-110 transition-all text-xl">schedule</span>
                                <span class="text-[12px] font-black tracking-tight uppercase">Schedule</span>
                            </button>
                            <button wire:click="addNode('trigger', 'conditional')" class="w-full px-6 py-3 flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/[0.03] transition-all group border-r-4 border-transparent hover:border-emerald-500">
                                <span class="material-symbols-outlined text-emerald-500/50 group-hover:text-emerald-500 group-hover:scale-110 transition-all text-xl">rule</span>
                                <span class="text-[12px] font-black tracking-tight uppercase">Conditional</span>
                            </button>
                        </div>
                    </div>

                    {{-- 2. Actions Group --}}
                    <div>
                        <div class="px-6 py-2 mb-1 flex items-center justify-between group cursor-default">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-600 group-hover:text-primary transition-colors">Actions</label>
                            <span class="material-symbols-outlined text-xs text-slate-700">play_arrow</span>
                        </div>
                        <div class="space-y-0.5">
                            <button wire:click="addNode('action', 'whatsapp_message')" class="w-full px-6 py-3 flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/[0.03] transition-all group border-r-4 border-transparent hover:border-blue-600">
                                <span class="material-symbols-outlined text-blue-500/50 group-hover:text-blue-500 group-hover:scale-110 transition-all text-xl">chat</span>
                                <span class="text-[12px] font-black tracking-tight uppercase">WhatsApp Msg</span>
                            </button>
                            <button wire:click="addNode('action', 'call_api')" class="w-full px-6 py-3 flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/[0.03] transition-all group border-r-4 border-transparent hover:border-blue-600">
                                <span class="material-symbols-outlined text-blue-500/50 group-hover:text-blue-500 group-hover:scale-110 transition-all text-xl">api</span>
                                <span class="text-[12px] font-black tracking-tight uppercase">Call API</span>
                            </button>
                            <button wire:click="addNode('action', 'update_row')" class="w-full px-6 py-3 flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/[0.03] transition-all group border-r-4 border-transparent hover:border-blue-600">
                                <span class="material-symbols-outlined text-blue-500/50 group-hover:text-blue-500 group-hover:scale-110 transition-all text-xl">database</span>
                                <span class="text-[12px] font-black tracking-tight uppercase">Update Data</span>
                            </button>
                        </div>
                    </div>

                    {{-- 3. Logic Group --}}
                    <div>
                        <div class="px-6 py-2 mb-1 flex items-center justify-between group cursor-default">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-600 group-hover:text-amber-500 transition-colors">Logic</label>
                            <span class="material-symbols-outlined text-xs text-slate-700">account_tree</span>
                        </div>
                        <div class="space-y-0.5">
                            <button wire:click="addNode('condition', 'if_else')" class="w-full px-6 py-3 flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/[0.03] transition-all group border-r-4 border-transparent hover:border-amber-500">
                                <span class="material-symbols-outlined text-amber-500/50 group-hover:text-amber-500 group-hover:scale-110 transition-all text-xl">call_split</span>
                                <span class="text-[12px] font-black tracking-tight uppercase">Condition</span>
                            </button>
                            <button wire:click="addNode('wait', 'delay')" class="w-full px-6 py-3 flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/[0.03] transition-all group border-r-4 border-transparent hover:border-amber-500">
                                <span class="material-symbols-outlined text-amber-500/50 group-hover:text-amber-500 group-hover:scale-110 transition-all text-xl">hourglass_empty</span>
                                <span class="text-[12px] font-black tracking-tight uppercase">Wait / Delay</span>
                            </button>
                            <button wire:click="addNode('loop', 'iterate_over_data')" class="w-full px-6 py-3 flex items-center gap-3 text-slate-400 hover:text-white hover:bg-white/[0.03] transition-all group border-r-4 border-transparent hover:border-amber-500">
                                <span class="material-symbols-outlined text-amber-500/50 group-hover:text-amber-500 group-hover:scale-110 transition-all text-xl">all_inclusive</span>
                                <span class="text-[12px] font-black tracking-tight uppercase">Loop</span>
                            </button>
                        </div>
                    </div>

                    {{-- 4/5. Navigation / Output --}}
                    <div class="pt-4 border-t border-white/5 space-y-1">
                        <button class="w-full px-6 py-3 flex items-center gap-3 text-slate-500 hover:text-white hover:bg-white/[0.02] transition-all group">
                            <span class="material-symbols-outlined text-lg group-hover:scale-110 transition-all">variable_insert</span>
                            <span class="text-[11px] font-black tracking-widest uppercase">Variables</span>
                        </button>
                        <button class="w-full px-6 py-3 flex items-center gap-3 text-slate-500 hover:text-white hover:bg-white/[0.02] transition-all group">
                            <span class="material-symbols-outlined text-lg group-hover:scale-110 transition-all">list_alt</span>
                            <span class="text-[11px] font-black tracking-widest uppercase">Logs / History</span>
                        </button>
                        <button class="w-full px-6 py-3 flex items-center gap-3 text-slate-500 hover:text-white hover:bg-white/[0.02] transition-all group" onclick="window.location.href='{{ route('automations.simulate', $automation->id ?? 1) }}'">
                            <span class="material-symbols-outlined text-lg group-hover:scale-110 transition-all text-primary">play_circle</span>
                            <span class="text-[11px] font-black tracking-widest uppercase text-primary">Simulation</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-5 border-t border-white/10 bg-[#081325]">
                <div class="space-y-1">
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-slate-600 hover:text-slate-300 transition-colors text-[10px] font-black tracking-widest uppercase group">
                        <span class="material-symbols-outlined text-lg group-hover:rotate-12 transition-transform">menu_book</span>
                        Docs
                    </button>
                    <button class="w-full flex items-center gap-3 px-3 py-2 text-slate-600 hover:text-slate-300 transition-colors text-[10px] font-black tracking-widest uppercase group">
                        <span class="material-symbols-outlined text-lg group-hover:bounce transition-all">contact_support</span>
                        Support
                    </button>
                </div>
            </div>
        </aside>

        {{-- Canvas Area --}}
         <main class="flex-1 relative overflow-hidden bg-[#061122]" 
               x-ref="canvas"
               @mousedown="startPanning($event)"
               :class="panModeActive ? (isPanning ? 'cursor-grabbing' : 'cursor-grab') : ''">
             
             {{-- Transformed Viewport Layer --}}
             <div class="absolute inset-0 transition-none" 
                  :style="'transform-origin: 0 0; transform: scale(' + (zoom/100) + ') translate(' + panX + 'px, ' + panY + 'px)'">
                 
                 {{-- Grid Backdrop (Infinite-ish) --}}
                 <div class="absolute inset-[-10000px] pointer-events-none" 
                      style="background-image: radial-gradient(rgba(148, 163, 184, 0.14) 1px, transparent 1px); background-size: 24px 24px;">
                 </div>
            
            {{-- Drawing Layer for Edges --}}
             <svg class="pointer-events-none absolute inset-0 h-full w-full overflow-visible z-10">
                {{-- Ghost Connection --}}
                <template x-if="connectingFromNodeId">
                    <path 
                        :d="getPath(getAnchorPoint(connectingFromNodeId, connectingFromHandle, connectingConditionKey).x, getAnchorPoint(connectingFromNodeId, connectingFromHandle, connectingConditionKey).y, mouseX, mouseY)"
                        stroke="rgba(36, 99, 235, 0.5)" 
                        stroke-width="3" 
                        stroke-dasharray="8,8"
                        fill="none"
                    />
                </template>

                {{-- Real Connections --}}
                @foreach($connections as $connection)
                    @php
                        $sourceNode = $nodes->firstWhere('id', $connection->source_node_id);
                        $targetNode = $nodes->firstWhere('id', $connection->target_node_id);
                    @endphp
                    @if($sourceNode && $targetNode)
                        <g wire:key="connection-{{ $connection->id }}">
                            <path 
                                d="M 0 0"
                                x-data="{}"
                                x-init="
                                    $nextTick(() => {
                                        const p1 = getAnchorPoint({{ $sourceNode->id }}, '{{ $connection->source_handle ?: 'bottom' }}', '{{ $connection->condition_key }}');
                                        const p2 = getAnchorPoint({{ $targetNode->id }}, '{{ $connection->target_handle ?: 'top' }}');
                                        $el.setAttribute('d', getPath(p1.x, p1.y, p2.x, p2.y));
                                    })
                                "
                                @node-moved.window="
                                    const p1 = getAnchorPoint({{ $sourceNode->id }}, '{{ $connection->source_handle ?: 'bottom' }}', '{{ $connection->condition_key }}');
                                    const p2 = getAnchorPoint({{ $targetNode->id }}, '{{ $connection->target_handle ?: 'top' }}');
                                    $el.setAttribute('d', getPath(p1.x, p1.y, p2.x, p2.y));
                                "
                                stroke="{{ $connection->condition_key === 'yes' ? '#10b981' : ($connection->condition_key === 'no' ? '#f43f5e' : '#3b82f6') }}" 
                                stroke-width="2.5" 
                                fill="none"
                                class="transition-colors duration-200 hover:stroke-white cursor-pointer pointer-events-auto"
                                @click.stop="@this.removeConnection({{ $connection->id }})"
                                title="Click to remove connection"
                            />
                            @if($connection->condition_key)
                                <foreignObject 
                                    x-data="{ x: 0, y: 0 }"
                                    x-init="
                                        const p1 = getAnchorPoint({{ $sourceNode->id }}, '{{ $connection->source_handle ?: 'bottom' }}', '{{ $connection->condition_key }}');
                                        const p2 = getAnchorPoint({{ $targetNode->id }}, '{{ $connection->target_handle ?: 'top' }}');
                                        x = (p1.x + p2.x) / 2 - 20;
                                        y = (p1.y + p2.y) / 2 - 10;
                                    "
                                    @node-moved.window="
                                        const p1 = getAnchorPoint({{ $sourceNode->id }}, '{{ $connection->source_handle ?: 'bottom' }}', '{{ $connection->condition_key }}');
                                        const p2 = getAnchorPoint({{ $targetNode->id }}, '{{ $connection->target_handle ?: 'top' }}');
                                        x = (p1.x + p2.x) / 2 - 20;
                                        y = (p1.y + p2.y) / 2 - 10;
                                    "
                                    :x="x" :y="y" width="40" height="20"
                                >
                                    <div class="flex h-full items-center justify-center rounded bg-[#1e293b] border {{ $connection->condition_key === 'yes' ? 'border-emerald-500/50' : 'border-rose-500/50' }} text-[9px] font-black uppercase text-slate-400">
                                        {{ $connection->condition_key === 'yes' ? 'Yes' : 'No' }}
                                    </div>
                                </foreignObject>
                            @endif
                        </g>
                    @endif
                @endforeach
            </svg>

            {{-- Nodes Layer --}}
             <div class="absolute inset-0 z-20">
                @foreach($nodes as $node)
                    <div 
                        id="node-{{ $node->id }}"
                        wire:key="node-{{ $node->id }}"
                        class="absolute transition-shadow duration-200 cursor-move {{ $selectedNodeId === $node->id ? 'ring-4 ring-primary/30 shadow-2xl' : 'shadow-xl hover:shadow-2xl' }}"
                        style="left: {{ $node->position_x }}px; top: {{ $node->position_y }}px;"
                        @mousedown="startDrag($event, {{ $node->id }}, {{ $node->position_x }}, {{ $node->position_y }})"
                    >
                        @php
                            $colors = match($node->type) {
                                'trigger' => ['border' => 'border-emerald-500', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
                                'action' => ['border' => 'border-blue-600', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
                                'condition' => ['border' => 'border-amber-500', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
                                default => ['border' => 'border-slate-400', 'bg' => 'bg-slate-100', 'text' => 'text-slate-500'],
                            };
                            $icon = match($node->subtype) {
                                'webhook' => 'bolt',
                                'whatsapp_message' => 'chat',
                                'send_email' => 'mail',
                                'update_row' => 'database',
                                'if_else' => 'call_split',
                                'delay' => 'schedule',
                                default => 'settings'
                            };
                        @endphp

                        <div class="w-64 rounded-xl border-l-[6px] {{ $colors['border'] }} bg-[#0b1730] p-4 flex items-center gap-4 border border-white/10">
                            <div class="w-12 h-12 shrink-0 rounded-xl {{ $colors['bg'] }} flex items-center justify-center {{ $colors['text'] }}">
                                <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">{{ $icon }}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[9px] font-black uppercase tracking-wider {{ $colors['text'] }} opacity-80">{{ $node->type }}</div>
                                <div class="font-black text-white truncate tracking-tight">{{ $node->label }}</div>
                            </div>
                        </div>
                        
                        {{-- Connection Handles (Visualization) --}}
                        @if($node->type === 'condition')
                            {{-- Two output handles for Condition nodes --}}
                            <div class="absolute -bottom-1.5 left-1/4 -translate-x-1/2 flex flex-col items-center gap-1">
                                <div 
                                    class="h-4 w-4 rounded-full bg-slate-600 border-2 border-[#061122] z-30 cursor-crosshair hover:bg-emerald-500 transition-colors flex items-center justify-center group"
                                    @mousedown.stop="if (!panModeActive) startConnecting($event, {{ $node->id }}, 'bottom', 'yes')"
                                >
                                    <div class="w-1.5 h-1.5 rounded-full bg-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                </div>
                                <span class="text-[8px] font-black uppercase text-emerald-500 tracking-tighter">Yes</span>
                            </div>

                            <div class="absolute -bottom-1.5 left-3/4 -translate-x-1/2 flex flex-col items-center gap-1">
                                <div 
                                    class="h-4 w-4 rounded-full bg-slate-600 border-2 border-[#061122] z-30 cursor-crosshair hover:bg-rose-500 transition-colors flex items-center justify-center group"
                                    @mousedown.stop="if (!panModeActive) startConnecting($event, {{ $node->id }}, 'bottom', 'no')"
                                >
                                    <div class="w-1.5 h-1.5 rounded-full bg-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                </div>
                                <span class="text-[8px] font-black uppercase text-rose-500 tracking-tighter">No</span>
                            </div>
                        @else
                            {{-- Standard single output handle --}}
                            <div 
                                class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 h-4 w-4 rounded-full bg-slate-600 border-2 border-[#061122] z-30 cursor-crosshair hover:bg-primary transition-colors flex items-center justify-center group"
                                @mousedown.stop="if (!panModeActive) startConnecting($event, {{ $node->id }}, 'bottom')"
                            >
                                <div class="w-1.5 h-1.5 rounded-full bg-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </div>
                        @endif

                        @if($node->type !== 'trigger')
                            <div 
                                class="absolute -top-1.5 left-1/2 -translate-x-1/2 h-4 w-4 rounded-full bg-slate-600 border-2 border-[#061122] z-30 cursor-pointer hover:bg-emerald-500 transition-colors"
                                @mouseup.stop="if (!panModeActive) completeConnection({{ $node->id }}, 'top')"
                            ></div>
                        @endif
                    </div>
                @endforeach
            </div>

             </div>
 
             {{-- Canvas Toolbar (Centered Pill) --}}
            <div class="absolute top-6 left-1/2 -translate-x-1/2 bg-[#0c1833]/90 backdrop-blur-md border border-white/10 rounded-full shadow-2xl px-6 py-2.5 flex items-center gap-4 z-30 transition-all">
                <div class="flex items-center gap-1.5">
                    <button @click="zoom = Math.min(150, zoom + 10)" class="p-1 text-slate-400 hover:text-white hover:bg-white/5 rounded-full transition-colors">
                        <span class="material-symbols-outlined text-xl">zoom_in</span>
                    </button>
                    <span class="text-[11px] font-black text-white w-12 text-center" x-text="zoom + '%'"></span>
                    <button @click="zoom = Math.max(50, zoom - 10)" class="p-1 text-slate-400 hover:text-white hover:bg-white/5 rounded-full transition-colors">
                        <span class="material-symbols-outlined text-xl">zoom_out</span>
                    </button>
                </div>
                <div class="h-4 w-[1px] bg-white/10"></div>
                <button @click="zoom = 100" class="p-1 text-slate-400 hover:text-white hover:bg-white/5 rounded-full transition-colors" title="Fit to Screen">
                    <span class="material-symbols-outlined text-xl">fit_screen</span>
                </button>
                <div class="h-4 w-[1px] bg-white/10"></div>
                <button @click="togglePanMode()" 
                        class="p-1 rounded-full transition-colors" 
                        :class="panModeActive ? 'bg-primary text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-white/5'"
                        title="Hand Tool (Pan)">
                    <span class="material-symbols-outlined text-xl">pan_tool</span>
                </button>
                <button class="p-1 text-slate-400 hover:text-white hover:bg-white/5 rounded-full transition-colors" title="Toggle Grid">
                    <span class="material-symbols-outlined text-xl">grid_on</span>
                </button>
            </div>
        </main>

        {{-- Right Inspector (Node Config) --}}
        <aside class="w-96 bg-[#081427] border-l border-white/10 z-40 flex flex-col shadow-[-10px_0_40px_rgba(0,0,0,0.3)] transition-all"
               x-data="{ 
                   showAdvanced: false,
                   config: @entangle('nodeConfig')
               }">
            @if($selectedNodeId)
                @php $activeNode = $nodes->firstWhere('id', $selectedNodeId); @endphp
                
                @include('livewire.web.automations.sections.header', ['activeNode' => $activeNode])

                <div class="flex-1 overflow-y-auto p-6 space-y-8">
                    @foreach($this->getResolvedSections() as $section)
                        @includeIf('livewire.web.automations.sections.' . $section, [
                            'activeNode' => $activeNode,
                            'nodeConfig' => $nodeConfig
                        ])
                    @endforeach

                    @include('livewire.web.automations.sections.footer', ['activeNode' => $activeNode])
                </div>
            @else
                {{-- Empty State --}}
                <div class="flex-1 flex flex-col items-center justify-center p-12 text-center">
                    <div class="w-24 h-24 rounded-[2.5rem] bg-white/[0.03] flex items-center justify-center text-slate-600 mb-8 border border-white/5 shadow-2xl relative group">
                        <div class="absolute inset-0 bg-primary/20 rounded-[2.5rem] blur-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <span class="material-symbols-outlined text-5xl font-light relative z-10 transition-transform group-hover:scale-110">touch_app</span>
                    </div>
                    <h3 class="text-sm font-black text-white uppercase tracking-[0.2em] mb-4">No node selected</h3>
                    <p class="text-[11px] font-bold text-slate-500 uppercase leading-relaxed tracking-[0.15em] px-4">
                        Select a node on the canvas to configure its properties or add a new one from the library.
                    </p>
                </div>
                
                <div class="p-8 border-t border-white/10">
                    <div class="p-7 rounded-[2rem] bg-primary/5 border border-primary/20 text-white shadow-2xl relative overflow-hidden group">
                        <div class="absolute -right-8 -top-8 w-24 h-24 bg-primary/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                        <h4 class="text-xs font-black tracking-[0.2em] uppercase mb-4 text-primary flex items-center gap-2">
                             <span class="material-symbols-outlined text-sm">lightbulb</span>
                             Workflow Tip
                        </h4>
                        <p class="text-[11px] font-bold text-slate-400 leading-relaxed uppercase tracking-tight">
                            Connect your trigger to an action to start building your flow. Drag nodes to rearrange them on the grid.
                        </p>
                    </div>
                </div>
            @endif
        </aside>
    </div>
    {{-- Custom Trigger Modal --}}
    @if($showCustomTriggerModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="w-full max-w-md bg-[#081427] border border-white/10 rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <div class="p-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <h3 class="text-lg font-black text-white uppercase tracking-wider">New Custom Trigger</h3>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Define your unique automation entry point</p>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Trigger Name</label>
                        <input 
                            type="text" 
                            wire:model="newTriggerName"
                            placeholder="e.g. High Value Customer Inquiry"
                            class="w-full bg-[#101d39] border border-white/10 rounded-2xl px-5 py-4 text-sm text-white focus:ring-2 focus:ring-primary shadow-xl transition-all"
                        />
                        @error('newTriggerName') <span class="text-[10px] font-bold text-rose-500 uppercase tracking-tight">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Category</label>
                        <div class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-sm text-slate-400 font-bold uppercase tracking-widest">
                            {{ str_replace('_', ' ', $nodeConfig['trigger_category'] ?? 'General') }}
                        </div>
                        <p class="text-[9px] font-bold text-slate-600 uppercase tracking-tighter">Category is inherited from your current sidebar selection.</p>
                    </div>

                    <div class="space-y-2 text-primary/80 bg-primary/5 p-4 rounded-2xl border border-primary/20">
                        <p class="text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                             <span class="material-symbols-outlined text-sm">auto_awesome</span>
                             Pre-configured Defaults
                        </p>
                        <p class="text-[9px] font-medium leading-relaxed mt-1">
                            We'll automatically set up the core rules and shared variables for this category. You can fine-tune them after creation.
                        </p>
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button 
                        wire:click="$set('showCustomTriggerModal', false)"
                        class="flex-1 py-4 bg-white/5 border border-white/10 text-slate-400 text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-white/10 transition-all"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="saveCustomTrigger"
                        class="flex-1 py-4 bg-primary text-white text-[11px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-primary-dark shadow-[0_10px_20px_rgba(var(--color-primary-rgb),0.2)] transition-all active:scale-95"
                    >
                        Create Trigger
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
