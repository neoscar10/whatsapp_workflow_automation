<div x-data="calendarUI()" class="p-6 lg:p-8 w-full max-w-7xl mx-auto font-['Inter']">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-[28px] font-bold tracking-tight text-[#1c1b1b] dark:text-white">Compliance Calendar</h1>
            <p class="text-[15px] text-[#424656] dark:text-slate-400 mt-1">Track deadlines, overdue actions, and upcoming compliance events across all clients.</p>
        </div>
    </div>

    <!-- Top Row: Quick Stats & Legend -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Overdue Stats Card -->
        <div class="bg-white dark:bg-slate-800 border-l-4 border-l-[#ba1a1a] border border-[#c2c6d8]/50 dark:border-slate-700 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-[13px] font-bold text-[#727687] dark:text-slate-400 uppercase tracking-wider">Overdue</h3>
                <div class="w-8 h-8 rounded-full bg-[#ffdad6] dark:bg-red-500/20 flex items-center justify-center text-[#ba1a1a] dark:text-red-400">
                    <span class="material-symbols-outlined text-[18px]">error</span>
                </div>
            </div>
            <div class="text-[32px] font-extrabold text-[#ba1a1a] dark:text-red-400">{{ $stats['overdue'] }}</div>
            <p class="text-[13px] text-[#424656] dark:text-slate-400 mt-1">Require immediate attention</p>
        </div>

        <!-- Pending Stats Card -->
        <div class="bg-white dark:bg-slate-800 border-l-4 border-l-[#f7b84b] border border-[#c2c6d8]/50 dark:border-slate-700 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-[13px] font-bold text-[#727687] dark:text-slate-400 uppercase tracking-wider">This Week</h3>
                <div class="w-8 h-8 rounded-full bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center text-yellow-600 dark:text-yellow-400">
                    <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                </div>
            </div>
            <div class="text-[32px] font-extrabold text-[#1c1b1b] dark:text-white">{{ $stats['pending_week'] }}</div>
            <p class="text-[13px] text-[#424656] dark:text-slate-400 mt-1">Deadlines in next 7 days</p>
        </div>

        <!-- Completed Stats Card -->
        <div class="bg-white dark:bg-slate-800 border-l-4 border-l-[#0ab39c] border border-[#c2c6d8]/50 dark:border-slate-700 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-[13px] font-bold text-[#727687] dark:text-slate-400 uppercase tracking-wider">This Month</h3>
                <div class="w-8 h-8 rounded-full bg-teal-50 dark:bg-teal-500/20 flex items-center justify-center text-[#0ab39c] dark:text-teal-400">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                </div>
            </div>
            <div class="text-[32px] font-extrabold text-[#1c1b1b] dark:text-white">{{ $stats['completed_month'] }}</div>
            <p class="text-[13px] text-[#424656] dark:text-slate-400 mt-1">Successfully fulfilled</p>
        </div>

        <!-- Legend -->
        <div class="bg-white dark:bg-slate-800 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-2xl p-5 shadow-sm flex flex-col justify-center">
            <h3 class="text-[13px] font-bold text-[#727687] dark:text-slate-400 uppercase tracking-wider mb-3">Calendar Legend</h3>
            <div class="space-y-2">
                <div class="flex items-center gap-3 text-[13px] font-medium text-[#424656] dark:text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#ba1a1a]"></span> Overdue Action
                </div>
                <div class="flex items-center gap-3 text-[13px] font-medium text-[#424656] dark:text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#f7b84b]"></span> Pending Deadline
                </div>
                <div class="flex items-center gap-3 text-[13px] font-medium text-[#424656] dark:text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#006a61]"></span> Completed Task
                </div>
            </div>
        </div>
    </div>

    <!-- Main Calendar -->
    <div class="w-full bg-white dark:bg-slate-800 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-2xl shadow-sm p-6 overflow-hidden">
        <style>
            /* Tailwind FullCalendar Premium Override */
            .fc {
                --fc-border-color: rgba(194, 198, 216, 0.4);
                --fc-page-bg-color: transparent;
                --fc-neutral-bg-color: transparent;
                --fc-list-event-hover-bg-color: rgba(0, 80, 203, 0.05);
                --fc-event-bg-color: transparent;
                --fc-event-border-color: transparent;
                font-family: 'Inter', sans-serif;
            }
            .fc-theme-standard .fc-scrollgrid { border: 1px solid var(--fc-border-color); border-radius: 0.75rem; overflow: hidden; }
            .fc .fc-toolbar-title { font-size: 1.25rem; font-weight: 700; color: #1c1b1b; }
            
            .fc .fc-button-primary {
                background-color: #ffffff !important;
                border: 1px solid var(--fc-border-color) !important;
                color: #424656 !important;
                border-radius: 0.5rem;
                text-transform: capitalize;
                font-weight: 600;
                box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important;
                transition: all 0.2s;
            }
            .fc .fc-button-primary:hover { background-color: #f6f3f2 !important; }
            .fc .fc-button-primary:not(:disabled).fc-button-active,
            .fc .fc-button-primary:not(:disabled):active {
                background-color: #0050cb !important;
                border-color: #0050cb !important;
                color: #fff !important;
            }
            
            .fc .fc-col-header-cell-cushion {
                padding: 14px;
                font-weight: 700;
                color: #727687;
                text-transform: uppercase;
                font-size: 12px;
                letter-spacing: 0.05em;
                text-decoration: none;
            }
            
            .fc .fc-daygrid-day-number { color: #1c1b1b; font-weight: 600; text-decoration: none; padding: 12px; font-size: 14px; }
            .fc .fc-day-today { background-color: rgba(0, 80, 203, 0.03) !important; }
            .fc-daygrid-day { cursor: pointer; transition: background-color 0.2s; }
            .fc-daygrid-day:hover { background-color: rgba(0, 80, 203, 0.02) !important; }

            /* Remove default event background completely for custom dot rendering */
            .fc-event { border: none !important; background: transparent !important; box-shadow: none !important; cursor: pointer; }

            @media (prefers-color-scheme: dark) {
                .fc {
                    --fc-border-color: rgba(51, 65, 85, 1);
                    --fc-neutral-bg-color: rgba(30, 41, 59, 0.5);
                    --fc-list-event-hover-bg-color: rgba(51, 65, 85, 0.5);
                    color: #f8fafc;
                }
                .fc .fc-toolbar-title { color: #fff; }
                .fc .fc-button-primary { background-color: #1e293b !important; color: #cbd5e1 !important; }
                .fc .fc-button-primary:hover { background-color: #334155 !important; }
                .fc .fc-col-header-cell-cushion { color: #94a3b8; }
                .fc .fc-daygrid-day-number { color: #f8fafc; }
                .fc .fc-day-today { background-color: rgba(129, 140, 248, 0.05) !important; }
                .fc-daygrid-day:hover { background-color: rgba(255, 255, 255, 0.02) !important; }
            }
        </style>
        
        <div id="compliance-calendar" class="min-h-[600px]"></div>
    </div>

    <!-- Event Details Slide-over Modal -->
    <div x-show="modalOpen" class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" style="display: none;">
        <div x-show="modalOpen" 
             x-transition:enter="ease-in-out duration-500" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in-out duration-500" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
                    <div x-show="modalOpen" 
                         @click.outside="modalOpen = false"
                         x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" 
                         x-transition:enter-start="translate-x-full" 
                         x-transition:enter-end="translate-x-0" 
                         x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" 
                         x-transition:leave-start="translate-x-0" 
                         x-transition:leave-end="translate-x-full" 
                         class="pointer-events-auto w-screen max-w-md">
                        
                        <div class="flex h-full flex-col bg-white dark:bg-slate-900 shadow-2xl rounded-l-3xl border-l border-[#c2c6d8]/50 dark:border-slate-700">
                            <!-- Modal Header -->
                            <div class="px-6 py-6 sm:px-8 bg-slate-50 dark:bg-slate-800/50 border-b border-[#c2c6d8]/50 dark:border-slate-700 flex items-start justify-between gap-4 shrink-0">
                                <div>
                                    <h2 class="text-[20px] font-bold text-[#1c1b1b] dark:text-white" id="slide-over-title" x-text="formattedDate">Compliance Details</h2>
                                    <p class="text-[14px] text-[#424656] dark:text-slate-400 mt-1"><span x-text="selectedDayEvents.length"></span> event(s) scheduled for this day.</p>
                                </div>
                                <button @click="modalOpen = false" type="button" class="relative rounded-full p-2 bg-white dark:bg-slate-800 text-[#424656] dark:text-slate-400 hover:text-[#1c1b1b] dark:hover:text-white border border-[#c2c6d8] dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-sm">
                                    <span class="sr-only">Close panel</span>
                                    <span class="material-symbols-outlined text-[20px]">close</span>
                                </button>
                            </div>
                            
                            <!-- Modal Body (List of Events) -->
                            <div class="flex-1 overflow-y-auto p-6 sm:p-8">
                                <div class="space-y-4">
                                    <template x-for="event in selectedDayEvents" :key="event.id">
                                        <div class="bg-white dark:bg-slate-800/50 rounded-2xl p-5 border border-[#c2c6d8]/50 dark:border-slate-700 shadow-sm relative overflow-hidden">
                                            <!-- Colored Left Border -->
                                            <div class="absolute left-0 top-0 bottom-0 w-1" 
                                                 :class="{
                                                     'bg-[#0ab39c]': event.extendedProps.status === 'completed',
                                                     'bg-[#ba1a1a]': event.extendedProps.status === 'overdue',
                                                     'bg-[#f7b84b]': event.extendedProps.status === 'pending'
                                                 }"></div>
                                            
                                            <div class="flex justify-between items-start mb-3">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold tracking-wide uppercase shadow-sm"
                                                      :class="{
                                                          'bg-[#e0fbf6] text-[#006a61] dark:bg-teal-500/20 dark:text-teal-400': event.extendedProps.status === 'completed',
                                                          'bg-[#ffdad6] text-[#ba1a1a] dark:bg-red-500/20 dark:text-red-400': event.extendedProps.status === 'overdue',
                                                          'bg-[#fef9c3] text-[#854d0e] dark:bg-yellow-500/20 dark:text-yellow-400': event.extendedProps.status === 'pending'
                                                      }">
                                                    <span class="material-symbols-outlined text-[14px]" x-text="event.extendedProps.status === 'completed' ? 'check_circle' : (event.extendedProps.status === 'overdue' ? 'error' : 'pending_actions')"></span>
                                                    <span x-text="event.extendedProps.status"></span>
                                                </span>
                                            </div>
                                            
                                            <h3 class="text-[16px] font-bold text-[#1c1b1b] dark:text-white mb-1" x-text="event.extendedProps.client_name"></h3>
                                            <p class="text-[13px] text-[#424656] dark:text-slate-400 mb-4 flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-[16px] text-[#727687] dark:text-slate-500">task</span>
                                                <span x-text="event.extendedProps.compliance_name"></span>
                                            </p>
                                            
                                            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-[#c2c6d8]/30 dark:border-slate-700/50">
                                                <a :href="event.extendedProps.workspace_url" class="flex-1 inline-flex justify-center items-center gap-1.5 px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-[#1c1b1b] dark:text-white rounded-lg font-medium text-[12px] transition-colors">
                                                    <span class="material-symbols-outlined text-[16px]">folder_open</span> Workspace
                                                </a>
                                                <template x-if="event.extendedProps.contact_id">
                                                    <a :href="'/chats?contact=' + event.extendedProps.contact_id" class="flex-1 inline-flex justify-center items-center gap-1.5 px-3 py-2 bg-[#25D366]/10 hover:bg-[#25D366]/20 text-[#128C7E] dark:text-[#25D366] rounded-lg font-medium text-[12px] transition-colors">
                                                        <svg class="w-[14px] h-[14px]" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                                                        Message
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js & FullCalendar Integration -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('calendarUI', () => ({
                modalOpen: false,
                selectedDayEvents: [],
                formattedDate: '',
                init() {
                    var calendarEl = document.getElementById('compliance-calendar');
                    var rawEvents = @json($events);
                    
                    // Group events by date
                    var eventsByDate = {};
                    rawEvents.forEach(e => {
                        let date = e.start;
                        if(!eventsByDate[date]) eventsByDate[date] = [];
                        eventsByDate[date].push(e);
                    });

                    // Create single indicator event per date
                    var groupedEvents = Object.keys(eventsByDate).map(date => {
                        let dayEvents = eventsByDate[date];
                        let hasOverdue = dayEvents.some(e => e.extendedProps.status === 'overdue');
                        let hasPending = dayEvents.some(e => e.extendedProps.status === 'pending');
                        let hasCompleted = dayEvents.some(e => e.extendedProps.status === 'completed');
                        
                        return {
                            start: date,
                            extendedProps: {
                                isGroupedIndicator: true,
                                dayEvents: dayEvents,
                                hasOverdue: hasOverdue,
                                hasPending: hasPending,
                                hasCompleted: hasCompleted
                            }
                        };
                    });

                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        themeSystem: 'standard',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek'
                        },
                        events: groupedEvents,
                        eventContent: function(arg) {
                            if(arg.event.extendedProps.isGroupedIndicator) {
                                let props = arg.event.extendedProps;
                                let html = '<div class="flex justify-center items-center gap-1.5 w-full mt-1 mb-1">';
                                if (props.hasOverdue) html += '<div class="w-2.5 h-2.5 rounded-full bg-[#ba1a1a] shadow-sm"></div>';
                                if (props.hasPending) html += '<div class="w-2.5 h-2.5 rounded-full bg-[#f7b84b] shadow-sm"></div>';
                                if (props.hasCompleted) html += '<div class="w-2.5 h-2.5 rounded-full bg-[#0ab39c] shadow-sm"></div>';
                                html += '</div>';
                                return { html: html };
                            }
                        },
                        eventClick: (info) => {
                            info.jsEvent.preventDefault();
                            if(info.event.extendedProps.isGroupedIndicator) {
                                this.selectedDayEvents = info.event.extendedProps.dayEvents;
                                
                                // Format date nicely for the modal title
                                let dateObj = new Date(info.event.start);
                                this.formattedDate = dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                                
                                this.modalOpen = true;
                            }
                        },
                        dateClick: (info) => {
                            // Also open modal if clicking the day cell itself if it has events
                            let dateStr = info.dateStr;
                            if(eventsByDate[dateStr]) {
                                this.selectedDayEvents = eventsByDate[dateStr];
                                let dateObj = new Date(dateStr);
                                this.formattedDate = dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                                this.modalOpen = true;
                            }
                        }
                    });
                    calendar.render();
                }
            }));
        });
    </script>
    @endpush
</div>
