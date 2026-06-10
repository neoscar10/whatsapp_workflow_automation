<div class="p-6 lg:p-8 w-full max-w-7xl mx-auto font-['Inter'] space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-[28px] font-bold tracking-tight text-[#1c1b1b] dark:text-white mb-1">Compliance Intelligence</h1>
            <p class="text-[15px] text-[#424656] dark:text-slate-400">AI-powered insights and risk analysis for your portfolio.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-slate-800 border border-[#c2c6d8] dark:border-slate-700 hover:bg-[#f6f3f2] dark:hover:bg-slate-700 text-[#1c1b1b] dark:text-white rounded-xl font-semibold text-[14px] transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[20px]">calendar_today</span>
                Last 30 Days
            </button>
            <button class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-[#003fa4] text-white rounded-xl font-semibold text-[14px] transition-all shadow-md">
                <span class="material-symbols-outlined text-[20px]">magic_button</span>
                AI Report
            </button>
        </div>
    </div>

    <!-- Top Hero Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Main AI Status Card -->
        <div class="lg:col-span-8 bg-gradient-to-br from-white to-[#f6f3f2] dark:from-slate-800 dark:to-slate-800/80 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-3xl p-8 shadow-sm relative overflow-hidden group">
            <!-- Decorative background elements -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-primary/5 dark:bg-blue-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 group-hover:scale-110 transition-transform duration-1000"></div>
            
            <div class="relative flex flex-col md:flex-row items-center gap-10">
                <!-- Radial Score -->
                <div class="relative w-48 h-48 shrink-0 flex items-center justify-center">
                    <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                        <circle class="text-[#e5e2e1] dark:text-slate-700" cx="50" cy="50" fill="none" r="44" stroke="currentColor" stroke-width="8"></circle>
                        <circle class="text-primary dark:text-blue-500" cx="50" cy="50" fill="none" r="44" stroke="currentColor" stroke-dasharray="276" stroke-dashoffset="16" stroke-width="8" stroke-linecap="round"></circle>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span class="text-[44px] font-bold tracking-tight text-[#1c1b1b] dark:text-white leading-none mb-1">94%</span>
                        <span class="text-[12px] font-['Geist'] font-semibold tracking-wider text-[#727687] dark:text-slate-400 uppercase">Health Score</span>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-[#86f2e4]/30 dark:bg-teal-900/30 text-[#006f66] dark:text-teal-400 rounded-full text-[12px] font-['Geist'] font-bold tracking-wide uppercase mb-4">
                        <span class="w-2 h-2 rounded-full bg-[#006a61] dark:bg-teal-400 animate-pulse"></span>
                        Optimal Posture
                    </div>
                    <h2 class="text-[28px] font-bold text-[#1c1b1b] dark:text-white leading-tight mb-3">AI Compliance Intelligence</h2>
                    <p class="text-[15px] text-[#424656] dark:text-slate-300 leading-relaxed mb-6">
                        Your portfolio's compliance posture is highly optimized. We've detected 3 minor optimizations for the upcoming GST filing window to completely mitigate tail-risk.
                    </p>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                        <div class="flex items-center gap-3 bg-white dark:bg-slate-900/50 px-4 py-2.5 rounded-xl border border-[#c2c6d8]/30 dark:border-slate-700">
                            <span class="material-symbols-outlined text-[#006a61] dark:text-teal-400 text-[20px]">verified_user</span>
                            <div class="text-left">
                                <p class="text-[11px] font-['Geist'] font-semibold text-[#727687] dark:text-slate-400 uppercase tracking-wide">Risk Level</p>
                                <p class="text-[14px] font-semibold text-[#1c1b1b] dark:text-white leading-none mt-0.5">Minimal</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-white dark:bg-slate-900/50 px-4 py-2.5 rounded-xl border border-[#c2c6d8]/30 dark:border-slate-700">
                            <span class="material-symbols-outlined text-primary dark:text-blue-400 text-[20px]">schedule</span>
                            <div class="text-left">
                                <p class="text-[11px] font-['Geist'] font-semibold text-[#727687] dark:text-slate-400 uppercase tracking-wide">Next Deadline</p>
                                <p class="text-[14px] font-semibold text-[#1c1b1b] dark:text-white leading-none mt-0.5">12 Days (GST R1)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Regulatory Feed -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-800 border border-[#c2c6d8]/50 dark:border-slate-700 rounded-3xl p-8 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-primary dark:text-blue-400">rss_feed</span>
                    <h3 class="text-[20px] font-bold text-[#1c1b1b] dark:text-white">Regulatory Feed</h3>
                </div>
                <p class="text-[14px] text-[#727687] dark:text-slate-400 mb-6">Real-time policy updates</p>
                
                <div class="space-y-5">
                    <div class="flex gap-4 group cursor-pointer">
                        <div class="w-2.5 h-2.5 mt-1.5 rounded-full bg-primary dark:bg-blue-400 shrink-0 group-hover:scale-125 transition-transform"></div>
                        <div>
                            <p class="text-[15px] font-semibold text-[#1c1b1b] dark:text-white group-hover:text-primary dark:group-hover:text-blue-400 transition-colors">New TDS Circular v2.1</p>
                            <p class="text-[13px] text-[#424656] dark:text-slate-400 mt-1">Revised threshold limits effective from July 1st.</p>
                            <p class="text-[11px] font-medium text-[#727687] dark:text-slate-500 mt-2">2 hours ago</p>
                        </div>
                    </div>
                    <div class="flex gap-4 group cursor-pointer">
                        <div class="w-2.5 h-2.5 mt-1.5 rounded-full bg-[#727687] dark:bg-slate-500 shrink-0 group-hover:scale-125 transition-transform"></div>
                        <div>
                            <p class="text-[15px] font-semibold text-[#1c1b1b] dark:text-white group-hover:text-primary dark:group-hover:text-blue-400 transition-colors">GST Portal Maintenance</p>
                            <p class="text-[13px] text-[#424656] dark:text-slate-400 mt-1">Scheduled downtime on June 15, 22:00 IST.</p>
                            <p class="text-[11px] font-medium text-[#727687] dark:text-slate-500 mt-2">Yesterday</p>
                        </div>
                    </div>
                </div>
            </div>
            <button class="w-full mt-6 py-3 bg-[#f6f3f2] hover:bg-[#e5e2e1] dark:bg-slate-900 dark:hover:bg-slate-700 text-[#1c1b1b] dark:text-white rounded-xl font-semibold text-[13px] transition-colors">
                View Full Feed
            </button>
        </div>
    </div>

    <!-- Bento Grid - 3 Columns -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Insight 1: Risk -->
        <div class="bg-white dark:bg-slate-800 border border-[#ba1a1a]/20 dark:border-red-900/30 rounded-3xl p-8 shadow-sm hover:shadow-md hover:border-[#ba1a1a]/40 dark:hover:border-red-500/50 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-6">
                    <div class="w-12 h-12 rounded-xl bg-[#ffdad6] dark:bg-red-900/40 flex items-center justify-center text-[#93000a] dark:text-red-400">
                        <span class="material-symbols-outlined text-[24px]">warning</span>
                    </div>
                    <span class="px-3 py-1 bg-[#ffdad6]/50 dark:bg-red-900/20 text-[#93000a] dark:text-red-400 rounded-full text-[12px] font-['Geist'] font-bold tracking-wide uppercase">
                        Medium Risk
                    </span>
                </div>
                <h4 class="text-[20px] font-bold text-[#1c1b1b] dark:text-white mb-3">GST Filing Delay Risk</h4>
                <p class="text-[14px] text-[#424656] dark:text-slate-400 leading-relaxed">
                    Based on historical data from vendors, a 2-day delay is predicted for Input Tax Credit reconciliation.
                </p>
            </div>
            <div class="mt-8 p-4 bg-[#f6f3f2] dark:bg-slate-900/80 rounded-xl border border-[#c2c6d8]/50 dark:border-slate-700">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-primary dark:text-blue-400 text-[16px]">smart_toy</span>
                    <p class="text-[12px] font-['Geist'] font-bold text-primary dark:text-blue-400 uppercase tracking-wide">AI Recommendation</p>
                </div>
                <p class="text-[13px] text-[#1c1b1b] dark:text-slate-200 font-medium italic leading-relaxed">
                    "Activate auto-reminder escalation for Top 5 vendors to secure credit."
                </p>
            </div>
        </div>

        <!-- Insight 2: Pattern -->
        <div class="bg-white dark:bg-slate-800 border border-[#006a61]/20 dark:border-teal-900/30 rounded-3xl p-8 shadow-sm hover:shadow-md hover:border-[#006a61]/40 dark:hover:border-teal-500/50 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-6">
                    <div class="w-12 h-12 rounded-xl bg-[#86f2e4]/30 dark:bg-teal-900/40 flex items-center justify-center text-[#006a61] dark:text-teal-400">
                        <span class="material-symbols-outlined text-[24px]">trending_up</span>
                    </div>
                    <span class="px-3 py-1 bg-[#86f2e4]/30 dark:bg-teal-900/20 text-[#006f66] dark:text-teal-400 rounded-full text-[12px] font-['Geist'] font-bold tracking-wide uppercase">
                        Optimized
                    </span>
                </div>
                <h4 class="text-[20px] font-bold text-[#1c1b1b] dark:text-white mb-3">Filing Pattern Analysis</h4>
                <p class="text-[14px] text-[#424656] dark:text-slate-400 leading-relaxed mb-6">
                    Your filing accuracy has improved by 12% Q-o-Q. Current error rate is 0.4% below industry average.
                </p>
            </div>
            
            <div class="h-24 flex items-end gap-2 w-full pt-4">
                <div class="flex-1 bg-[#f6f3f2] dark:bg-slate-700 rounded-t-md h-[40%] hover:bg-[#006a61]/20 dark:hover:bg-teal-500/20 transition-colors"></div>
                <div class="flex-1 bg-[#f6f3f2] dark:bg-slate-700 rounded-t-md h-[50%] hover:bg-[#006a61]/20 dark:hover:bg-teal-500/20 transition-colors"></div>
                <div class="flex-1 bg-[#006a61] dark:bg-teal-500 rounded-t-md h-[100%]"></div>
                <div class="flex-1 bg-[#f6f3f2] dark:bg-slate-700 rounded-t-md h-[60%] hover:bg-[#006a61]/20 dark:hover:bg-teal-500/20 transition-colors"></div>
                <div class="flex-1 bg-[#006a61]/80 dark:bg-teal-400 rounded-t-md h-[80%]"></div>
            </div>
        </div>

        <!-- Insight 3: Intelligence -->
        <div class="bg-white dark:bg-slate-800 border border-primary/20 dark:border-blue-900/30 rounded-3xl p-8 shadow-sm hover:shadow-md hover:border-primary/40 dark:hover:border-blue-500/50 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-6">
                    <div class="w-12 h-12 rounded-xl bg-[#e1e0ff] dark:bg-blue-900/40 flex items-center justify-center text-primary dark:text-blue-400">
                        <span class="material-symbols-outlined text-[24px]">psychology</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary dark:bg-blue-400 animate-pulse"></span>
                        <span class="px-3 py-1 bg-[#e1e0ff]/50 dark:bg-blue-900/20 text-primary dark:text-blue-400 rounded-full text-[12px] font-['Geist'] font-bold tracking-wide uppercase">
                            Active Scan
                        </span>
                    </div>
                </div>
                <h4 class="text-[20px] font-bold text-[#1c1b1b] dark:text-white mb-3">TDS Intelligence</h4>
                <p class="text-[14px] text-[#424656] dark:text-slate-400 leading-relaxed">
                    Cross-referencing 26AS data with your ledger reveals 3 mismatched entries in Section 194J.
                </p>
            </div>
            
            <button class="mt-8 w-full inline-flex justify-center items-center gap-2 px-5 py-3 bg-[#e1e0ff]/30 hover:bg-[#e1e0ff] dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-primary dark:text-blue-400 rounded-xl font-bold text-[14px] transition-colors">
                Review Discrepancies
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </button>
        </div>
    </div>

    <!-- AI Business Advisory Section -->
    <div class="pt-4">
        <div class="flex items-center gap-3 mb-6">
            <span class="material-symbols-outlined text-[#1c1b1b] dark:text-white text-[28px]">lightbulb</span>
            <h3 class="text-[24px] font-bold text-[#1c1b1b] dark:text-white">AI Business Advisory</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Advisory 1 -->
            <div class="group bg-[#f6f3f2]/50 dark:bg-slate-800/50 border border-[#c2c6d8]/50 dark:border-slate-700 hover:border-primary dark:hover:border-blue-500 rounded-3xl p-8 transition-all cursor-pointer">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 bg-white dark:bg-slate-800 rounded-2xl shadow-sm flex items-center justify-center group-hover:bg-primary dark:group-hover:bg-blue-500 transition-colors">
                        <span class="material-symbols-outlined text-primary dark:text-blue-400 group-hover:text-white text-[28px]">account_balance</span>
                    </div>
                    <div>
                        <p class="text-[12px] font-['Geist'] font-bold text-primary dark:text-blue-400 uppercase tracking-wide mb-1">Upcoming Trigger</p>
                        <h4 class="text-[18px] font-bold text-[#1c1b1b] dark:text-white">Mandatory Audit Applicability</h4>
                    </div>
                </div>
                <p class="text-[15px] text-[#424656] dark:text-slate-300 leading-relaxed">
                    Your current turnover trajectory suggests a Tax Audit requirement under Section 44AB for FY 24-25. We recommend initializing documentation by Q3.
                </p>
            </div>
            
            <!-- Advisory 2 -->
            <div class="group bg-[#f6f3f2]/50 dark:bg-slate-800/50 border border-[#c2c6d8]/50 dark:border-slate-700 hover:border-[#006a61] dark:hover:border-teal-500 rounded-3xl p-8 transition-all cursor-pointer">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 bg-white dark:bg-slate-800 rounded-2xl shadow-sm flex items-center justify-center group-hover:bg-[#006a61] dark:group-hover:bg-teal-500 transition-colors">
                        <span class="material-symbols-outlined text-[#006a61] dark:text-teal-400 group-hover:text-white text-[28px]">groups</span>
                    </div>
                    <div>
                        <p class="text-[12px] font-['Geist'] font-bold text-[#006a61] dark:text-teal-400 uppercase tracking-wide mb-1">Payroll Growth</p>
                        <h4 class="text-[18px] font-bold text-[#1c1b1b] dark:text-white">PF/ESI Registration</h4>
                    </div>
                </div>
                <p class="text-[15px] text-[#424656] dark:text-slate-300 leading-relaxed">
                    Employee headcount increased by 15%. Automated PF/ESI registration portal access is now ready for your digital signature.
                </p>
            </div>
        </div>
    </div>
</div>
