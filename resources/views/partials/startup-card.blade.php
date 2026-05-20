<div class="card">
    @php
        $raised = $startup['funding_raised'] ?? 20;
        $goal = $startup['funding_goal'] ?? 50;
    @endphp
    @if(!empty($startup['logo']))<img class="thumb" src="{{ $startup['logo'] }}" alt="{{ $startup['name'] }}">@endif
    <span class="tag">{{ $startup['category'] }}</span>
    @if($startup['verified'] ?? false)<span class="tag verify">Verified Startup</span>@endif
    <h3>{{ $startup['name'] }}</h3>
    <p>{{ $startup['description'] }}</p>
    <p>Founder: {{ $startup['founder'] }} | {{ $startup['city'] }} | {{ $startup['stage'] }}</p>
    <p>Industry: {{ $startup['category'] }} | Rating {{ $startup['rating'] }}/5</p>
    <div class="bar good"><span style="width:{{ min(100, round(($raised / max(1, $goal)) * 100)) }}%"></span></div>
    <p>Funding: Rs {{ $raised }}L raised / Goal Rs {{ $goal }}L</p>
    @if(session('startup_user'))
        <form method="post" action="/startups/{{ $startup['slug'] }}/save" style="display:inline">@csrf<button class="btn light">Save Startup</button></form>
        @if(session('startup_user.role') === 'Startup Investor')
            <form method="post" action="/startups/{{ $startup['slug'] }}/interest" style="display:inline">@csrf<button class="btn">Invest Request</button></form>
        @endif
    @endif
</div>
