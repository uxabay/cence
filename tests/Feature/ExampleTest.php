<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A guest user is redirected from the home page.
     */
    public function test_guest_is_redirected_from_home(): void
    {
        $response = $this->get('/');

        $response->assertRedirect();
    }

    /**
     * The admin panel requires authentication.
     */
    public function test_admin_requires_authentication(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect();
    }
}
