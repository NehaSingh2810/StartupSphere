@php($role = session('startup_user.role', 'Startup Founder'))
<aside class="side">
    <h3>{{ $role }}</h3>
    <a href="/dashboard">Dashboard</a>

    @if($role === 'Admin')
        <a href="/dashboard/users">Users</a>
        <a href="/dashboard/events">Events</a>
        <a href="/dashboard/startups">Startups</a>
        <a href="/dashboard/mentors">Mentors</a>
        <a href="/dashboard/investors">Investors</a>
        <a href="/dashboard/reports">Reports</a>
    @elseif($role === 'Startup Founder')
        <a href="/dashboard/my-startup">My Startup</a>
        <a href="/dashboard/my-events">My Events</a>
        <a href="/dashboard/investor-requests">Investor Requests</a>
    @elseif($role === 'Investor')
        <a href="/dashboard/browse-startups">Browse Startups</a>
        <a href="/dashboard/interested-startups">Interested Startups</a>
        <a href="/dashboard/events">Events</a>
    @endif

    <a href="/dashboard/reviews">Ratings and Reviews</a>
    <a href="/dashboard/notifications">Notifications</a>
</aside>
