@extends('layouts.app')
@section('content')
<section class="hero">
    <div>
        <span class="tag">The Startup Ecosystem</span>
        <h1>Find Startup Events, Pitch Competitions, Hackathons, Investor Meets, and Workshops in One Place</h1>
        <p>Your centralized hub for discovering and participating in the startup ecosystem. Browse events by city, category, and date, register securely, and connect with founders and investors.</p>
        
        <div style="margin: 24px 0;">
            <p style="font-weight: 600; margin-bottom: 12px; color: var(--ink);">Explore Categories:</p>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                @foreach(['Pitch Competition', 'Hackathon', 'Workshop', 'Webinar', 'Investor Meetup', 'Incubation Program'] as $cat)
                    <a href="/events?category={{ urlencode($cat) }}" class="tag" style="background: white; border: 1px solid var(--line); color: var(--ink);">{{ $cat }}</a>
                @endforeach
            </div>
        </div>

        <p><a class="btn" href="/events">Explore Events</a> <a class="btn alt" href="/register">Join Now</a></p>
    </div>
    <div class="hero-art">
        <span class="tag verify">Startup event discovery</span>
        <h2>The ultimate platform for discovering and managing startup events.</h2>
    </div>
</section>

<div class="stats">
    <div class="card"><div class="metric">300+</div><p>Events Listed</p></div>
    <div class="card"><div class="metric">45</div><p>Events in Bangalore</p></div>
    <div class="card"><div class="metric">30</div><p>Events in Delhi</p></div>
    <div class="card"><div class="metric">25</div><p>Events in Mumbai</p></div>
    <div class="card"><div class="metric">800+</div><p>Active Users</p></div>
</div>



<section>
    <div class="section-head"><div><h2>Upcoming Events</h2><p>Search, filter, and register for startup events.</p></div><a class="btn light" href="/events">View All</a></div>
    <div class="grid cards">@foreach($events as $event) @include('partials.event-card', ['event' => $event]) @endforeach</div>
</section>

<section>
    <div class="section-head"><div><h2>Featured Startups</h2><p>Browse startup profiles by industry, city, rating, and funding stage.</p></div><a class="btn light" href="/startups">View Startups</a></div>
    <div class="grid cards">@foreach($startups as $startup) @include('partials.startup-card', ['startup' => $startup]) @endforeach</div>
</section>

<section>
    <div class="section-head"><div><h2>Top Mentors</h2><p>Find experienced mentors for workshops and startup guidance.</p></div><a class="btn light" href="/mentors">View Mentors</a></div>
    <div class="grid three">@foreach($mentors as $person)<div class="card"><span class="tag">{{ $person['expertise'] }}</span><h3>{{ $person['name'] }}</h3><p>{{ $person['experience'] }} experience | {{ $person['sessions'] }} sessions | Rating {{ $person['rating'] }}</p></div>@endforeach</div>
</section>

<section>
    <div class="section-head"><div><h2>Top Investors</h2><p>Discover investors who attend pitch events and review startup profiles.</p></div><a class="btn light" href="/investors">View Investors</a></div>
    <div class="grid three">@foreach($investors as $person)<div class="card"><span class="tag">{{ $person['industry'] ?? 'Startup' }}</span><h3>{{ $person['name'] }}</h3><p>Focus: {{ $person['expertise'] }} | Range {{ $person['experience'] }}</p></div>@endforeach</div>
</section>



<section>
    <div class="grid three">
        <div class="card"><h2>About StartupSphere</h2><p>A role-based platform for listing startup-related events and connecting ecosystem members.</p><a class="btn light" href="/about">About Us</a></div>
        <div class="card"><h2>Contact Us</h2><p>Phone: +91-9876543210<br>Email: support@startupsphere.com<br>Address: Mohali, Punjab, India</p><a class="btn light" href="/contact">Contact</a></div>
        <div class="card"><h2>Core Modules</h2><p>Events, startups, mentors, investors, registrations, reviews, feedback, and users.</p><a class="btn light" href="/register">Create Account</a></div>
    </div>
</section>
@endsection
