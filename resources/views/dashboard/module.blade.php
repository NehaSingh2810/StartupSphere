@extends('layouts.app')
@section('content')
@php($role = session('startup_user.role', 'Startup Founder'))
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

        @elseif(in_array($module, ['startups', 'browse-startups', 'saved-startups', 'my-startup', 'interested-startups']))
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

        @elseif($module === 'mentors' || $module === 'my-sessions' || $module === 'startup-requests')
            @if($role === 'Admin' && $module === 'mentors')
                <form class="card" method="post" action="/dashboard/mentors">@csrf
                    <h2>Add Mentor</h2>
                    <div class="grid three">
                        <input name="name" placeholder="Mentor name" required>
                        <input name="expertise" placeholder="Expertise" required>
                        <input name="industry" placeholder="Industry" required>
                        <input name="experience" placeholder="Experience" required>
                        <input name="rating" type="number" min="1" max="5" placeholder="Rating" required>
                    </div><br>
                    <button class="btn">Add Mentor</button>
                </form>
            @endif
            <div class="grid three">
                @foreach($mentors as $person)
                    <div class="card"><span class="tag">{{ $person['expertise'] }}</span><h3>{{ $person['name'] }}</h3><p>{{ $person['experience'] }} | {{ $person['sessions'] }} sessions | Rating {{ $person['rating'] }}</p><button class="btn light">{{ $module === 'startup-requests' ? 'Accept Request' : 'View' }}</button></div>
                @endforeach
            </div>

        @elseif($module === 'investors' || $module === 'investor-requests')
            @if($role === 'Admin' && $module === 'investors')
                <form class="card" method="post" action="/dashboard/investors">@csrf
                    <h2>Add Investor</h2>
                    <div class="grid three">
                        <input name="name" placeholder="Investor name" required>
                        <input name="expertise" placeholder="Expertise" required>
                        <input name="industry" placeholder="Industry" required>
                        <input name="experience" placeholder="Experience" required>
                        <input name="rating" type="number" min="1" max="5" placeholder="Rating" required>
                    </div><br>
                    <button class="btn">Add Investor</button>
                </form>
            @endif
            <div class="grid three">
                @foreach($investors as $person)
                    <div class="card"><span class="tag">{{ $person['industry'] ?? 'Startup' }}</span><h3>{{ $person['name'] }}</h3><p>{{ $person['expertise'] }} | {{ $person['experience'] }}</p><button class="btn light">View</button></div>
                @endforeach
            </div>

        @elseif($module === 'reviews')
            <form class="card" method="post" action="/reviews">@csrf
                <h2>Add Rating and Review</h2>
                <input name="target" placeholder="Event or startup name" required><br><br>
                <select name="rating"><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select><br><br>
                <textarea name="comment" placeholder="Write review" required></textarea><br><br>
                <button class="btn">Submit Review</button>
            </form>
            <div class="grid three">@foreach($reviews as $review)<div class="card"><span class="tag">{{ $review['rating'] }} Stars</span><h3>{{ $review['target'] }}</h3><p>{{ $review['comment'] }}</p><p>{{ $review['user_email'] ?? 'User' }}</p></div>@endforeach</div>

        @elseif($module === 'feedback')
            <form class="card auth" method="post" action="/feedback">@csrf
                <h2>Submit Feedback</h2>
                <label>Subject</label><input name="subject" required>
                <label>Rating</label><select name="rating"><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select>
                <label>Message</label><textarea name="message" rows="5" required></textarea><br><br>
                <button class="btn">Submit Feedback</button>
            </form>
            <div class="grid three">@foreach($feedbacks as $item)<div class="card"><span class="tag">{{ $item['rating'] }} Stars</span><h3>{{ $item['subject'] ?? 'Feedback' }}</h3><p>{{ $item['message'] }}</p><p>{{ $item['user_email'] ?? 'User' }}</p></div>@endforeach</div>

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
