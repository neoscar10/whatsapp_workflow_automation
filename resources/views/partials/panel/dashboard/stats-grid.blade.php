<div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
    @foreach ($stats as $stat)
        @include('partials.panel.dashboard.stat-card', ['stat' => $stat])
    @endforeach
</div>
