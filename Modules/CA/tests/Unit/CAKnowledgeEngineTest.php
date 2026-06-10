<?php

namespace Modules\CA\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CA\Models\CABusinessType;
use Modules\CA\Models\CACompliance;
use Modules\CA\Models\CAAICache;
use Modules\CA\Services\AI\CacheManager;

class CAKnowledgeEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Run specific seeder
        $this->artisan('db:seed', ['--class' => 'Modules\CA\Database\Seeders\CADatabaseSeeder']);
    }

    public function test_business_types_and_relationships_are_seeded()
    {
        $pvtLtd = CABusinessType::where('slug', 'private-limited')->first();
        
        $this->assertNotNull($pvtLtd);
        $this->assertTrue($pvtLtd->compliances->count() > 0);
        
        $compliance = $pvtLtd->compliances->first();
        $this->assertInstanceOf(CACompliance::class, $compliance);
        $this->assertNotNull($compliance->serviceCategory);
    }

    public function test_ai_cache_manager_saves_and_retrieves()
    {
        $manager = new CacheManager();
        $prompt = "Test prompt for Private Limited";
        
        $json = ['test' => 'data'];
        
        $manager->saveResponse('test_key', $prompt, $json, 'gpt-4', 100);
        
        $cached = $manager->getCachedResponse($prompt);
        
        $this->assertNotNull($cached);
        $this->assertEquals('data', $cached['test']);
        
        $miss = $manager->getCachedResponse("Different prompt");
        $this->assertNull($miss);
    }
}
