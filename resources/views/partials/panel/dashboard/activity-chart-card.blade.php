<div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
    <div class="flex items-center justify-between border-b border-slate-100 p-6 dark:border-slate-800">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ data_get($chart, 'title') }}</h3>
            <p class="text-[12px] font-medium text-slate-400 dark:text-slate-500">{{ data_get($chart, 'subtitle') }}</p>
        </div>
    </div>

    <div class="flex flex-1 flex-col p-8">
        <div class="mb-8 flex items-end gap-3">
            <h4 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white leading-none">
                {{ data_get($chart, 'total') }}
            </h4>

            <div class="flex items-center gap-1.5 mb-1 px-2 py-0.5 rounded-full bg-green-50 dark:bg-green-900/20">
                <span class="material-symbols-outlined text-[18px] text-green-500">trending_up</span>
                <span class="text-xs font-black text-green-600 dark:text-green-500 uppercase">{{ data_get($chart, 'change') }}</span>
            </div>
            <span class="mb-1.5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ data_get($chart, 'change_label') }}</span>
        </div>

        <div class="relative h-[250px] w-full" 
             x-data="{
                 data: {{ json_encode(array_values(data_get($chart, 'data', [0, 0, 0, 0, 0, 0, 0]))) }},
                 width: 472,
                 height: 150,
                 padding: 25,
                 get points() {
                     if (!this.data || !this.data.length) return [];
                     let max = Math.max(...this.data);
                     if (max === 0) max = 1;
                     return this.data.map((val, i) => {
                         let x = (i / (this.data.length - 1)) * this.width;
                         let y = this.height - (val / max) * (this.height - this.padding * 2) - this.padding;
                         return {x, y};
                     });
                 },
                 get curvedPath() {
                     let pts = this.points;
                     if (pts.length < 2) return '';
                     let d = `M ${pts[0].x} ${pts[0].y}`;
                     for (let i = 0; i < pts.length - 1; i++) {
                         let x0 = i ? pts[i - 1].x : pts[0].x;
                         let y0 = i ? pts[i - 1].y : pts[0].y;
                         let x1 = pts[i].x;
                         let y1 = pts[i].y;
                         let x2 = pts[i + 1].x;
                         let y2 = pts[i + 1].y;
                         let x3 = i + 2 < pts.length ? pts[i + 2].x : x2;
                         let y3 = i + 2 < pts.length ? pts[i + 2].y : y2;
                         
                         let cp1x = x1 + (x2 - x0) / 6;
                         let cp1y = y1 + (y2 - y0) / 6;
                         let cp2x = x2 - (x3 - x1) / 6;
                         let cp2y = y2 - (y3 - y1) / 6;
                         
                         d += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${x2} ${y2}`;
                     }
                     return d;
                 },
                 get fillPath() {
                     let d = this.curvedPath;
                     if (!d) return '';
                     return d + ` L ${this.width} ${this.height} L 0 ${this.height} Z`;
                 }
             }">
            <svg class="h-full w-full" preserveAspectRatio="none" viewBox="0 0 472 150">
                <defs>
                    <linearGradient id="chartGradient" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#2463eb" stop-opacity="0.25"></stop>
                        <stop offset="100%" stop-color="#2463eb" stop-opacity="0"></stop>
                    </linearGradient>
                </defs>
                <path x-bind:d="fillPath" fill="url(#chartGradient)"></path>
                <path x-bind:d="curvedPath" fill="none" stroke="#2463eb" stroke-linecap="round" stroke-width="4" filter="drop-shadow(0 4px 6px rgba(36,99,235,0.2))"></path>
            </svg>
        </div>

        <div class="mt-6 flex justify-between px-2">
            @foreach (data_get($chart, 'days', ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']) as $day)
                <span class="text-[10px] font-black tracking-widest {{ $day === 'WED' ? 'text-primary' : 'text-slate-400 dark:text-slate-500' }}">{{ $day }}</span>
            @endforeach
        </div>
    </div>
</div>
