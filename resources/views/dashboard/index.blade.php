@extends('layouts.app')
@section('content')
@php($role = session('startup_user.role', 'User'))
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
                <div class="card"><h3>Startups</h3><p>Manage startup listings.</p><a class="btn light" href="/dashboard/startups">Open</a></div>
                <div class="card"><h3>Investors</h3><p>View investor data.</p><a class="btn light" href="/dashboard/investors">Open</a></div>
                <div class="card"><h3>Notifications</h3><p>Review registrations and investor requests.</p><a class="btn light" href="/dashboard/notifications">Open</a></div>
                <div class="card"><h3>Registered Events</h3><p>Review user event registrations.</p><a class="btn light" href="/dashboard/registered-events">Open</a></div>
                <div class="card"><h3>Saved Startups</h3><p>Review startups saved by users.</p><a class="btn light" href="/dashboard/saved-startups">Open</a></div>
                <div class="card"><h3>Reports</h3><p>View system reports.</p><a class="btn light" href="/dashboard/reports">Open</a></div>
            </div>
        @elseif($role === 'User')
            <div class="grid cards" style="margin-bottom: 32px;">
                <div class="card"><h3>Upcoming Events</h3><p>Register for events.</p><a class="btn light" href="/dashboard/browse-events">Open</a></div>
                <div class="card"><h3>Browse Startups</h3><p>Find startups and save them.</p><a class="btn light" href="/dashboard/browse-startups">Open</a></div>
                <div class="card"><h3>Registered Events</h3><p>Your bookings.</p><a class="btn light" href="/dashboard/registered-events">Open</a></div>
                <div class="card"><h3>Saved Startups</h3><p>Startups you saved.</p><a class="btn light" href="/dashboard/saved-startups">Open</a></div>
                <div class="card"><h3>Reviews</h3><p>Read event reviews.</p><a class="btn light" href="/dashboard/reviews">Open</a></div>
            </div>

        @elseif($role === 'Startup Investor')
            <div class="grid cards" style="margin-bottom: 32px;">
                <div class="card"><h3>Browse Events</h3><p>Review events and request investment.</p><a class="btn light" href="/dashboard/browse-events">Open</a></div>
                <div class="card"><h3>Browse Startups</h3><p>Find startups and send interest to admin.</p><a class="btn light" href="/dashboard/browse-startups">Open</a></div>
                <div class="card"><h3>Requests</h3><p>Your requests notify admin for review.</p><a class="btn light" href="/dashboard/investor-requests">Open</a></div>
            </div>
        @else
            <div class="grid cards" style="margin-bottom: 32px;">
                <div class="card"><h3>Reviews</h3><p>Read event reviews.</p><a class="btn light" href="/dashboard/reviews">Open</a></div>
            </div>
        @endif

        <div class="section-head"><div><h2>Upcoming Events</h2><p>Main feature of the platform.</p></div><a class="btn light" href="/events">Public Events</a></div>
        <div class="grid three">@foreach($events as $event) @include('partials.event-card', ['event' => $event]) @endforeach</div>
    </section>
</div>
@endsection
