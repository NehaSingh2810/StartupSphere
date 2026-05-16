@extends('layouts.app')
@section('content')
@php($role = session('startup_user.role', 'Startup Founder'))
<div class="dash">
    @include('partials.dashboard-nav')
    <section>
        <div class="topline">
            <div><h1>Welcome Back, {{ session('startup_user.name') }}</h1><p>Manage your StartupSphere activity from this workspace.</p></div>
            <span class="tag">{{ $role }}</span>
        </div>

        <div class="panel" style="margin-bottom: 24px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <strong style="font-size:14px;">Profile 80% Complete</strong>
                <span style="font-size:13px; color:var(--muted);">Add photo, bio, city, skills</span>
            </div>
            <div class="bar"><span style="width:80%"></span></div>
        </div>

        @if($role === 'Admin')
            <div class="grid cards" style="margin-bottom: 32px;">
                <div class="card"><h3>Users</h3><p>Manage platform users.</p><a class="btn light" href="/dashboard/users">Open</a></div>
                <div class="card"><h3>Events</h3><p>Manage startup events.</p><a class="btn light" href="/dashboard/events">Open</a></div>
                <div class="card"><h3>Feedback</h3><p>Read feedback submissions.</p><a class="btn light" href="/dashboard/feedback">Open</a></div>
                <div class="card"><h3>Reports</h3><p>View system reports.</p><a class="btn light" href="/dashboard/reports">Open</a></div>
            </div>
        @elseif($role === 'Startup Founder')
            <div class="grid cards" style="margin-bottom: 32px;">
                <div class="card"><h3>My Startup</h3><p>Edit profile details.</p><a class="btn light" href="/dashboard/my-startup">Open</a></div>
                <div class="card"><h3>My Events</h3><p>Registered events.</p><a class="btn light" href="/dashboard/my-events">Open</a></div>
                <div class="card"><h3>Saved Startups</h3><p>Startups you saved.</p><a class="btn light" href="/dashboard/saved-startups">Open</a></div>
                <div class="card"><h3>Reviews</h3><p>Read event reviews.</p><a class="btn light" href="/dashboard/reviews">Open</a></div>
            </div>

        @else
            <div class="grid cards" style="margin-bottom: 32px;">
                <div class="card"><h3>Upcoming Events</h3><p>Register for events.</p><a class="btn light" href="/dashboard/browse-events">Open</a></div>
                <div class="card"><h3>Registered Events</h3><p>Your bookings.</p><a class="btn light" href="/dashboard/registered-events">Open</a></div>
                <div class="card"><h3>Saved Events</h3><p>Events you bookmarked.</p><a class="btn light" href="/dashboard/saved-startups">Open</a></div>
                <div class="card"><h3>Certificates</h3><p>Workshop certificates.</p><a class="btn light" href="/dashboard/certificates">Open</a></div>
            </div>
        @endif

        <div class="section-head"><div><h2>Upcoming Events</h2><p>Main feature of the platform.</p></div><a class="btn light" href="/events">Public Events</a></div>
        <div class="grid three">@foreach($events as $event) @include('partials.event-card', ['event' => $event]) @endforeach</div>
    </section>
</div>
@endsection
