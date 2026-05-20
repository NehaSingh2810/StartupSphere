@extends('layouts.app')
@section('content')
<section class="search-hero">
    <span class="tag alert">Global Search</span>
    <h1>Find events, startups, and experts fast.</h1>
    <p>One search box for the whole platform to seamlessly connect with the entire startup ecosystem.</p>
    <form class="search-box" method="get" action="/search">
        <input name="q" value="{{ $query }}" placeholder="Search AI events, SaaS startups, investors">
        <select name="type">
            <option value="all" @selected($type === 'all')>All</option>
            <option value="events" @selected($type === 'events')>Events</option>
            <option value="startups" @selected($type === 'startups')>Startups</option>
            <option value="people" @selected($type === 'people')>Experts</option>
        </select>
        <button class="btn">Search</button>
    </form>
</section>

@if($query)
    <div class="section-head">
        <div><h2>{{ $total }} results for "{{ $query }}"</h2><p>Sorted by relevance.</p></div>
        <a class="btn light" href="/search">Clear</a>
    </div>
    <div class="grid three">
        @forelse($results as $item)
            <div class="card result-card">
                <span class="tag">{{ $item['type'] }}</span>
                <span class="score">{{ min(99, $item['score']) }}</span>
                <h3>{{ $item['title'] }}</h3>
                <p><strong>{{ $item['subtitle'] }}</strong></p>
                <p>{{ $item['description'] }}</p>
                <p>{{ $item['meta'] }}</p>
                <a class="btn light" href="{{ $item['url'] }}">Open</a>
            </div>
        @empty
            <div class="card"><h3>No matching records</h3><p>Try searching for AI, SaaS, funding, pitch, Mohali, or investor.</p></div>
        @endforelse
    </div>
@else
    <div class="grid three">
        @foreach(['AI events','SaaS startups','Seed investors','Pitch competition','Investor meetup','Mohali workshop'] as $suggestion)
            <a class="card" href="/search?q={{ urlencode($suggestion) }}"><span class="tag">Try Search</span><h3>{{ $suggestion }}</h3><p>Open ranked results for this startup ecosystem query.</p></a>
        @endforeach
    </div>
@endif
@endsection
