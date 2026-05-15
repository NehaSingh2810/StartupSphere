@extends('layouts.app')
@section('content')
<section>
    <h1>{{ $content[0] }}</h1>
    <p>{{ $content[1] }}</p>
</section>
@if($page === 'about')
<div class="grid three">
    <div class="card"><h3>Mission</h3><p>Connect startup ecosystems across India with events, mentorship, investors, and learning resources.</p></div>
    <div class="card"><h3>Vision</h3><p>Become a trusted discovery and networking platform for early-stage startup growth.</p></div>
    <div class="card"><h3>Why Us</h3><p>One platform for startup listings, event booking, ratings, reviews, feedback, and profile management.</p></div>
</div>
@elseif($page === 'faq')
<div class="grid three">@foreach(['How can I register for an event? Login and click Register on any event.','Can I rate events? Yes, feedback supports 1 to 5 star ratings.','Can startups be saved? Yes, logged-in users can save startup profiles.','Is there an admin side? The project includes collections and modules ready for admin management.','Which database is used? MongoDB collections store users, events, startups, bookings, feedback, and more.','What is the demo login? demo@startupsphere.com / password'] as $faq)<div class="card"><p>{{ $faq }}</p></div>@endforeach</div>
@elseif($page === 'contact')
<form class="card auth" method="post" action="/feedback">@csrf
    <h2>Send Message</h2>
    <input name="rating" type="hidden" value="5"><textarea name="message" rows="5" placeholder="Your message"></textarea><br><br><button class="btn">Submit</button>
</form>
@elseif($page === 'blogs')
<div class="grid cards">@foreach(['How to Build an MVP','Startup Funding Guide','Pitch Deck Checklist','Legal Basics','Marketing for Founders','Finding Investors','Product Launch Plan','Hiring First Team'] as $blog)<div class="card"><span class="tag">Blog</span><h3>{{ $blog }}</h3><p>Detailed practical guide for startup founders and students.</p></div>@endforeach</div>
@else
<div class="grid three">@foreach(['Pitch deck template','Funding checklist','Business model canvas','Legal document samples','Marketing launch plan','Investor email format'] as $resource)<div class="card"><h3>{{ $resource }}</h3><p>Ready-to-use startup resource section for your project demo.</p></div>@endforeach</div>
@endif
@endsection
