<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Root redirects to login when not logged in.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Authenticated resident is redirected to resident home.
     */
    public function test_authenticated_resident_is_redirected_to_home(): void
    {
        $resident = User::factory()->create(['role' => 'resident']);

        $response = $this->actingAs($resident)->get('/');

        $response->assertRedirect(route('resident.home'));
    }
}
