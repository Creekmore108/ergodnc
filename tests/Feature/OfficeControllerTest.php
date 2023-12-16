<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Office;

class OfficeControllerTest extends TestCase
{
    // use RefreshDatabase;
    use LazilyRefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        Office::factory()->count(3)->create();

        $response = $this->get('/api/offices');

        // dd(
        //     $response->json()
        // );
        $response->assertJsonCount(3, 'data');

        $response->assertOk()->dump();
    }
}
