@php($role = session('startup_user.role', 'Startup Founder'))
<aside class="side">
    <h3>{{ $role }}</h3>
    <a href="/dashboard">Dashboard</a>

    @if($role === 'Admin')
        <a href="/dashboard/users">Users</a>
        <a href="/dashboard/events">Events</a>
        <a href="/dashboard/startups">Startups</a>
        <a href="/dashboard/reports">Reports</a>
    @elseif($role === 'Startup Founder')
        <a href="/dashboard/my-startup">My Startup</a>
        <a href="/dashboard/my-events">My Events</a>
        <a href="/dashboard/saved-startups">Saved Startups</a>
    @elseif($role === 'Student')
        <a href="/dashboard/browse-events">Browse Events</a>
        <a href="/dashboard/registered-events">Registered Events</a>
        <a href="/dashboard/saved-startups">Saved Events</a>
    @endif

    <a href="/dashboard/reviews">Ratings and Reviews</a>
    <a href="/dashboard/notifications">Notifications</a>
</aside>
