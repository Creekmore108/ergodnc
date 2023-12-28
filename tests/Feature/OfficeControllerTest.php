<?php

namespace Tests\Feature;

 use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\OfficePendingApproval;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OfficeControllerTest extends TestCase
{
    // use RefreshDatabase;
    use LazilyRefreshDatabase;

    /**
     * @test
     */
    public function ListAllOfficeInPaginatedFormat(): void
    {
        $user = User::factory()->create();
        $tags = Tag::factory(2)->create();
        $tags2 = Tag::factory(2)->create();

        Office::factory(2)->for($user)->create();

        Office::factory()->for($user)->hasAttached($tags)->create();
        Office::factory()->hasAttached($tags2)->create();

        $response = $this->get('/api/offices');

        // dd($response->json());

        // $response->assertOk();
            // ->assertJsonStructure(['data', 'meta', 'links']);
            // ->assertJsonCount(20, 'data');
            // ->assertJsonStructure(['data' => ['*' => ['id', 'title']]]);
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
        // $response->assertJsonCount(3, 'data');
    }

    /**
     * @test
     */
    public function FilterByUserId()
    {
        Office::factory(3)->create();

        $host = User::factory()->create();
        $office = Office::factory()->for($host)->create();

        $response = $this->get('/api/offices?user_id='.$host->id);

        // $response->assertOk();
        $response->assertJsonCount(1,'data');
        $this->assertEquals($office->id, $response->json('data')[0]['id']);
    }

    /**
     * @test
     */
    public function FiltersByVisitorId()
    {
        Office::factory(3)->create();

        $user = User::factory()->create();
        $office = Office::factory()->create();

        Reservation::factory()->for(Office::factory())->create();
        Reservation::factory()->for($office)->for($user)->create();

        $response = $this->get('/api/offices?vistor_id='.$user->id);

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
        // $this->assertEquals($user->id, $response->json('data')[0]['user']['id']);

    }

     /**
     * @test
     */
    public function ReturnsTheNumberOfActiveReservationd()
    {

        $office = Office::factory()->create();

        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_CANCELLED]);

        $response = $this->get(
            '/api/offices'
        );
        $response->assertOk();
        // $this->assertEquals(1, $response->json('data')[0]['reservations_count']);
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

        $response->assertOk();
            // ->assertJsonPath('data.reservations_count', 1)
            // ->assertJsonCount(1, 'data.tags')
            // ->assertJsonCount(1, 'data.images')
            // ->assertJsonPath('data.user.id', $user->id);
    }

    /**
     * @test
     */
    public function CreatesAnOffice()
    {
        // Notification::fake();

         // $admin = User::factory()->create(['is_admin' => true]);

        $user = User::factory()->create();
        $tags = Tag::factory(2)->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/offices', Office::factory()->raw([
            'title' => 'My New Offcie',
            'tags' => $tags->pluck('id')->toArray()
        ]));

        dd(
            $response->json()
        );

        // $response = $this->postJson('/api/offices', [
        //     'title' => 'Office in Colorado',
        //     'description' => 'Description HERE',
        //     'lat' => '39.888888888',
        //     'lng' => '-8.838483484',
        //     'address_line1' => '123 Main st.',
        //     'price_per_day' => 10_000,
        //     'monthly_discount' => 5,
        //     'tags' => [
        //         $tag->id, $tag2->id
        //     ]
        // ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'My New Offcie')
            ->assertJsonPath('data.APPROVAL_STATUS', Office::APPROVAL_PENDING)
            ->assertJsonPath('data.reservations_count', 0)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonCount(2, 'data.tags');

        // dd($response->json());

        // $response->dump();

        // Sanctum::actingAs($user, ['*']);

        // $response = $this->postJson('/offices', Office::factory()->raw([
        //     'tags' => $tags->pluck('id')->toArray()
        // ]));

        // $this->assertDatabaseHas('offices', [
        //     'id' => $response->json('data.id')
        // ]);

        // Notification::assertSentTo($admin, OfficePendingApproval::class);
    }

    /**
     * @test
     */
    public function DoesntAllowCreatingIfScopeIsNotProvided()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test',[]);

        // Sanctum::actingAs($user, []);

        $response = $this->postJson('/api/offices', [],[
            'Authorization' => 'Bearer '.$token->plainTextToken
        ]);

        $response->assertStatus(403);

        // dd(
        //     $response->json()
        // );

        // $response->assertCreated()
        //     ->assertJsonPath('data.title', 'Office in Colorado')
        //     ->assertJsonPath('data.APPROVAL_STATUS', Office::APPROVAL_PENDING)
        //     ->assertJsonPath('data.reservations_count', 0);

        // $response->assertForbidden();
    }

    /**
     * @test
     */
    public function UpdateAnOffice()
    {

        $user = User::factory()->create();
        $tags = Tag::factory(2)->create();
        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tags);

        $this->actingAs($user);

        $response = $this->postJson('/api/offices', Office::factory()->raw([
            'title' => 'My New Office',
            'tags' => $tags->pluck('id')->toArray()
        ]));



        // $response = $this->putJson('/api/offices/'.$office->id, [
        //     'title' => 'Amazing Office',
        //     // 'address_label2' => 'test address',
        // ]);

        // dd(
        //     $response->json()
        // );

        $response->assertCreated()
            ->assertJsonPath('data.title', 'My New Office');
    }

     /**
     * @test
     */
    public function DoesntUpdateOfficeThatDoesntBelongToUser()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $office = Office::factory()->for($anotherUser)->create();

        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id, [
            'title' => 'Amazing Office'
        ]);
        // dd(
        //     $response
        // );

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        // $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function MarksTheOfficeAsPendingIfDirty()
    {
        // $admin = User::factory()->create(['is_admin' => true]);

        // Notification::fake();

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id, [
            'lat' => 40.74051727562952
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('offices', [
            'id' => $office->id,
            'approval_status' => Office::APPROVAL_PENDING,
        ]);

        // Notification::assertSentTo($admin, OfficePendingApproval::class);
    }

     /**
     * @test
     */
    public function CanDeleteOffices()
    {
        // Storage::put('/office_image.jpg', 'empty');

        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        // $image = $office->images()->create([
        //     'path' => 'office_image.jpg'
        // ]);

        $this->actingAs($user);

        $response = $this->deleteJson('/api/offices/'.$office->id);


        $response->assertOk();

        $this->assertSoftDeleted($office);

        // $this->assertModelMissing($image);

        // Storage::assertMissing('office_image.jpg');
    }

    /**
     * @test
     */
    public function CannotDeleteAnOfficeThatHasReservations()
    {
        $user = User::factory()->create();
        $office = Office::factory()->for($user)->create();

        Reservation::factory(3)->for($office)->create();

        $this->actingAs($user);

        $response = $this->deleteJson('/api/offices/'.$office->id);

        $response->assertUnprocessable();

        $this->assertNotSoftDeleted($office);
    }

}
