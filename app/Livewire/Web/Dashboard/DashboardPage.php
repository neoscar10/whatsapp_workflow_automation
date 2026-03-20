<?php

namespace App\Livewire\Web\Dashboard;

use App\Services\Dashboard\DashboardOverviewService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class DashboardPage extends Component
{
    public string $heading = '';
    public string $subheading = '';
    public array $storage = [];
    public array $topbarUser = [];
    public array $stats = [];
    public array $chart = [];
    public array $activities = [];
    public array $campaigns = [];

    public function mount(DashboardOverviewService $service)
    {
        $data = $service->getOverviewData(auth()->user());

        $this->heading = $data['heading'];
        $this->subheading = $data['subheading'];
        $this->storage = $data['storage'];
        $this->topbarUser = $data['topbarUser'];
        $this->stats = $data['stats'];
        $this->chart = $data['chart'];
        $this->activities = $data['activities'];
        $this->campaigns = $data['campaigns'];
    }

    #[Layout('layouts.panel')]
    #[Title('Dashboard - WhatsApp Cloud Panel')]
    public function render()
    {
        return view('livewire.web.dashboard.dashboard-page')
            ->with([
                'activeNav' => 'dashboard',
                'storage' => $this->storage,
                'topbarUser' => $this->topbarUser,
            ]);
    }
}
