<?php

namespace Tests\Feature\Automations;

use App\Jobs\ProcessAutomationNode;
use App\Models\AutomationFlow;
use App\Models\AutomationNode;
use App\Models\AutomationRun;
use App\Models\Company;
use App\Models\User;
use App\Services\Automations\AutomationRunnerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AutomationQueueTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        $this->actingAs($this->user);
    }

    public function test_trigger_starts_async_run()
    {
        Queue::fake();

        $flow = AutomationFlow::create([
            'company_id' => $this->company->id,
            'name' => 'Test Flow',
            'status' => 'active'
        ]);

        $triggerNode = AutomationNode::create([
            'automation_flow_id' => $flow->id,
            'type' => 'trigger',
            'subtype' => 'webhook_api',
            'label' => 'Incoming Webhook',
            'config' => []
        ]);

        $actionNode = AutomationNode::create([
            'automation_flow_id' => $flow->id,
            'type' => 'action',
            'subtype' => 'whatsapp_message',
            'label' => 'WhatsApp Message',
            'config' => ['recipient_expression' => '{{trigger.phone}}']
        ]);

        // Connect them
        $flow->connections()->create([
            'source_node_id' => $triggerNode->id,
            'target_node_id' => $actionNode->id,
        ]);

        $run = AutomationRun::create([
            'automation_flow_id' => $flow->id,
            'company_id' => $this->company->id,
            'trigger_node_id' => $triggerNode->id,
            'trigger_context' => ['phone' => '+1234567890'],
        ]);

        app(AutomationRunnerService::class)->executeRun($run);

        Queue::assertPushed(ProcessAutomationNode::class, function ($job) use ($run, $triggerNode) {
            return $job->run->id === $run->id && $job->nodeId === $triggerNode->id;
        });

        $this->assertEquals('running', $run->fresh()->status);
    }

    public function test_wait_node_schedules_delayed_job()
    {
        Queue::fake();

        $flow = AutomationFlow::create(['company_id' => $this->company->id, 'name' => 'Delay Flow']);
        
        $waitNode = AutomationNode::create([
            'automation_flow_id' => $flow->id,
            'type' => 'wait',
            'subtype' => 'wait_delay',
            'label' => 'Wait 10m',
            'config' => ['delay_value' => 10, 'delay_unit' => 'minutes']
        ]);

        $nextNode = AutomationNode::create([
            'automation_flow_id' => $flow->id,
            'type' => 'action',
            'subtype' => 'whatsapp_message',
            'label' => 'Next Message',
            'config' => []
        ]);

        $flow->connections()->create([
            'source_node_id' => $waitNode->id,
            'target_node_id' => $nextNode->id,
        ]);

        $run = AutomationRun::create([
            'automation_flow_id' => $flow->id,
            'company_id' => $this->company->id,
            'status' => 'running',
            'context' => [],
            'step_count' => 1
        ]);

        app(AutomationRunnerService::class)->processNodeStep($run, $waitNode->id);

        Queue::assertPushed(ProcessAutomationNode::class, function ($job) use ($nextNode) {
            return $job->nodeId === $nextNode->id && !is_null($job->delay);
        });

        $this->assertEquals('delayed', $run->fresh()->status);
    }

    public function test_loop_protection_halts_run()
    {
        Queue::fake();

        $flow = AutomationFlow::create(['company_id' => $this->company->id, 'name' => 'Loop Flow']);
        $node = AutomationNode::create([
            'automation_flow_id' => $flow->id,
            'type' => 'action',
            'subtype' => 'whatsapp_message',
            'label' => 'Loop Node',
            'config' => []
        ]);

        $run = AutomationRun::create([
            'automation_flow_id' => $flow->id,
            'company_id' => $this->company->id,
            'status' => 'running',
            'step_count' => 50 // At the limit
        ]);

        app(AutomationRunnerService::class)->processNodeStep($run, $node->id);

        $this->assertEquals('failed', $run->fresh()->status);
        $this->assertStringContainsString('Maximum step execution limit', $run->fresh()->last_error);
        
        Queue::assertNotPushed(ProcessAutomationNode::class);
    }
}
