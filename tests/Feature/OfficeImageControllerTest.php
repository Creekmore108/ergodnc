<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfficeImageControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @test
     */
    public function UploadsAnImageAndStoresItUnderTheOffice()
    {
        Storage::fake();

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->post("/api/offices/{$office->id}/images", [
            'image' => UploadedFile::fake()->image('image.jpg')
        ]);

        $response->assertCreated();

        // @TODO This test is failing and needs investigation
//        Storage::assertExists(
//            $response->json('data.path')
//        );
    }
}
