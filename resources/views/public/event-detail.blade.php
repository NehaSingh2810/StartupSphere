@extends('layouts.app')
@section('content')
<section>
    @if(!empty($event['image']))<img class="thumb" src="{{ $event['image'] }}" alt="{{ $event['title'] }}">@endif
    <span class="tag">{{ $event['category'] }}</span>
    <h1>{{ $event['title'] }}</h1>
    <p>{{ $event['description'] }}</p>
    <div class="grid three">
        <div class="card"><h3>Date and Time</h3><p>{{ $event['date'] }} | {{ $event['time'] }}</p></div>
        <div class="card"><h3>Venue</h3><p>{{ $event['venue'] }}, {{ $event['city'] }}</p></div>
        <div class="card"><h3>Seats</h3><div class="bar"><span style="width:{{ min(100, round(($event['booked'] / max(1, $event['seats'])) * 100)) }}%"></span></div><p>{{ $event['booked'] }} booked, {{ $event['left'] }} remaining from {{ $event['seats'] }} | {{ $event['price'] }}</p><p>⭐ {{ $event['rating'] }}/5</p></div>
    </div>
    <div class="card" style="margin-top:18px">
        <h2>Agenda</h2>
        <div class="timeline">
            <div><strong>10:00 AM</strong><p>Founder welcome and keynote.</p></div>
            <div><strong>11:00 AM</strong><p>Startup showcase and investor questions.</p></div>
            <div><strong>01:00 PM</strong><p>Networking, feedback, and certificate information.</p></div>
        </div>
        @if(session('startup_user') && $canRegister)
            <form method="post" action="/events/{{ $event['slug'] }}/book">@csrf<button class="btn">Register for Event</button></form>
        @elseif(session('startup_user') && session('startup_user.role') === 'Startup Investor')
            <form method="post" action="/events/{{ $event['slug'] }}/invest">@csrf<button class="btn">Send Investment Request</button></form>
        @elseif(session('startup_user') && session('startup_user.role') === 'Admin')
            <span class="tag">Admin view only</span>
        @elseif(session('startup_user'))
            <span class="tag">Users can register; startup investors can send requests</span>
        @else<a class="btn" href="/login">Login to Register</a>@endif
    </div>
    <section>
        <h2>Reviews</h2>
        @if(session('startup_user'))
            <form class="card" method="post" action="/reviews" style="margin-bottom:18px;">@csrf
                <input type="hidden" name="event_slug" value="{{ $event['slug'] }}">
                <h3>Write Review for {{ $event['title'] }}</h3>
                <select name="rating"><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select><br><br>
                <textarea name="comment" placeholder="Write review" required></textarea><br><br>
                <button class="btn">Submit Review</button>
            </form>
        @endif
        <div class="grid three">
            @forelse($reviews as $review)
                <div class="card"><span class="tag">{{ $review['rating'] }} Stars</span><h3>{{ $review['target'] }}</h3><p>{{ $review['comment'] }}</p><p>{{ $review['user_email'] ?? 'User' }}</p></div>
            @empty
                <div class="card">No reviews yet for this event.</div>
            @endforelse
        </div>
    </section>
</section>
@endsection
