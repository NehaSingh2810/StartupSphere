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
            @if($module === 'registered-events')
                @if($role === 'Admin')
                    <h2>User Event Registrations</h2>
                    <div class="grid three">
                        @forelse($registrations as $registration)
                            <div class="card">
                                <span class="tag verify">{{ $registration['status'] ?? 'Registered' }}</span>
                                <h3>{{ $registration['event_title'] ?? 'Event registration' }}</h3>
                                <p>User: {{ $registration['user_name'] ?? 'User' }}</p>
                                <p>{{ $registration['user_email'] ?? 'No email' }}</p>
                                <p>{{ $registration['created_at'] ?? 'Just now' }}</p>
                            </div>
                        @empty
                            <div class="card"><span class="tag">No Registrations</span><h3>Registered Events</h3><p>User event registrations will appear here.</p></div>
                        @endforelse
                    </div>
                    <h2 style="margin-top:28px;">Startup Investor Event Requests</h2>
                    <div class="grid three">
                        @forelse($eventInvestmentRequests as $request)
                            <div class="card">
                                <span class="tag alert">{{ $request['status'] ?? 'Requested' }}</span>
                                <h3>{{ $request['event_title'] ?? 'Event request' }}</h3>
                                <p>Startup Investor: {{ $request['user_name'] ?? 'Startup Investor' }}</p>
                                <p>{{ $request['user_email'] ?? 'No email' }}</p>
                                <p>{{ $request['created_at'] ?? 'Just now' }}</p>
                            </div>
                        @empty
                            <div class="card"><span class="tag">No Requests</span><h3>Investor Event Requests</h3><p>Startup investor event requests will appear here after they click Invest Request.</p></div>
                        @endforelse
                    </div>
                @else
                    <div class="grid three">
                        @forelse($events as $event)
                            <div class="card">
                                <span class="tag verify">Registered</span>
                                <h3>{{ $event['title'] }}</h3>
                                <p>{{ $event['date'] }} at {{ $event['time'] }} | {{ $event['city'] }}</p>
                                <p>{{ $event['venue'] }} | {{ $event['price'] }}</p>
                                <a class="btn light" href="/events/{{ $event['slug'] }}">Details</a>
                            </div>
                        @empty
                            <div class="card"><span class="tag">No Events</span><h3>Registered Events</h3><p>Your registered events will appear here after you register.</p><a class="btn light" href="/dashboard/browse-events">Browse Events</a></div>
                        @endforelse
                    </div>
                @endif
            @else
                <div class="flow">
                    <div class="flow-step"><strong>1</strong><p>Admin adds event details like title, date, venue, organizer, seats, and category.</p></div>
                    <div class="flow-step"><strong>2</strong><p>Users search or filter events from public listing or dashboard.</p></div>
                    <div class="flow-step"><strong>3</strong><p>Register button stores participation in the registrations collection.</p></div>
                </div>
                <div class="grid three">@foreach($events as $event) @include('partials.event-card', ['event' => $event]) @endforeach</div>
            @endif

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
            @if($module === 'saved-startups' && $role === 'Admin')
                <div class="grid three">
                    @forelse($savedStartupRecords as $saved)
                        <div class="card">
                            <span class="tag verify">Saved</span>
                            <h3>{{ $saved['startup_title'] ?? 'Saved startup' }}</h3>
                            <p>User: {{ $saved['user_name'] ?? 'User' }}</p>
                            <p>{{ $saved['user_email'] ?? 'No email' }}</p>
                            <p>{{ $saved['created_at'] ?? 'Just now' }}</p>
                        </div>
                    @empty
                        <div class="card"><span class="tag">No Saved Startups</span><h3>Saved Startups</h3><p>User saved startup activity will appear here.</p></div>
                    @endforelse
                </div>
            @else
                <div class="flow">
                    <div class="flow-step"><strong>1</strong><p>Startup directory stores founder, industry, funding stage, city, and rating.</p></div>
                    <div class="flow-step"><strong>2</strong><p>Users save startups and investors send interest requests.</p></div>
                    <div class="flow-step"><strong>3</strong><p>Admin can add or approve startup profiles from the dashboard.</p></div>
                </div>
                <div class="grid three">
                    @forelse($startups as $startup)
                        @include('partials.startup-card', ['startup' => $startup])
                    @empty
                        <div class="card"><span class="tag">No Startups</span><h3>Saved Startups</h3><p>Your saved startups will appear here after you save them.</p><a class="btn light" href="/dashboard/browse-startups">Browse Startups</a></div>
                    @endforelse
                </div>
            @endif

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

        @elseif($module === 'reports')
            <div class="stats">
                <div class="card"><div class="metric">{{ $stats['users'] }}</div><p>Users</p></div>
                <div class="card"><div class="metric">{{ $stats['events'] }}</div><p>Events</p></div>
                <div class="card"><div class="metric">{{ $stats['startups'] }}</div><p>Startups</p></div>
                <div class="card"><div class="metric">{{ count($reviews) }}</div><p>Reviews</p></div>
            </div>

        @elseif($module === 'notifications')
            <div class="grid three">@foreach($notifications as $notice)<div class="card"><span class="tag alert">Notification</span><p>{{ $notice }}</p></div>@endforeach</div>

        @elseif($module === 'investor-requests')
            @if($role === 'Admin')
                <h2>Event Investment Requests</h2>
                <div class="grid three">
                    @forelse($eventInvestmentRequests as $request)
                        <div class="card">
                            <span class="tag verify">{{ $request['status'] ?? 'Requested' }}</span>
                            <h3>{{ $request['event_title'] ?? 'Event request' }}</h3>
                            <p>Investor: {{ $request['user_name'] ?? 'Startup Investor' }}</p>
                            <p>{{ $request['user_email'] ?? 'No email' }}</p>
                            <p>{{ $request['created_at'] ?? 'Just now' }}</p>
                        </div>
                    @empty
                        <div class="card"><span class="tag">No Requests</span><h3>Event Requests</h3><p>Startup investor event requests will appear here automatically.</p></div>
                    @endforelse
                </div>

                <h2 style="margin-top:28px;">Startup Investment Requests</h2>
                <div class="grid three">
                    @forelse($startupInvestmentRequests as $request)
                        <div class="card">
                            <span class="tag verify">{{ $request['status'] ?? 'Interested' }}</span>
                            <h3>{{ $request['startup_title'] ?? 'Startup request' }}</h3>
                            <p>Investor: {{ $request['user_name'] ?? 'Startup Investor' }}</p>
                            <p>{{ $request['user_email'] ?? 'No email' }}</p>
                            <p>{{ $request['created_at'] ?? 'Just now' }}</p>
                        </div>
                    @empty
                        <div class="card"><span class="tag">No Requests</span><h3>Startup Requests</h3><p>Startup investor interest requests will appear here automatically.</p></div>
                    @endforelse
                </div>
            @else
                <div class="grid three">
                    <div class="card"><span class="tag verify">Event Requests</span><h3>Request Event Investment</h3><p>Use event cards to send investment requests. Admin receives each request for review.</p><a class="btn light" href="/dashboard/browse-events">Browse Events</a></div>
                    <div class="card"><span class="tag">Startup Requests</span><h3>Request Startup Investment</h3><p>Use startup cards to send interest to admin. Investors cannot add or edit events.</p><a class="btn light" href="/dashboard/browse-startups">Browse Startups</a></div>
                    <div class="card"><span class="tag alert">Admin Review</span><h3>Request Status</h3><p>Every request is sent to the fixed admin account for review.</p><a class="btn light" href="/dashboard/investor-requests">View Requests</a></div>
                </div>
            @endif

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
