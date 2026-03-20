<?php

namespace Tests\Feature\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class SimpleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function database_is_sqlite()
    {
        $driver = DB::connection()->getDriverName();
        $this->assertEquals('sqlite', $driver);
    }

    /** @test */
    public function home_route_is_found()
    {
        $this->get('/')->assertStatus(200)->assertSee('Register');
    }

    /** @test */
    public function debug_route_is_found()
    {
        $this->get('/debug-route')->assertSee('ok');
    }
}
