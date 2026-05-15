<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'StartupSphere' }}</title>
    <style>
        :root { --ink:#102022; --muted:#607174; --line:#dce6e2; --brand:#0f8b6f; --accent:#e15d44; --gold:#d99121; --blue:#2563eb; --soft:#f3f8f5; --panel:#ffffff; }
        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body { margin:0; font-family: Arial, Helvetica, sans-serif; color:var(--ink); background:#fbfdfb; line-height:1.5; display:flex; flex-direction:column; }
        a, button { color:inherit; text-decoration:none; font:inherit; }
        .wrap { width:100%; max-width:1180px; margin:auto; padding:0 20px; }
        header { position:sticky; top:0; z-index:10; background:#fffffff0; border-bottom:1px solid var(--line); backdrop-filter: blur(10px); }
        nav { min-height:72px; display:flex; align-items:center; justify-content:space-between; gap:18px; flex-wrap:wrap; }
        .logo { font-weight:800; font-size:22px; color:var(--brand); }
        .nav-toggle { display:none; border:0; background:transparent; color:var(--ink); font-size:28px; cursor:pointer; line-height:1; }
        .links { display:flex; align-items:center; flex-wrap:wrap; gap:14px; font-size:14px; }
        .btn { border:0; border-radius:6px; padding:10px 14px; background:var(--brand); color:white; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:40px; }
        .btn.alt { background:#102022; }
        .btn.light { background:#e8f4ef; color:var(--brand); }
        .hero { min-height:calc(100vh - 128px); display:grid; grid-template-columns:1.1fr .9fr; gap:38px; align-items:center; padding:60px 0 48px; }
        h1 { font-size:56px; line-height:1.04; margin:0 0 18px; letter-spacing:-0.02em; }
        h2 { font-size:34px; margin:0 0 18px; }
        h3 { margin:0 0 8px; }
        p { color:var(--muted); }
        .hero-art { min-height:420px; border-radius:8px; background:#102022; padding:24px; color:white; display:grid; gap:14px; align-content:end; overflow:hidden; position:relative; }
        .hero-art:before { content:""; position:absolute; inset:0; background:linear-gradient(135deg,#0f8b6fcc,#102022 55%,#e15d44bb), url('https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=1100&q=80') center/cover; }
        .hero-art > * { position:relative; }
        .stats, .grid { display:grid; gap:18px; }
        .stats { grid-template-columns:repeat(5,1fr); margin:24px 0; }
        .grid.cards { grid-template-columns:repeat(4,1fr); }
        .grid.three { grid-template-columns:repeat(3,1fr); }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:18px; box-shadow:0 10px 26px #1020220d; }
        .thumb { width:100%; aspect-ratio:16/9; object-fit:cover; border-radius:6px; margin-bottom:12px; background:var(--soft); }
        .panel { background:var(--soft); border:1px solid var(--line); border-radius:8px; padding:20px; }
        .metric { font-size:28px; font-weight:800; color:var(--brand); }
        .tag { display:inline-block; padding:5px 9px; border-radius:999px; background:#e8f4ef; color:#0f6b58; font-size:12px; font-weight:700; }
        .tag.verify { background:#fff1d9; color:#a45b00; }
        .tag.alert { background:#edf3ff; color:#184bb5; }
        .tag.risk { background:#ffe9e5; color:#a33220; }
        .bar { height:9px; border-radius:999px; background:#e8eee9; overflow:hidden; margin:8px 0; }
        .bar span { display:block; height:100%; background:var(--accent); }
        .bar.good span { background:var(--brand); }
        .score { height:38px; width:38px; border-radius:50%; display:inline-grid; place-items:center; background:#edf8f4; color:var(--brand); font-weight:800; font-size:12px; }
        .timeline { display:grid; gap:10px; margin:14px 0; }
        .timeline div { display:grid; grid-template-columns:92px 1fr; gap:12px; align-items:start; padding:10px 0; border-bottom:1px solid var(--line); }
        .search-hero { padding:48px 0 28px; }
        .search-box { display:grid; grid-template-columns:1fr 170px auto; gap:10px; padding:10px; background:white; border:1px solid var(--line); border-radius:8px; box-shadow:0 16px 36px #10202212; margin-top:18px; }
        .search-box input, .search-box select { border:0; background:#f8fbf9; }
        .result-card { position:relative; }
        .result-card .score { position:absolute; right:16px; top:16px; }
        .flow { display:grid; gap:12px; margin:16px 0; }
        .flow-step { display:grid; grid-template-columns:38px 1fr; gap:12px; align-items:start; padding:12px; background:white; border:1px solid var(--line); border-radius:8px; }
        .flow-step strong { width:32px; height:32px; border-radius:50%; display:grid; place-items:center; background:#102022; color:white; }
        section { padding:46px 0; }
        .toolbar { display:flex; gap:10px; flex-wrap:wrap; margin:18px 0 26px; }
        input, select, textarea { width:100%; padding:12px; border:1px solid var(--line); border-radius:6px; font:inherit; background:white; }
        form.inline { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        form.inline input, form.inline select { width:auto; min-width:190px; }
        .auth { max-width:520px; margin:48px auto; }
        .dash { display:grid; grid-template-columns:260px 1fr; gap:24px; padding:28px 0; }
        .side { border-right:1px solid var(--line); min-height:70vh; padding-right:18px; }
        .side a, .drop a { display:block; padding:10px 12px; border-radius:6px; margin-bottom:4px; }
        .side a:hover, .drop a:hover { background:var(--soft); }
        .topline { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px; }
        .user-menu { position: relative; }
        .drop { position:absolute; right:0; top:62px; background:white; border:1px solid var(--line); border-radius:8px; padding:8px; min-width:220px; display:none; box-shadow:0 18px 36px #10202224; }
        .drop a, .drop form { width:100%; }
        .drop button { width:100%; }
        .drop a { display:block; padding:10px 12px; border-radius:6px; }
        .drop a:hover { background:var(--soft); }
        .notice { padding:12px 14px; border-radius:6px; background:#e8f4ef; color:#0f6b58; margin:16px 0; }
        .section-head { display:flex; align-items:end; justify-content:space-between; gap:16px; margin:24px 0 16px; }
        .error { color:#b3261e; font-size:14px; }
        footer { background:#102022; color:white; padding:34px 0; margin-top:30px; }
        footer p { color:#dce6e2; }
        @media (max-width: 900px) { .hero, .dash { grid-template-columns:1fr; } .stats, .grid.cards, .grid.three { grid-template-columns:1fr 1fr; } .side { border-right:0; min-height:auto; } h1 { font-size:42px; } }
        @media (max-width: 760px) {
            .nav-toggle { display:inline-flex; }
            .links { display:none; width:100%; flex-direction:column; align-items:flex-start; padding-top:16px; border-top:1px solid var(--line); }
            .links.open { display:flex; }
            .links a, .links .btn { width:100%; justify-content:flex-start; }
            .hero { min-height:auto; padding:42px 0 30px; }
            .hero-art { min-height:280px; }
            .stats, .grid.cards, .grid.three, .search-box { grid-template-columns:1fr; }
            nav { height:auto; padding:14px 0; align-items:flex-start; }
            .timeline div { grid-template-columns:1fr; }
            .drop { position:static; box-shadow:none; border:none; width:auto; }
        }
        @media (max-width: 560px) { .hero { gap:24px; } .h1 { font-size:32px; } .links { padding-top:12px; } }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
<header>
    <div class="wrap">
        <nav x-data="{ open: false }">
            <a class="logo" href="/">StartupSphere</a>
            <button class="nav-toggle" @click="open = !open" aria-label="Toggle menu">☰</button>
            <div class="links" :class="{ 'open': open }">
                <a href="/">Home</a><a href="/events">Events</a><a href="/startups">Startups</a><a href="/mentors">Mentors</a><a href="/investors">Investors</a><a href="/about">About</a><a href="/contact">Contact</a>
                @if(session('startup_user'))
                    <div class="user-menu" x-data="{ openUser: false }" @click.away="openUser = false">
                        <button type="button" class="btn light" @click.prevent.stop="openUser = !openUser">{{ session('startup_user.name') }}</button>
                        <div class="drop" x-show="openUser" x-cloak x-transition @click.stop>
                            <a href="/">Home</a>
                            <a href="/dashboard">Dashboard</a>
                            <a href="/dashboard/profile">My Profile</a>
                            <a href="/dashboard/settings">Settings</a>
                            <form method="post" action="/logout">@csrf<button class="btn alt" type="submit">Logout</button></form>
                        </div>
                    </div>
                @else
                    <a href="/login">Login</a><a class="btn" href="/register">Register</a>
                @endif
            </div>
        </nav>
    </div>
</header>
<main class="wrap">
    @if(session('status'))<div class="notice">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="notice">@foreach($errors->all() as $error)<div class="error">{{ $error }}</div>@endforeach</div>@endif
    @yield('content')
</main>
<footer>
    <div class="wrap">
        <h3>StartupSphere</h3>
        <p>Role-based startup event listing platform with events, startups, mentors, investors, registrations, reviews, and feedback.</p>
        <p>Phone: +91-9876543210 | Email: support@startupsphere.com | Address: Mohali, Punjab, India</p>
    </div>
</footer>
</body>
</html>
