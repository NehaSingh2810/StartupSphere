<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_public_pages_are_available(): void
    {
        foreach (['/about', '/startups', '/events', '/mentors', '/investors', '/blogs', '/faq', '/contact'] as $path) {
            $this->get($path)->assertStatus(200);
        }
    }

    public function test_dashboard_requires_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_demo_user_can_login(): void
    {
        $this->post('/login', [
            'email' => 'demo@startupsphere.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');
    }
}
