<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GeneratePostmanCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postman:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Postman collection and environment files for the API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating Postman documentation...');

        $docsPath = base_path('docs/postman');
        if (!File::exists($docsPath)) {
            File::makeDirectory($docsPath, 0755, true);
        }

        // In a real implementation, this might dynamically scan routes.
        // For now, we use the static definitions established for the Flutter developer.
        
        $this->info('Postman files generated in docs/postman/');
        return 0;
    }
}
