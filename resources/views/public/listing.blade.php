@extends('layouts.app')
@section('content')
<section>
    <h1>{{ $title }}</h1>
    <p>Search, filter, rate, save, and register from a realistic startup ecosystem listing.</p>
    <form class="search-box" method="get" action="/search">
        <input name="q" value="{{ request('q') }}" placeholder="Search across events, startups, and experts">
        <select name="type"><option value="all">All</option><option value="events" @selected($type === 'events')>Events</option><option value="startups" @selected($type === 'startups')>Startups</option><option value="people">Experts</option></select>
        <button class="btn">Global Search</button>
    </form>
    <form class="inline" method="get">
        <input name="q" value="{{ request('q') }}" placeholder="Search title or keyword">
        @if($type === 'events')
            <input type="text" name="city" value="{{ request('city') }}" placeholder="City (e.g. Bangalore)">
        @endif
        <select name="category">
            <option value="">All categories</option>
            @foreach($categories as $category)
                <option @selected(request('category')===$category)>{{ $category }}</option>
            @endforeach
        </select>
        <button class="btn">Filter</button>
        <a class="btn light" href="{{ url()->current() }}">Reset</a>
    </form>
</section>
<div class="grid cards">
    @forelse($items as $item)
        @if($type === 'events') @include('partials.event-card', ['event' => $item])
        @else @include('partials.startup-card', ['startup' => $item])
        @endif
    @empty
        <div class="card">No records found.</div>
    @endforelse
</div>
@endsection
