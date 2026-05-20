<?php
namespace Tests\Feature;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_public_pages_are_available(): void
    {
        foreach (['/about', '/startups', '/events', '/investors', '/blogs', '/faq', '/contact'] as $path) {
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

    public function test_admin_credentials_can_login(): void
    {
        $this->post('/login', [
            'email' => '123@gmail.com',
            'password' => '1234567890',
        ])->assertRedirect('/dashboard');
    }

    public function test_admin_feedback_dashboard_is_removed(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'Nehaa', 'email' => '123@gmail.com', 'role' => 'Admin'],
        ])->get('/dashboard')
            ->assertDontSee('/dashboard/feedback')
            ->assertDontSee('Feedback');

        $this->withSession([
            'startup_user' => ['name' => 'Nehaa', 'email' => '123@gmail.com', 'role' => 'Admin'],
        ])->get('/dashboard/feedback')->assertNotFound();
    }

    public function test_investor_credentials_can_login(): void
    {
        $this->post('/login', [
            'email' => 'investor@startupsphere.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');
    }

    public function test_admin_cannot_register_for_events(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'Nehaa', 'email' => '123@gmail.com', 'role' => 'Admin'],
        ])->post('/events/startup-pitch-night/book')->assertSessionHas('status', 'Only users can register for events. Startup investors can send investment requests.');
    }

    public function test_notifications_are_hidden_from_user_and_startup_investor(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'Demo User', 'email' => 'user@example.com', 'role' => 'User'],
        ])->get('/dashboard')
            ->assertDontSee('/dashboard/notifications')
            ->assertDontSee('Notifications');

        $this->withSession([
            'startup_user' => ['name' => 'Startup Investor Demo', 'email' => 'investor@startupsphere.com', 'role' => 'Startup Investor'],
        ])->get('/dashboard')
            ->assertDontSee('/dashboard/notifications')
            ->assertDontSee('Notifications');
    }

    public function test_certificates_are_removed_from_user_section(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'Demo User', 'email' => 'user@example.com', 'role' => 'User'],
        ])->get('/dashboard')
            ->assertDontSee('/dashboard/certificates')
            ->assertDontSee('Certificates');

        $this->withSession([
            'startup_user' => ['name' => 'Demo User', 'email' => 'user@example.com', 'role' => 'User'],
        ])->get('/dashboard/certificates')->assertNotFound();
    }

    public function test_non_admin_cannot_open_notifications_page(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'Startup Investor Demo', 'email' => 'investor@startupsphere.com', 'role' => 'Startup Investor'],
        ])->get('/dashboard/notifications')
            ->assertRedirect('/dashboard')
            ->assertSessionHas('status', 'Notifications are available only for admin.');
    }

    public function test_only_fixed_admin_can_add_events(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'Fake Admin', 'email' => 'fake@example.com', 'role' => 'Admin'],
        ])->post('/dashboard/events', [
            'title' => 'Blocked Event',
            'category' => 'Workshop',
            'date' => '20 Jun 2026',
            'venue' => 'Campus',
            'city' => 'Mohali',
            'organizer' => 'StartupSphere',
            'seats' => 10,
        ])->assertSessionHas('status', 'Only admin Nehaa can add events.');
    }

    public function test_event_reviews_are_attached_to_event(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'Nehaa', 'email' => '123@gmail.com', 'role' => 'Admin'],
        ])->post('/reviews', [
            'event_slug' => 'startup-pitch-night',
            'rating' => 5,
            'comment' => 'Admin review for this exact event.',
        ])->assertSessionHas('status', 'Review submitted successfully.');

        $this->get('/events/startup-pitch-night')->assertSee('Admin review for this exact event.');
    }

    public function test_event_detail_shows_review_rating(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'user', 'email' => 'user@example.com', 'role' => 'User'],
            'reviews' => [
                ['event_slug' => 'fintech-expo', 'target' => 'FinTech Expo', 'rating' => 2, 'comment' => 'Needs clearer agenda.', 'user_email' => 'user@example.com'],
            ],
        ])->get('/events/fintech-expo')->assertSee('2/5');
    }

    public function test_user_profile_name_is_used_for_registration_notification_and_seat_decreases(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'Demo User', 'email' => 'user@example.com', 'role' => 'User', 'phone' => '9999999999', 'city' => 'Mohali', 'bio' => 'User account.', 'skills' => 'Events'],
        ])->post('/profile', [
            'name' => 'user',
            'phone' => '9999999999',
            'city' => 'Mohali',
            'bio' => 'User account.',
            'skills' => 'Events',
        ])->assertSessionHas('status', 'Profile updated successfully.');

        $before = $this->get('/events/startup-pitch-night')->getContent();
        preg_match('/(\d+) booked, (\d+) remaining from (\d+)/', $before, $beforeSeats);
        $this->assertNotEmpty($beforeSeats);

        $this->post('/events/startup-pitch-night/book')
            ->assertSessionHas('status', 'Event registered successfully.');

        $after = $this->get('/events/startup-pitch-night')->getContent();
        preg_match('/(\d+) booked, (\d+) remaining from (\d+)/', $after, $afterSeats);
        $this->assertNotEmpty($afterSeats);
        $this->assertSame((int) $beforeSeats[1] + 1, (int) $afterSeats[1]);
        $this->assertSame((int) $beforeSeats[2] - 1, (int) $afterSeats[2]);

        $this->assertStringContainsString('user (user@example.com, User, 9999999999, Mohali) registered for event Startup Pitch Night', session('admin_notifications.0'));

        $this->get('/dashboard/registered-events')
            ->assertSee('Registered')
            ->assertSee('Startup Pitch Night')
            ->assertDontSee('AI Innovation Summit');

        $this->withSession([
            'startup_user' => ['name' => 'Nehaa', 'email' => '123@gmail.com', 'role' => 'Admin'],
            'registrations' => session('registrations'),
        ])->get('/dashboard/notifications')->assertSee('user (user@example.com, User, 9999999999, Mohali) registered for event Startup Pitch Night');

        $this->withSession([
            'startup_user' => ['name' => 'Nehaa', 'email' => '123@gmail.com', 'role' => 'Admin'],
            'registrations' => session('registrations'),
        ])->get('/dashboard/registered-events')
            ->assertSee('Startup Pitch Night')
            ->assertSee('user@example.com');
    }

    public function test_startup_investor_event_request_notifies_admin(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'Startup Investor Demo', 'email' => 'investor@startupsphere.com', 'role' => 'Startup Investor'],
        ])->post('/events/startup-pitch-night/invest')
            ->assertSessionHas('status', 'Investment request sent to admin.');

        $this->assertStringContainsString('Startup Investor Demo wants to invest in event Startup Pitch Night', session('admin_notifications.0'));

        $this->withSession([
            'startup_user' => ['name' => 'Nehaa', 'email' => '123@gmail.com', 'role' => 'Admin'],
            'event_investment_requests' => session('event_investment_requests'),
            'admin_notifications' => session('admin_notifications'),
        ])->get('/dashboard/investor-requests')
            ->assertSee('Event Investment Requests')
            ->assertSee('Startup Pitch Night')
            ->assertSee('Startup Investor Demo')
            ->assertSee('investor@startupsphere.com');

        $this->withSession([
            'startup_user' => ['name' => 'Nehaa', 'email' => '123@gmail.com', 'role' => 'Admin'],
            'event_investment_requests' => session('event_investment_requests'),
        ])->get('/dashboard/registered-events')
            ->assertSee('Startup Investor Event Requests')
            ->assertSee('Startup Pitch Night')
            ->assertSee('Startup Investor Demo')
            ->assertSee('investor@startupsphere.com');
    }

    public function test_startup_investor_startup_request_notifies_admin(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'Startup Investor Demo', 'email' => 'investor@startupsphere.com', 'role' => 'Startup Investor'],
        ])->post('/startups/neuralx/interest')
            ->assertSessionHas('status', 'Investment interest sent to admin.');

        $this->assertStringContainsString('Startup Investor Demo wants to invest in startup NeuralX', session('admin_notifications.0'));
    }

    public function test_saved_startup_notifies_admin_with_user_details(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'user', 'email' => 'user@example.com', 'role' => 'User', 'phone' => '9999999999', 'city' => 'Mohali'],
        ])->post('/startups/neuralx/save')
            ->assertSessionHas('status', 'Startup saved to your dashboard.');

        $this->assertStringContainsString('user (user@example.com, User, 9999999999, Mohali) saved startup NeuralX', session('admin_notifications.0'));

        $this->get('/dashboard/saved-startups')
            ->assertSee('NeuralX')
            ->assertDontSee('PayNova');

        $this->withSession([
            'startup_user' => ['name' => 'Nehaa', 'email' => '123@gmail.com', 'role' => 'Admin'],
            'saved_startups_records' => session('saved_startups_records'),
        ])->get('/dashboard/notifications')->assertSee('user (user@example.com, User, 9999999999, Mohali) saved startup NeuralX');

        $this->withSession([
            'startup_user' => ['name' => 'Nehaa', 'email' => '123@gmail.com', 'role' => 'Admin'],
            'saved_startups_records' => session('saved_startups_records'),
        ])->get('/dashboard/saved-startups')
            ->assertSee('NeuralX')
            ->assertSee('user@example.com');
    }

    public function test_event_review_rating_reflects_on_listing_cards(): void
    {
        $this->withSession([
            'startup_user' => ['name' => 'user', 'email' => 'user@example.com', 'role' => 'User', 'phone' => '9999999999', 'city' => 'Mohali'],
            'reviews' => [
                ['event_slug' => 'fintech-expo', 'target' => 'FinTech Expo', 'rating' => 2, 'comment' => 'Needs clearer agenda.', 'user_email' => 'user@example.com'],
            ],
        ])->get('/events')->assertSee('2/5');

        $this->withSession([
            'startup_user' => ['name' => 'user', 'email' => 'user@example.com', 'role' => 'User', 'phone' => '9999999999', 'city' => 'Mohali'],
            'reviews' => [
                ['event_slug' => 'fintech-expo', 'target' => 'FinTech Expo', 'rating' => 2, 'comment' => 'Needs clearer agenda.', 'user_email' => 'user@example.com'],
            ],
        ])->get('/')->assertSee('2/5');
    }
}
