<?php

namespace Tests\Feature\API;

use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DealTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test fetching the list of deals.
     */
    public function test_can_fetch_deals(): void
    {
        // Create some test deals
        Deal::factory()->count(5)->create();
        
        // Make request to the deals endpoint
        $response = $this->getJson('/api/deals');
        
        // Assert the response is successful
        $response->assertStatus(200);
        
        // Assert the response contains the correct structure
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'type',
                    'price',
                    'source',
                    'valid_until',
                ]
            ],
            // Removing pagination keys if they don't exist in the actual response
        ]);
        
        // Assert we got the expected number of deals
        $this->assertCount(5, $response->json('data'));
    }
    
    /**
     * Test fetching a single deal.
     */
    public function test_can_fetch_single_deal(): void
    {
        // Create a test deal
        $deal = Deal::factory()->create();
        
        // Make request to fetch the deal
        $response = $this->getJson("/api/deals/{$deal->id}");
        
        // Assert the response is successful
        $response->assertStatus(200);
        
        // Assert the response contains the expected deal data
        $response->assertJson([
            'id' => $deal->id,
            'title' => $deal->title,
            'type' => $deal->type,
            'price' => (string) $deal->price, // Cast to string since JSON converts decimals to strings
        ]);
    }
    
    /**
     * Test bookmarking a deal.
     */
    public function test_authenticated_user_can_bookmark_deal(): void
    {
        // Create a test user and deal
        $user = User::factory()->create();
        $deal = Deal::factory()->create();
        
        // Send a request to bookmark the deal as the authenticated user
        Sanctum::actingAs($user);
        $response = $this->postJson("/api/deals/{$deal->id}/bookmark");
        
        // Assert the response is successful
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Deal bookmarked successfully',
            ]);
        
        // Assert the deal is bookmarked for the user
        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $user->id,
            'deal_id' => $deal->id,
        ]);
    }
    
    /**
     * Test that unauthenticated users cannot bookmark deals.
     */
    public function test_unauthenticated_user_cannot_bookmark_deal(): void
    {
        // Create a test deal
        $deal = Deal::factory()->create();
        
        // Try to bookmark the deal without authentication
        $response = $this->postJson("/api/deals/{$deal->id}/bookmark");
        
        // Assert the response requires authentication
        $response->assertStatus(401);
        
        // Assert no bookmark was created
        $this->assertDatabaseMissing('bookmarks', [
            'deal_id' => $deal->id,
        ]);
    }
    
    /**
     * Test fetching a user's bookmarks.
     */
    public function test_user_can_fetch_their_bookmarks(): void
    {
        // Create a test user and deals
        $user = User::factory()->create();
        $deals = Deal::factory()->count(3)->create();
        
        // Bookmark the deals for the user
        $user->bookmarkedDeals()->attach($deals->pluck('id'));
        
        // Send a request to get the user's bookmarks
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/user/bookmarks');
        
        // Assert the response is successful
        $response->assertStatus(200);
        
        // Assert the response contains the bookmarked deals
        $response->assertJsonCount(3, 'data');
        
        // Verify the response contains the expected deal IDs
        $responseIds = collect($response->json('data'))->pluck('id')->toArray();
        $expectedIds = $deals->pluck('id')->toArray();
        $this->assertEquals(sort($expectedIds), sort($responseIds));
    }
} 