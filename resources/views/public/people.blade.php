@extends('layouts.app')
@section('content')
<section>
    <h1>{{ $title }}</h1>
    <p>Connect with ecosystem experts, book guidance, and grow your startup network.</p>
    <form class="search-box" method="get" action="/search">
        <input name="q" placeholder="Search expert name, industry, funding stage">
        <input type="hidden" name="type" value="people">
        <button class="btn">Search Experts</button>
    </form>
</section>
<div class="grid cards">
    @foreach($people as $person)
        <div class="card">
            <span class="tag">{{ $person['expertise'] }}</span>
            <h3>{{ $person['name'] }}</h3>
            <p>{{ $kind === 'mentor' ? 'Experience' : 'Funding Focus' }}: {{ $person['experience'] }}</p>
            <p>{{ $person['sessions'] }} {{ $kind === 'mentor' ? 'sessions completed' : 'portfolio/startup connects' }} | Rating {{ $person['rating'] }}</p>
            <a class="btn light" href="/login">Connect</a>
        </div>
    @endforeach
</div>
@endsection
