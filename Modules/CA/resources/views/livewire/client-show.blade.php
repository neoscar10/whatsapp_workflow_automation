<div class="p-6 lg:p-8 w-full max-w-7xl mx-auto font-['Inter']">
    <!-- Header Section -->
    <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl p-6 shadow-sm mb-8">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 rounded-2xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary dark:text-blue-400 font-bold text-[32px] shrink-0">
                    {{ substr($client->client_name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-[24px] font-semibold text-[#1c1b1b] dark:text-white mb-2">{{ $client->client_name }}</h1>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#f6f3f2] dark:bg-slate-900 text-[#424656] dark:text-slate-300 rounded-lg text-[13px] font-medium border border-[#c2c6d8]/50 dark:border-slate-700">
                            <span class="material-symbols-outlined text-[16px]">domain</span>
                            {{ $client->businessType->name ?? 'N/A' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#f6f3f2] dark:bg-slate-900 text-[#424656] dark:text-slate-300 rounded-lg text-[13px] font-medium border border-[#c2c6d8]/50 dark:border-slate-700">
                            <span class="material-symbols-outlined text-[16px]">location_on</span>
                            {{ $client->city ?? 'City' }}, {{ $client->state ?? 'State' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#f6f3f2] dark:bg-slate-900 text-[#424656] dark:text-slate-300 rounded-lg text-[13px] font-medium border border-[#c2c6d8]/50 dark:border-slate-700">
                            <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                            Onboarded: {{ $client->created_at->format('M Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <button class="flex-1 md:flex-none inline-flex justify-center items-center gap-2 px-6 py-2.5 bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-600 hover:bg-[#f6f3f2] dark:hover:bg-slate-700 text-[#424656] dark:text-slate-300 rounded-xl font-medium text-[14px] transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">edit</span> Edit
                </button>
                @if($client->contact_id)
                    <a href="{{ route('chats.index', ['contact' => $client->contact_id]) }}" class="flex-1 md:flex-none inline-flex justify-center items-center gap-2 px-6 py-2.5 bg-[#25D366] hover:bg-[#1DA851] text-white rounded-xl font-medium text-[14px] transition-colors shadow-md">
                        <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg> WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Main Content Area -->
        <div class="lg:col-span-8 space-y-8">
            
            <!-- Client Details -->
            <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-[#c2c6d8]/50 dark:border-slate-700 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary dark:text-blue-400">person</span>
                    <h2 class="text-[18px] font-semibold text-[#1c1b1b] dark:text-white">Client Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-[12px] font-['Geist'] font-medium text-[#727687] dark:text-slate-400 uppercase tracking-wide mb-1">Full Name</p>
                            <h3 class="text-[15px] font-semibold text-[#1c1b1b] dark:text-white">{{ $client->client_name }}</h3>
                        </div>
                        <div>
                            <p class="text-[12px] font-['Geist'] font-medium text-[#727687] dark:text-slate-400 uppercase tracking-wide mb-1">Mobile</p>
                            <h3 class="text-[15px] font-semibold text-[#1c1b1b] dark:text-white">{{ $client->phone ?? '-' }}</h3>
                        </div>
                        <div>
                            <p class="text-[12px] font-['Geist'] font-medium text-[#727687] dark:text-slate-400 uppercase tracking-wide mb-1">E-mail</p>
                            <h3 class="text-[15px] font-semibold text-[#1c1b1b] dark:text-white">{{ $client->email ?? '-' }}</h3>
                        </div>
                        <div>
                            <p class="text-[12px] font-['Geist'] font-medium text-[#727687] dark:text-slate-400 uppercase tracking-wide mb-1">Full Address</p>
                            <h3 class="text-[15px] font-semibold text-[#1c1b1b] dark:text-white">{{ $client->address ?? '-' }}, {{ $client->country ?? '-' }}</h3>
                        </div>
                    </div>
                    
                    @if($client->contact)
                        <div class="mt-6 pt-6 border-t border-[#c2c6d8]/30 dark:border-slate-700 flex items-center justify-between bg-[#f6f3f2]/50 dark:bg-slate-900/50 p-4 rounded-xl">
                            <div class="flex items-center gap-4">
                                <img src="{{ $client->contact->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($client->contact->name) }}" class="w-12 h-12 rounded-full border-2 border-white dark:border-slate-800 shadow-sm object-cover">
                                <div>
                                    <p class="text-[11px] font-['Geist'] font-bold text-primary dark:text-blue-400 uppercase tracking-wide mb-0.5">Linked Contact</p>
                                    <h4 class="text-[15px] font-semibold text-[#1c1b1b] dark:text-white">{{ $client->contact->name }}</h4>
                                </div>
                            </div>
                            <a href="{{ route('contacts.index', ['search' => $client->contact->phone ?? $client->contact->name]) }}" wire:navigate class="px-4 py-2 bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-600 hover:bg-primary/10 text-primary dark:text-blue-400 rounded-lg text-[13px] font-semibold transition-colors">
                                View Contact
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Assigned Compliances -->
            <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-[#c2c6d8]/50 dark:border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#006a61] dark:text-teal-400">task</span>
                        <h2 class="text-[18px] font-semibold text-[#1c1b1b] dark:text-white">Assigned Compliances</h2>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($client->clientCompliances as $cc)
                            @php
                                $statusStyle = match($cc->health_status) {
                                    'completed' => ['bg' => 'bg-[#86f2e4]/30', 'text' => 'text-[#006f66]', 'icon' => 'check_circle', 'dot' => 'bg-[#006a61]'],
                                    'overdue' => ['bg' => 'bg-[#ffdad6]', 'text' => 'text-[#93000a]', 'icon' => 'error', 'dot' => 'bg-[#ba1a1a]'],
                                    'at_risk' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'warning', 'dot' => 'bg-yellow-600'],
                                    'in_progress' => ['bg' => 'bg-primary/20', 'text' => 'text-blue-900', 'icon' => 'pending_actions', 'dot' => 'bg-primary'],
                                    default => ['bg' => 'bg-[#f6f3f2]', 'text' => 'text-[#424656]', 'icon' => 'sync', 'dot' => 'bg-[#727687]']
                                };
                            @endphp
                            <div class="group flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white dark:bg-slate-800 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-xl hover:border-primary/50 hover:shadow-sm transition-all gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center shrink-0 {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }}">
                                        <span class="material-symbols-outlined text-[24px]">{{ $statusStyle['icon'] }}</span>
                                    </div>
                                    <div>
                                        <a href="{{ route('ca.clients.compliance.workspace', ['clientId' => $client->id, 'clientComplianceId' => $cc->id]) }}" class="text-[16px] font-semibold text-[#1c1b1b] dark:text-white group-hover:text-primary dark:group-hover:text-blue-400 transition-colors">
                                            {{ $cc->compliance->name ?? 'Unknown' }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[13px] text-[#424656] dark:text-slate-400">{{ $cc->compliance->serviceCategory->name ?? '-' }}</span>
                                            <span class="w-1 h-1 rounded-full bg-[#c2c6d8]"></span>
                                            <span class="text-[13px] text-[#727687] dark:text-slate-500">Assigned: {{ $cc->assigned_at ? $cc->assigned_at->format('M Y') : '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-['Geist'] font-medium tracking-wide {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }} shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusStyle['dot'] }}"></span>
                                    {{ str_replace('_', ' ', strtoupper($cc->health_status)) }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-10 bg-[#f6f3f2]/30 dark:bg-slate-900/30 rounded-xl border-2 border-dashed border-[#c2c6d8] dark:border-slate-700">
                                <span class="material-symbols-outlined text-[40px] text-[#727687] dark:text-slate-400 mb-3 block">receipt_long</span>
                                <h3 class="text-[16px] font-medium text-[#1c1b1b] dark:text-white mb-1">No Compliances Assigned</h3>
                                <p class="text-[14px] text-[#424656] dark:text-slate-400">Click assign to start adding compliance tasks.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-4 space-y-8">
            <!-- Compliance Tracker -->
            <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl shadow-sm p-6">
                <h3 class="text-[16px] font-semibold text-[#1c1b1b] dark:text-white mb-5 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary dark:text-blue-400">track_changes</span>
                    Active Subscriptions
                </h3>
                
                <div class="space-y-3">
                    @forelse($client->clientCompliances->pluck('compliance.serviceCategory.name')->filter()->unique() as $serviceName)
                        <div class="flex items-center justify-between p-3 bg-[#f6f3f2]/50 dark:bg-slate-900/50 rounded-xl border border-[#c2c6d8]/30 dark:border-slate-700">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-600 flex items-center justify-center text-[#424656] dark:text-slate-400 shrink-0">
                                    <span class="material-symbols-outlined text-[16px]">verified_user</span>
                                </div>
                                <span class="font-semibold text-[#1c1b1b] dark:text-white text-[14px]">{{ $serviceName }}</span>
                            </div>
                            <span class="px-2 py-0.5 bg-primary/20 text-blue-900 dark:bg-primary/30 dark:text-blue-300 rounded text-[10px] font-bold tracking-wider uppercase">
                                Tracked
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-[13px] text-[#727687] dark:text-slate-400">
                            No active subscriptions.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 rounded-2xl shadow-sm p-6">
                <h3 class="text-[16px] font-semibold text-[#1c1b1b] dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary dark:text-blue-400">history</span>
                    Activity History
                </h3>
                
                <div class="relative pl-6 space-y-8 before:content-[''] before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#c2c6d8]/50 dark:before:bg-slate-700">
                    @forelse($client->timelines as $timeline)
                        @php
                            $isDoc = str_contains($timeline->event_key, 'document') || str_contains($timeline->event_key, 'upload');
                            $isError = str_contains($timeline->event_key, 'overdue') || str_contains($timeline->event_key, 'reject');
                            
                            $timelineColor = $isDoc ? 'bg-primary dark:bg-blue-500' : ($isError ? 'bg-[#ba1a1a] dark:bg-red-500' : 'bg-[#006a61] dark:bg-teal-500');
                        @endphp
                        <div class="relative group">
                            <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full {{ $timelineColor }} ring-4 ring-white dark:ring-slate-800"></div>
                            <h4 class="text-[14px] font-semibold text-[#1c1b1b] dark:text-white mb-1">{{ $timeline->title }}</h4>
                            <p class="text-[13px] text-[#424656] dark:text-slate-400 leading-snug">{{ $timeline->description }}</p>
                            <p class="text-[11px] font-medium text-[#727687] dark:text-slate-500 mt-2">{{ $timeline->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <div class="text-center py-6 text-[13px] text-[#727687] dark:text-slate-400">
                            No recent activity.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
