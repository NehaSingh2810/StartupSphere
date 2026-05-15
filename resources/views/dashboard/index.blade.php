@extends('layouts.app')
@section('content')
@php($role = session('startup_user.role', 'Startup Founder'))
<div class="dash">
    @include('partials.dashboard-nav')
    <section>
        <div class="topline">
            <div><h1>{{ $role }} Dashboard</h1><p>Welcome, {{ session('startup_user.name') }}. Manage your StartupSphere activity from this role-based workspace.</p></div>
            <span class="tag">{{ $role }}</span>
        </div>

        <div class="stats">
            <div class="card"><div class="metric">{{ $stats['events'] }}</div><p>Events</p></div>
            <div class="card"><div class="metric">{{ $stats['startups'] }}</div><p>Startups</p></div>
            <div class="card"><div class="metric">{{ $stats['mentors'] }}</div><p>Mentors</p></div>
            <div class="card"><div class="metric">{{ $stats['investors'] }}</div><p>Investors</p></div>
            <div class="card"><div class="metric">{{ count($notifications) }}</div><p>Notifications</p></div>
        </div>

        <div class="panel">
            <h2>Project Flow</h2>
            <p>StartupSphere is a role-based event discovery platform. Public users browse startup events, startups, mentors, and investors. Logged-in users manage activity according to role: Admin, Startup Founder, or Investor.</p>
        </div>

        @if($role === 'Admin')
            <div class="grid three">
                <div class="card"><h3>Manage Events</h3><p>Add, edit, and review startup event listings.</p><a class="btn light" href="/dashboard/events">Open Events</a></div>
                <div class="card"><h3>Approve Startups</h3><p>Review startup profiles before showing them in the directory.</p><a class="btn light" href="/dashboard/startups">Open Startups</a></div>
                <div class="card"><h3>User and Feedback</h3><p>Manage platform users and read feedback submissions.</p><a class="btn light" href="/dashboard/users">Open Users</a></div>
            </div>
        @elseif($role === 'Startup Founder')
            <div class="grid three">
                <div class="card"><h3>My Startup</h3><p>Create and update startup profile details.</p><a class="btn light" href="/dashboard/my-startup">Edit Startup</a></div>
                <div class="card"><h3>My Events</h3><p>Register for pitch events, workshops, and hackathons.</p><a class="btn light" href="/dashboard/my-events">View Events</a></div>
                <div class="card"><h3>Investor Requests</h3><p>See investors who marked interest in your startup.</p><a class="btn light" href="/dashboard/investor-requests">View Requests</a></div>
            </div>
        @elseif($role === 'Investor')
            <div class="grid three">
                <div class="card"><h3>Browse Startups</h3><p>Search startup profiles by industry, stage, city, and rating.</p><a class="btn light" href="/dashboard/browse-startups">Browse</a></div>
                <div class="card"><h3>Interested Startups</h3><p>Track startups you marked Interested.</p><a class="btn light" href="/dashboard/interested-startups">View List</a></div>
                <div class="card"><h3>Pitch Events</h3><p>Attend startup pitch events and funding workshops.</p><a class="btn light" href="/dashboard/events">View Events</a></div>
            </div>
        @elseif($role === 'Mentor')
            <div class="grid three">
                <div class="card"><h3>My Sessions</h3><p>Manage mentorship sessions and workshop participation.</p><a class="btn light" href="/dashboard/my-sessions">Open Sessions</a></div>
                <div class="card"><h3>Startup Requests</h3><p>Accept or review startup guidance requests.</p><a class="btn light" href="/dashboard/startup-requests">View Requests</a></div>
                <div class="card"><h3>Events</h3><p>Attend webinars, workshops, and mentor panels.</p><a class="btn light" href="/dashboard/events">View Events</a></div>
            </div>
        @else
            <div class="grid three">
                <div class="card"><h3>Browse Events</h3><p>Register for startup events, webinars, and hackathons.</p><a class="btn light" href="/dashboard/browse-events">Browse Events</a></div>
                <div class="card"><h3>Saved Startups</h3><p>Bookmark interesting startups for later learning.</p><a class="btn light" href="/dashboard/saved-startups">View Saved</a></div>
                <div class="card"><h3>Certificates</h3><p>View certificates earned after workshops.</p><a class="btn light" href="/dashboard/certificates">Open Certificates</a></div>
            </div>
        @endif

        <div class="section-head"><div><h2>Upcoming Events</h2><p>Main feature of the platform.</p></div><a class="btn light" href="/events">Public Events</a></div>
        <div class="grid three">@foreach($events as $event) @include('partials.event-card', ['event' => $event]) @endforeach</div>
    </section>
</div>
@endsection
