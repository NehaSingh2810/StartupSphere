@php($role = session('startup_user.role', 'User'))
<aside class="side">
    <h3>{{ $role }}</h3>
    <a href="/dashboard">Dashboard</a>

    @if($role === 'Admin')
        <a href="/dashboard/users">Users</a>
        <a href="/dashboard/events">Events</a>
        <a href="/dashboard/startups">Startups</a>
        <a href="/dashboard/investors">Investors</a>
        <a href="/dashboard/feedback">Feedback</a>
        <a href="/dashboard/investor-requests">Investment Requests</a>
        <a href="/dashboard/reports">Reports</a>
    @elseif($role === 'User')
        <a href="/dashboard/browse-events">Browse Events</a>
        <a href="/dashboard/registered-events">Registered Events</a>
        <a href="/dashboard/saved-startups">Saved Startups</a>
        <a href="/dashboard/certificates">Certificates</a>
    @elseif($role === 'Startup Investor')
        <a href="/dashboard/browse-events">Browse Events</a>
        <a href="/dashboard/browse-startups">Browse Startups</a>
        <a href="/dashboard/investor-requests">Investment Requests</a>
    @endif

    <a href="/dashboard/reviews">Ratings and Reviews</a>
    <a href="/dashboard/notifications">Notifications</a>
</aside>
