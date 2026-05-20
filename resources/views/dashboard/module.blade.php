@extends('layouts.app')
@section('content')
@php($role = session('startup_user.role', 'User'))
<div class="dash">
    @include('partials.dashboard-nav')
    <section>
        <div class="topline"><div><h1>{{ $title }}</h1><p>{{ $role }} workspace module.</p></div><span class="tag">{{ $role }}</span></div>

        @if(in_array($module, ['events', 'my-events', 'browse-events', 'registered-events']))
            @if($role === 'Admin' && $module === 'events')
                <form class="card" method="post" action="/dashboard/events">@csrf
                    <h2>Add Event</h2>
                    <div class="grid three">
                        <input name="title" placeholder="Event title" required>
                        <select name="category"><option>Workshop</option><option>Hackathon</option><option>Pitch Competition</option><option>Investor Meet</option><option>Webinar</option><option>Networking</option></select>
                        <input name="date" placeholder="Date, example 20 Jun 2026" required>
                        <input name="venue" placeholder="Venue" required>
                        <input name="city" placeholder="City" required>
                        <input name="organizer" placeholder="Organizer" required>
                        <input name="seats" type="number" placeholder="Seats" required>
                    </div><br>
                    <button class="btn">Add Event</button>
                </form>
            @endif
            <div class="flow">
                <div class="flow-step"><strong>1</strong><p>Admin adds event details like title, date, venue, organizer, seats, and category.</p></div>
                <div class="flow-step"><strong>2</strong><p>Users search or filter events from public listing or dashboard.</p></div>
                <div class="flow-step"><strong>3</strong><p>Register button stores participation in the registrations collection.</p></div>
            </div>
            <div class="grid three">@foreach($events as $event) @include('partials.event-card', ['event' => $event]) @endforeach</div>

        @elseif(in_array($module, ['startups', 'browse-startups', 'saved-startups', 'my-startup']))
            @if($role === 'Admin' && $module === 'startups')
                <form class="card" method="post" action="/dashboard/startups">@csrf
                    <h2>Add Startup</h2>
                    <div class="grid three">
                        <input name="name" placeholder="Startup name" required>
                        <input name="category" placeholder="Industry / Category" required>
                        <input name="founder" placeholder="Founder name" required>
                        <input name="city" placeholder="City" required>
                        <input name="stage" placeholder="Stage (Seed, MVP, etc.)" required>
                        <input name="valuation" placeholder="Valuation" disabled>
                    </div><br>
                    <textarea name="description" placeholder="Description" required></textarea><br><br>
                    <button class="btn">Add Startup</button>
                </form>
            @endif
            @if($module === 'my-startup')
                <form class="card">
                    <h2>Startup Profile Fields</h2>
                    <div class="grid three">
                        <input placeholder="Startup name">
                        <input placeholder="Industry">
                        <input placeholder="Funding stage">
                        <input placeholder="Location">
                        <input placeholder="Team size">
                        <input placeholder="Website">
                    </div><br>
                    <textarea placeholder="Description"></textarea><br><br>
                    <button class="btn">Save Profile</button>
                </form>
            @endif
            <div class="flow">
                <div class="flow-step"><strong>1</strong><p>Startup directory stores founder, industry, funding stage, city, and rating.</p></div>
                <div class="flow-step"><strong>2</strong><p>Investors can bookmark startups and track interest.</p></div>
                <div class="flow-step"><strong>3</strong><p>Admin can add or approve startup profiles from the dashboard.</p></div>
            </div>
            <div class="grid three">@foreach($startups as $startup) @include('partials.startup-card', ['startup' => $startup]) @endforeach</div>

        @elseif($module === 'users')
            <div class="grid three">
                @foreach($users as $user)
                    <div class="card"><span class="tag">{{ $user['role'] }}</span><h3>{{ $user['name'] }}</h3><p>{{ $user['email'] }} | {{ $user['phone'] ?? '' }}</p><p>{{ $user['city'] ?? 'Mohali' }}</p></div>
                @endforeach
            </div>

        @elseif($module === 'investors')
            <div class="grid three">
                @foreach($investors as $investor)
                    <div class="card"><span class="tag">{{ $investor['industry'] ?? 'Startup' }}</span><h3>{{ $investor['name'] }}</h3><p>{{ $investor['expertise'] }} | {{ $investor['experience'] }}</p><p>Rating {{ $investor['rating'] ?? '4.8' }}/5</p></div>
                @endforeach
            </div>



        @elseif($module === 'reviews')
            <form class="card" method="post" action="/reviews">@csrf
                <h2>Add Rating and Review</h2>
                <select name="event_slug">
                    @foreach($events as $event)
                        <option value="{{ $event['slug'] }}">{{ $event['title'] }}</option>
                    @endforeach
                </select><br><br>
                <select name="rating"><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select><br><br>
                <textarea name="comment" placeholder="Write review" required></textarea><br><br>
                <button class="btn">Submit Review</button>
            </form>
            <div class="grid three">@foreach($reviews as $review)<div class="card"><span class="tag">{{ $review['rating'] }} Stars</span><h3>{{ $review['target'] }}</h3><p>{{ $review['comment'] }}</p><p>{{ $review['user_email'] ?? 'User' }}</p></div>@endforeach</div>

        @elseif($module === 'feedback')
            <form class="card auth" method="post" action="/dashboard/feedback">@csrf
                <h2>Submit Feedback</h2>
                <label>Subject</label><input name="subject" required>
                <label>Rating</label><select name="rating"><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select>
                <label>Message</label><textarea name="message" rows="5" required></textarea><br><br>
                <button class="btn">Submit Feedback</button>
            </form>
            <div class="grid three">@foreach($feedbacks as $item)<div class="card"><span class="tag">{{ isset($item['rating']) ? $item['rating'].' Stars' : 'Contact Feedback' }}</span><h3>{{ $item['subject'] ?? 'Feedback' }}</h3><p>{{ $item['message'] }}</p><p>{{ $item['user_email'] ?? $item['email'] ?? 'User' }}</p></div>@endforeach</div>

        @elseif($module === 'reports')
            <div class="stats">
                <div class="card"><div class="metric">{{ $stats['users'] }}</div><p>Users</p></div>
                <div class="card"><div class="metric">{{ $stats['events'] }}</div><p>Events</p></div>
                <div class="card"><div class="metric">{{ $stats['startups'] }}</div><p>Startups</p></div>
                <div class="card"><div class="metric">{{ count($reviews) }}</div><p>Reviews</p></div>
                <div class="card"><div class="metric">{{ count($feedbacks) }}</div><p>Feedbacks</p></div>
            </div>

        @elseif($module === 'notifications')
            <div class="grid three">@foreach($notifications as $notice)<div class="card"><span class="tag alert">Notification</span><p>{{ $notice }}</p></div>@endforeach</div>

        @elseif($module === 'investor-requests')
            <div class="grid three">
                <div class="card"><span class="tag verify">Investor Flow</span><h3>Event Requests</h3><p>Investors use event cards to send investment requests. Admin receives each request as a notification.</p><a class="btn light" href="/dashboard/browse-events">Browse Events</a></div>
                <div class="card"><span class="tag">Startup Requests</span><h3>Startup Interest</h3><p>Investors use startup cards to send interest to admin. They cannot add or edit events.</p><a class="btn light" href="/dashboard/browse-startups">Browse Startups</a></div>
                <div class="card"><span class="tag alert">Admin Review</span><h3>Connected Notifications</h3><p>Admin checks notifications to follow up on investor requests and student registrations.</p><a class="btn light" href="/dashboard/notifications">Open Notifications</a></div>
            </div>

        @elseif($module === 'certificates')
            <div class="grid three"><div class="card"><span class="tag verify">Certificate</span><h3>Startup Workshop Participation</h3><p>Generated after attending a workshop or webinar.</p><button class="btn light">Download</button></div></div>

        @elseif($module === 'profile')
            @php($user=session('startup_user'))
            <form class="card auth" method="post" action="/profile">@csrf<h2>Update Profile</h2><label>Name</label><input name="name" value="{{ $user['name'] }}"><label>Phone</label><input name="phone" value="{{ $user['phone'] ?? '' }}"><label>City</label><input name="city" value="{{ $user['city'] ?? '' }}"><label>Bio</label><textarea name="bio">{{ $user['bio'] ?? '' }}</textarea><label>Skills</label><input name="skills" value="{{ $user['skills'] ?? '' }}"><br><br><button class="btn">Update Profile</button></form>

        @elseif($module === 'settings')
            <div class="grid three">
                <form class="card" method="post" action="/settings/password">@csrf<h3>Change Password</h3><input name="password" type="password" placeholder="New password"><br><br><input name="password_confirmation" type="password" placeholder="Confirm password"><br><br><button class="btn">Update Password</button></form>
                <div class="card"><h3>Settings Dropdown</h3><p>Keep only My Profile, Edit Profile, Change Password, and Logout.</p></div>
            </div>
        @endif
    </section>
</div>
@endsection
