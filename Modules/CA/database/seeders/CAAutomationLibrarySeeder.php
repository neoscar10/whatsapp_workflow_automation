<?php

namespace Modules\CA\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CA\Services\AutomationLibraryService;

class CAAutomationLibrarySeeder extends Seeder
{
    public function run(): void
    {
        app(AutomationLibraryService::class)->seedDefaultLibrary();
        $this->command->info('CA Automation Library seeded successfully.');
    }
}
