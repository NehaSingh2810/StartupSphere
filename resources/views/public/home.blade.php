@extends('layouts.app')
@section('content')
<section class="hero">
    <div>
        <span class="tag">Mobile-friendly Startup Platform</span>
        <h1>Discover Startup Events, Connect with Industry Experts, Grow Your Network</h1>
        <p>StartupSphere delivers a full-width web experience that adjusts to desktop and mobile screens. Admins can add startups, mentors, and investors while founders and investors use the dashboard by role.</p>
        <form class="search-box" method="get" action="/search">
            <input name="q" placeholder="Search pitch events, hackathons, startups, mentors">
            <select name="type"><option value="all">All</option><option value="events">Events</option><option value="startups">Startups</option><option value="people">Mentors/Investors</option></select>
            <button class="btn">Search</button>
        </form>
        <p><a class="btn" href="/events">Explore Events</a> <a class="btn alt" href="/register">Join Now</a></p>
        <p class="tag">{{ $mongoOnline ? 'MongoDB connected' : 'Demo data visible. Start MongoDB for live storage.' }}</p>
    </div>
    <div class="hero-art">
        <span class="tag verify">Startup event discovery</span>
        <h2>One platform for events, startups, mentors, investors, reviews, and feedback.</h2>
        <p style="color:white">Simple enough for viva, realistic enough for a product demo.</p>
    </div>
</section>

<div class="stats">
    @foreach(['300+ Events','150+ Startups','60+ Mentors','40+ Investors','800+ Users'] as $stat)
        <div class="card"><div class="metric">{{ explode(' ', $stat)[0] }}</div><p>{{ substr($stat, strpos($stat, ' ') + 1) }}</p></div>
    @endforeach
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
    <div class="section-head"><h2>Send Us Your Feedback</h2></div>
    <div class="grid">
        <form method="post" action="/feedback" style="max-width: 600px;">
            @csrf
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <input type="text" name="subject" placeholder="Subject" required>
            <textarea name="message" placeholder="Your feedback or suggestion..." rows="6" required></textarea>
            <button class="btn" type="submit">Send Feedback</button>
        </form>
    </div>
</section>

<section>
    <div class="grid three">
        <div class="card"><h2>About StartupSphere</h2><p>A role-based platform for listing startup-related events and connecting ecosystem members.</p><a class="btn light" href="/about">About Us</a></div>
        <div class="card"><h2>Contact Us</h2><p>Phone: +91-9876543210<br>Email: support@startupsphere.com<br>Address: Mohali, Punjab, India</p><a class="btn light" href="/contact">Contact</a></div>
        <div class="card"><h2>Core Modules</h2><p>Events, startups, mentors, investors, registrations, reviews, feedback, and users.</p><a class="btn light" href="/register">Create Account</a></div>
    </div>
</section>
@endsection
