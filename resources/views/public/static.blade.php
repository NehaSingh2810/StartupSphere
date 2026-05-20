@extends('layouts.app')
@section('content')
<section>
    <h1>{{ $content[0] }}</h1>
    <p>{{ $content[1] }}</p>
</section>
@if($page === 'about')
<div class="grid three">
    <div class="card"><h3>Mission</h3><p>Connect startup ecosystems across India with events, investors, founders, and learning resources.</p></div>
    <div class="card"><h3>Vision</h3><p>Become a trusted discovery and networking platform for early-stage startup growth.</p></div>
    <div class="card"><h3>Why Us</h3><p>One platform for startup listings, event booking, ratings, reviews, feedback, and profile management.</p></div>
</div>
@elseif($page === 'faq')
<div class="grid three">@foreach(['How can I register for an event? Login and click Register on any event.','Can I rate events? Yes, feedback supports 1 to 5 star ratings.','Can startups be saved? Yes, logged-in users can save startup profiles.','Is there an admin side? The project includes collections and modules ready for admin management.','Which database is used? MongoDB collections store users, events, startups, bookings, feedback, and more.','What is the demo login? demo@startupsphere.com / password'] as $faq)<div class="card"><p>{{ $faq }}</p></div>@endforeach</div>
@elseif($page === 'contact')
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px; align-items: start;">
    <div>
        <h2 style="font-size: 28px; margin-bottom: 16px;">We'd Love to Hear From You</h2>
        <p style="color: var(--muted); margin-bottom: 24px;">Whether you have a question about our events, need help with your startup profile, or just want to say hi, our team is ready to answer all your questions.</p>
        <div style="display:flex; flex-direction: column; gap: 16px;">
            <div style="padding: 16px; background: white; border: 1px solid var(--line); border-radius: 8px;">
                <strong style="color: var(--brand);">Email Us</strong><br>support@startupsphere.com
            </div>
            <div style="padding: 16px; background: white; border: 1px solid var(--line); border-radius: 8px;">
                <strong style="color: var(--brand);">Call Us</strong><br>+91-9876543210
            </div>
            <div style="padding: 16px; background: white; border: 1px solid var(--line); border-radius: 8px;">
                <strong style="color: var(--brand);">Visit Us</strong><br>Mohali, Punjab, India
            </div>
        </div>
    </div>
    <form class="card" method="post" action="/feedback" style="margin: 0;">
        @csrf
        <h2 style="margin-top:0;">Send Us Your Feedback</h2>
        <input type="text" name="name" placeholder="Your Name" required style="margin-bottom: 12px; width: 100%;">
        <input type="email" name="email" placeholder="Your Email" required style="margin-bottom: 12px; width: 100%;">
        <input type="text" name="subject" placeholder="Subject" required style="margin-bottom: 12px; width: 100%;">
        <textarea name="message" placeholder="Your feedback or suggestion..." rows="6" required style="margin-bottom: 16px; width: 100%;"></textarea>
        <button class="btn" type="submit" style="width: 100%;">Send Feedback</button>
    </form>
</div>
@elseif($page === 'blogs')
<div class="grid cards">@foreach(['How to Build an MVP','Startup Funding Guide','Pitch Deck Checklist','Legal Basics','Marketing for Founders','Finding Investors','Product Launch Plan','Hiring First Team'] as $blog)<div class="card"><span class="tag">Blog</span><h3>{{ $blog }}</h3><p>Detailed practical guide for startup founders and students.</p></div>@endforeach</div>
@elseif($page === 'investors')
<div class="grid three">
    <div class="card"><span class="tag verify">Investor Role</span><h3>What Investors Do</h3><p>Investors browse events and startups, then send investment interest to the admin account for follow-up.</p><a class="btn light" href="/register">Create Investor Account</a></div>
    <div class="card"><span class="tag">Different from Admin</span><h3>No Management Access</h3><p>Investors cannot add events, manage users, or change platform records. Admin controls those modules.</p><a class="btn light" href="/events">Browse Events</a></div>
    <div class="card"><span class="tag alert">Connected Flow</span><h3>Admin Notifications</h3><p>Every investor event or startup request appears in the admin notifications dashboard.</p><a class="btn light" href="/startups">Browse Startups</a></div>
</div>
@elseif(in_array($page, ['privacy', 'terms']))
<div class="grid three">
    <div class="card"><h3>Accounts</h3><p>Demo users can register, login, update profiles, and manage dashboard activity.</p></div>
    <div class="card"><h3>Activity</h3><p>Event registrations, startup saves, reviews, and feedback are stored for project workflow demonstration.</p></div>
    <div class="card"><h3>Support</h3><p>For changes or questions, use the contact page feedback form.</p><a class="btn light" href="/contact">Contact</a></div>
</div>
@else
<div class="grid three">@foreach(['Pitch deck template','Funding checklist','Business model canvas','Legal document samples','Marketing launch plan','Investor email format'] as $resource)<div class="card"><h3>{{ $resource }}</h3><p>Ready-to-use startup resource section for your project demo.</p></div>@endforeach</div>
@endif
@endsection
