<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\AutomationNode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AutomationWebhookController extends Controller
{
    /**
     * Handle incoming universal automation webhooks.
     */
    public function handle(Request $request, $uuid)
    {
        // Find the trigger node that owns this UUID
        // We search through the config JSON column
        $node = AutomationNode::where('type', 'trigger')
            ->where('config->webhook_uuid', $uuid)
            ->first();

        if (!$node) {
            return response()->json(['error' => 'Webhook target not found'], 404);
        }

        $config = $node->config;
        $secret = $config['webhook_secret'] ?? null;

        // Validate API Key if present in config
        if ($secret) {
            $providedKey = $request->header('X-Automation-Key') ?? $request->query('api_key');
            if ($providedKey !== $secret) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        $payload = $request->json()->all();

        // Detect variables from payload
        $detectedVariables = [];
        foreach ($payload as $key => $value) {
            $type = is_numeric($value) ? 'NUMBER' : (is_bool($value) ? 'BOOLEAN' : 'STRING');
            $detectedVariables[] = [
                'key' => $key,
                'type' => $type
            ];
        }

        // Update node config with last payload and detected variables
        $config['last_test_payload'] = $payload;
        $config['detected_variables'] = $detectedVariables;
        $config['last_received_at'] = now()->toDateTimeString();
        
        $node->config = $config;
        $node->save();

        // Fire the actual automation flow
        app(\App\Services\Automations\AutomationTriggerService::class)->fireTrigger($node, $payload);

        Log::info("Automation Webhook received and fired for node [{$node->id}]", ['payload' => $payload]);

        return response()->json([
            'status' => 'received_and_fired',
            'variables_detected' => count($detectedVariables)
        ], 200);
    }
}
