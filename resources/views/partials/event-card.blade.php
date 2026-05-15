<div class="card">
    @php
        $seats = $event['seats'] ?? 100;
        $booked = $event['booked'] ?? max(0, $seats - ($event['left'] ?? 25));
        $left = $event['left'] ?? max(0, $seats - $booked);
    @endphp
    @if(!empty($event['image']))<img class="thumb" src="{{ $event['image'] }}" alt="{{ $event['title'] }}">@endif
    <span class="tag">{{ $event['category'] }}</span>
    <h3>{{ $event['title'] }}</h3>
    <p>{{ $event['date'] }} at {{ $event['time'] }} | {{ $event['city'] }}</p>
    <p>{{ $event['venue'] }} | {{ $event['price'] }}</p>
    <div class="bar"><span style="width:{{ min(100, round(($booked / max(1, $seats)) * 100)) }}%"></span></div>
    <p>Total seats: {{ $seats }} | Booked: {{ $booked }} | Seats left: {{ $left }}</p>
    <p>Organizer: {{ $event['organizer'] }} | Rating {{ $event['rating'] }}/5</p>
    <a class="btn light" href="/events/{{ $event['slug'] }}">Details</a>
    @if(session('startup_user'))
        <form method="post" action="/events/{{ $event['slug'] }}/book" style="display:inline">@csrf<button class="btn">Register</button></form>
    @endif
</div>
