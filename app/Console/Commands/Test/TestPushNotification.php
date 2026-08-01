<?php

namespace App\Console\Commands\Test;

use App\Models\UserDeviceToken;
use App\Services\Notifications\MobilePushNotificationService;
use Illuminate\Console\Command;

class TestPushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:push {company_id} {--token=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify push token database records, credentials, and test mobile push notifications';

    /**
     * Execute the console command.
     */
    public function handle(MobilePushNotificationService $pushService)
    {
        $companyId = (int) $this->argument('company_id');
        $customToken = $this->option('token');

        $this->info("==============================================================");
        $this->info("   Mobile Push Notification Diagnostics (Company ID: {$companyId})");
        $this->info("==============================================================");

        // Step 1: Check Database Tokens
        $this->comment("\n[Step 1] Checking device token records in database...");
        if ($customToken) {
            $tokens = collect([$customToken]);
            $this->info("-> Using custom device token passed via command option.");
        } else {
            $tokens = UserDeviceToken::where('company_id', $companyId)
                ->whereNotNull('device_token')
                ->pluck('device_token');
            $this->info("-> Found " . $tokens->count() . " registered device token(s) for this company.");
            
            if ($tokens->isEmpty()) {
                $this->warn("-> WARNING: No device tokens are registered in your database for Company ID {$companyId}.");
                $this->line("   Possible Cause: The mobile app developer has not implemented or successfully called");
                $this->line("   the registration API endpoint (POST /api/v1/devices/token) after fetching the FCM token.");
            } else {
                foreach ($tokens as $index => $tok) {
                    $this->line("   Token #" . ($index + 1) . ": " . substr($tok, 0, 30) . "...");
                }
            }
        }

        // Step 2: Check Credentials Configuration
        $this->comment("\n[Step 2] Checking Firebase / FCM Server configuration...");
        $credentialsPath = config('services.firebase.credentials') ?? env('FIREBASE_CREDENTIALS', 'storage/app/firebase/service-account.json');
        
        $isAbsolute = str_starts_with($credentialsPath, '/') || str_starts_with($credentialsPath, '\\') || (strlen($credentialsPath) > 1 && $credentialsPath[1] === ':');
        if (!$isAbsolute) {
            $credentialsPath = base_path($credentialsPath);
        }

        $hasServiceAccount = file_exists($credentialsPath);
        $this->info("-> Firebase Service Account file: " . ($hasServiceAccount ? "FOUND" : "NOT FOUND"));
        $this->line("   Path checked: {$credentialsPath}");

        $legacyKey = config('services.fcm.server_key') ?? env('FCM_SERVER_KEY');
        $this->info("-> Legacy FCM Server Key (.env): " . ($legacyKey ? "CONFIGURED" : "NOT CONFIGURED"));

        if (!$hasServiceAccount && !$legacyKey) {
            $this->error("-> ERROR: Neither FCM v1 Service Account JSON file nor Legacy Server Key is configured.");
            $this->line("   Please add FIREBASE_CREDENTIALS or FCM_SERVER_KEY to your server's .env file.");
            return 1;
        }

        // Step 3: Check Queue Worker Status
        $this->comment("\n[Step 3] Checking Queue Worker status...");
        $queueConnection = env('QUEUE_CONNECTION', 'sync');
        $this->info("-> Queue Connection (.env): {$queueConnection}");
        if ($queueConnection !== 'sync') {
            $this->line("   Note: Push notifications are sent via a background Queue Job.");
            $this->line("   Make sure 'php artisan queue:work' or 'php artisan queue:listen' is active on the server.");
        } else {
            $this->line("   Note: Queue is in 'sync' mode; jobs will process instantly without a queue worker.");
        }

        // Step 4: Perform Send Test
        if ($tokens->isEmpty()) {
            $this->comment("\n[Step 4] Skipping sending phase since no target tokens exist.");
            $this->info("\nDiagnostics complete.");
            return 0;
        }

        $this->comment("\n[Step 4] Attempting to send test push notification...");
        $result = $pushService->sendPushToTokens(
            $tokens,
            "⚡ Test Push Notification",
            "This is a diagnostic push message sent at " . now()->toDateTimeString(),
            ['click_action' => 'TEST_ACTION', 'diagnostic' => 'true']
        );

        $this->info("\n=== Delivery Summary ===");
        $this->info("Sent successfully: " . ($result['sent'] ?? 0));
        $this->info("Failed:            " . ($result['failed'] ?? 0));
        if (isset($result['status'])) {
            $this->info("Status code/error: " . $result['status']);
        }

        $this->info("\nDiagnostics complete.");
        return 0;
    }
}
