<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductionJobApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_get_production_jobs_options_returns_json_response(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/admin/production/jobs/options');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson([
                'success' => true,
                'message' => 'Production job options retrieved successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'statuses',
                    'queues',
                    'priorities',
                    'job_types',
                ],
            ]);
    }

    /** @test */
    public function test_get_production_jobs_by_id_returns_json_response(): void
    {
        Sanctum::actingAs($this->user);

        $jobId = DB::table('jobs')->insertGetId([
            'queue' => 'default',
            'payload' => json_encode(['job' => 'TestJob']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ]);

        $response = $this->getJson("/api/v1/admin/production/jobs/{$jobId}");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson([
                'success' => true,
                'message' => 'Production job retrieved successfully.',
            ])
            ->assertJsonPath('data.id', $jobId);
    }

    /** @test */
    public function test_get_production_jobs_options_is_not_intercepted_by_job_id_param(): void
    {
        Sanctum::actingAs($this->user);

        // Call options route
        $optionsResponse = $this->getJson('/api/v1/admin/production/jobs/options');
        $optionsResponse->assertStatus(200)
            ->assertJsonPath('data.statuses.0', 'pending');

        // Ensure options is not treated as a numeric ID in show route
        $this->assertNotEquals('options', $optionsResponse->json('data.id'));
    }

    /** @test */
    public function test_non_existent_production_job_id_returns_404_json_not_html(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/admin/production/jobs/99999');

        $response->assertStatus(404)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson([
                'success' => false,
                'message' => 'Production job not found.',
            ]);
    }
}
