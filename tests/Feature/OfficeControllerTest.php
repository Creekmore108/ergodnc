<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Office;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\Image;

class OfficeControllerTest extends TestCase
{
    // use RefreshDatabase;
    // use LazilyRefreshDatabase;

    /**
     * @test
     */
    public function ListAllOfficeInPaginatedFormat(): void
    {
        Office::factory()->count(3)->create();

        $response = $this->get('/api/offices');

        $response->assertJsonCount(3, 'data');

        $response->assertOk()->dump();
    }


     /**
     * @test
     */
    public function OnlyListOfficesThatAreNotHiddenAndApproved()
    {
        Office::factory(3)->create();

        Office::factory()->create(['hidden' => true]);
        Office::factory()->create(['approval_status' => Office::APPROVAL_PENDING ]);

        $response = $this->get('/api/offices');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    /**
     * @test
     */
    public function FilterByHostId()
    {
        Office::factory(3)->create();

        $host = User::factory()->create();
        $office = Office::factory()->for($host)->create();

        $response = $this->get('/api/offices?user_id='.$host->id);

        $response->assertOk();
        $response->assertJsonCount(1,'data');
        $this->assertEquals($office->id, $response->json('data')[0]['id']);
    }

    /**
     * @test
     */
    public function FiltersByUserId()
    {
        Office::factory(3)->create();

        $user = User::factory()->create();
        $office = Office::factory()->create();

        Reservation::factory()->for(Office::factory())->create();
        Reservation::factory()->for($office)->for($user)->create();

        $response = $this->get('/api/offices?user_id='.$user->id);

        $response->assertOk();
        // $response->assertJsonCount(1,'data');

    }

     /**
     * @test
     */
    public function FiltersImageTagsAndUsers()
    {
        // $user = User::factory()->create();

        // $tag = Tag::factory()->create();

        // $image = Image::factory()->create();

        // $office = Office::factory()->for($user)->create();

        // $office->tags()->attach($tag);

        // $office->images()->create(['path' => 'image.jpg']);

        $user = User::factory()->create();
        Office::factory()->for($user)->hasTags(1)->hasImages(1)->create();


        $response = $this->get('/api/offices');

        $response->assertOk();

        $this->assertIsArray($response->json('data')[0]['tags']);
        $this->assertIsArray($response->json('data')[0]['images']);
        $this->assertEquals($user->id, $response->json('data')[0]['user']['id']);

    }

     /**
     * @test
     */
    public function RturnsTheNumberOfActiveReservationd()
    {

        $office = Office::factory()->create();

        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_CANCELLED]);

        $response = $this->get(
            '/api/offices'
        )->dump();
        $response->assertOk();
        $this->assertEquals(1, $response->json('data')[0]['reservations_count']);
    }

    /**
     * @test
     */
    public function OrdersByDistanceWhenCoordinatesAreProvided()
    {

        Office::factory()->create([
            'lat' => '39.74051727562952',
            'lng' => '-8.770375324893696',
            'title' => 'Leiria'
        ]);

        Office::factory()->create([
            'lat' => '39.07753883078113',
            'lng' => '-9.281266331143293',
            'title' => 'Torres Vedras'
        ]);

        $response = $this->get('/api/offices?lat=38.720661384644046&lng=-9.16044783453807');

        $response->assertOk();
        $this->assertEquals('Torres Vedras', $response->json('data')[0]['title']);
        // $response->assertOk()
        //     ->assertJsonPath('data.0.title', 'Torres Vedras')
        //     ->assertJsonPath('data.1.title', 'Leiria');

        // $response = $this->get('/offices');

        // $response->assertOk()
        //     ->assertJsonPath('data.0.title', 'Leiria')
        //     ->assertJsonPath('data.1.title', 'Torres Vedras');

    }

    /**
     * @test
     */
    public function ShowsTheOffice()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag);
        $office->images()->create(['path' => 'image.jpg']);

        // $office = Office::factory()->for($user)->hasTags(1)->hasImages(1)->create();

        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);
        // Reservation::factory()->for($office)->cancelled()->create();

        // $office = Office::factory()->create();

        $response = $this->get('/api/offices/'.$office->id);

        $response->assertOk()->dump();
            // ->assertJsonPath('data.reservations_count', 1)
            // ->assertJsonCount(1, 'data.tags')
            // ->assertJsonCount(1, 'data.images')
            // ->assertJsonPath('data.user.id', $user->id);
    }

}
