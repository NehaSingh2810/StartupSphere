<div class="card">
    @php
        $seats = $event['seats'] ?? 100;
        $booked = $event['booked'] ?? max(0, $seats - ($event['left'] ?? 25));
        $left = $event['left'] ?? max(0, $seats - $booked);
    @endphp
    @if(!empty($event['image']))<img class="thumb" src="{{ $event['image'] }}" alt="{{ $event['title'] }}">@endif
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
        <span class="tag">{{ $event['category'] }}</span>
        @if($left > 20)
            <span style="font-size:12px; font-weight:bold; color:var(--brand);">🟢 Open</span>
        @elseif($left > 0)
            <span style="font-size:12px; font-weight:bold; color:var(--gold);">🟡 Closing Soon</span>
        @else
            <span style="font-size:12px; font-weight:bold; color:var(--accent);">🔴 Full</span>
        @endif
    </div>
    
    <h3 style="margin-top:0;">{{ $event['title'] }}</h3>
    <p>{{ $event['date'] }} at {{ $event['time'] }} | {{ $event['city'] }}</p>
    <p>{{ $event['venue'] }} | {{ $event['price'] }}</p>
    <div class="bar"><span style="width:{{ min(100, round(($booked / max(1, $seats)) * 100)) }}%"></span></div>
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <span style="font-size:13px; font-weight:bold;">Seats: {{ $booked }} / {{ $seats }}</span>
        <span style="font-size:13px; color:var(--muted);">⭐ {{ $event['rating'] }}/5</span>
    </div>
    
    <div style="display:flex; gap:8px;">
        <a class="btn light" style="flex:1;" href="/events/{{ $event['slug'] }}">Details</a>
        @if(session('startup_user') && $left > 0)
            <form method="post" action="/events/{{ $event['slug'] }}/book" style="flex:1; display:flex;">
                @csrf
                <button class="btn" style="width:100%;">Register Now</button>
            </form>
        @endif
    </div>
</div>
