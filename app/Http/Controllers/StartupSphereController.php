<?php

namespace App\Http\Controllers;

use App\Services\MongoStore;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StartupSphereController extends Controller
{
    public function __construct(private MongoStore $mongo)
    {
    }

    public function home()
    {
        $events = $this->eventData();
        $recentReviewSlugs = collect(session('reviews', []))
            ->pluck('event_slug')
            ->filter()
            ->reverse()
            ->values();

        if ($recentReviewSlugs->isNotEmpty()) {
            $events = collect($events)
                ->sortBy(fn ($event) => ($index = $recentReviewSlugs->search($event['slug'])) === false ? 999 : $index)
                ->values()
                ->all();
        }

        return view('public.home', [
            'events' => array_slice($events, 0, 4),
            'startups' => array_slice($this->startupData(), 0, 4),
            'investors' => array_slice($this->investorData(), 0, 3),
            'mongoOnline' => $this->mongo->available(),
        ]);
    }

    public function staticPage(string $page)
    {
        $pages = [
            'about' => ['About StartupSphere', 'StartupSphere is a role-based platform that lists startup-related events and keeps students, investors, founders, and admins connected.'],
            'success-stories' => ['Success Stories', 'Read practical stories of founders who validated ideas, joined pitch events, met investors, and found early customers.'],
            'blogs' => ['Startup Blog', 'Guides about MVP building, funding, pitch decks, legal basics, marketing, product launches, and startup growth.'],
            'faq' => ['Frequently Asked Questions', 'Find answers about event registration, ratings, startup reviews, investor requests, and account settings.'],
            'contact' => ['Contact Us', 'Phone: +91-9876543210 | Email: support@startupsphere.com | Address: Mohali, Punjab, India'],
            'resources' => ['Resource Center', 'Download pitch deck templates, funding checklists, legal document samples, business model canvas, and launch plans.'],
            'investors' => ['Startup Investors', 'Investors browse events and startups, send investment interest to admin, and track opportunities without managing platform content.'],
            'privacy' => ['Privacy Policy', 'StartupSphere uses submitted details only for demo account access, event registration, feedback, reviews, investment requests, and saved startup workflows.'],
            'terms' => ['Terms of Service', 'Use StartupSphere as a project demo platform for browsing startup events, managing profiles, and testing ecosystem workflows responsibly.'],
        ];

        abort_unless(isset($pages[$page]), 404);

        return view('public.static', ['page' => $page, 'content' => $pages[$page]]);
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->q);
        $type = $request->type ?: 'all';
        $results = $query === '' ? [] : $this->globalSearch($query, $type);

        return view('public.search', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'total' => count($results),
        ]);
    }

    public function startups(Request $request)
    {
        $items = collect($this->startupData())
            ->when($request->category, fn ($list) => $list->where('category', $request->category))
            ->when($request->q, fn ($list) => $list->filter(fn ($item) => Str::contains(Str::lower($item['name'].' '.$item['category'].' '.$item['city']), Str::lower($request->q))))
            ->values()
            ->all();

        return view('public.listing', [
            'type' => 'startups',
            'title' => 'Startup Listings',
            'items' => $items,
            'categories' => ['AI', 'FinTech', 'EdTech', 'HealthTech', 'SaaS', 'E-commerce', 'GreenTech', 'Cybersecurity'],
        ]);
    }

    public function events(Request $request)
    {
        $items = collect($this->eventData())
            ->when($request->category, fn ($list) => $list->where('category', $request->category))
            ->when($request->city, fn ($list) => $list->filter(fn ($item) => Str::lower($item['city']) === Str::lower($request->city)))
            ->when($request->q, fn ($list) => $list->filter(fn ($item) => Str::contains(Str::lower($item['title'].' '.$item['category'].' '.$item['city']), Str::lower($request->q))))
            ->values()
            ->all();

        return view('public.listing', [
            'type' => 'events',
            'title' => 'Startup Event Listings',
            'items' => $items,
            'categories' => ['Pitch Competition', 'Hackathon', 'Workshop', 'Webinar', 'Investor Meetup', 'Incubation Program'],
        ]);
    }

    public function eventDetail(string $slug)
    {
        $event = collect($this->eventData())->firstWhere('slug', $slug);
        abort_unless($event, 404);

        return view('public.event-detail', [
            'event' => $event,
            'reviews' => collect($this->reviewData())
                ->filter(fn ($review) => ($review['event_slug'] ?? null) === $slug || ($review['target'] ?? null) === $event['title'])
                ->values()
                ->all(),
            'canRegister' => $this->canRegisterForEvents(),
        ]);
    }



    public function register(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('auth.register');
        }

        $data = $request->validate([
            'name' => 'required|min:2',
            'email' => 'required|email',
            'phone' => 'required|min:8',
            'role' => 'required|in:User,Admin,Startup Investor',
            'password' => 'required|min:6|confirmed',
        ]);

        $email = strtolower($data['email']);
        $role = $data['role'];

        if ($email === '123@gmail.com') {
            $data['name'] = 'Nehaa';
            $role = 'Admin';
        } elseif ($role === 'Admin') {
            return back()->withErrors(['role' => 'Admin is fixed to Nehaa using 123@gmail.com.'])->onlyInput('email');
        }

        $user = [
            'name' => $data['name'],
            'email' => $email,
            'phone' => $data['phone'],
            'role' => $role,
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'bio' => 'StartupSphere '.$role.' account.',
            'skills' => 'Startup events, networking, learning',
            'city' => 'Mohali',
        ];

        $this->mongo->insert('users', $user);
        $request->session()->put('startup_user', $user);

        return redirect('/dashboard')->with('status', 'Registration successful. Welcome to StartupSphere.');
    }

    public function login(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('auth.login');
        }

        $data = $request->validate(['email' => 'required|email', 'password' => 'required']);
        $email = strtolower($data['email']);
        $user = $this->mongo->findOne('users', ['email' => $email]);

        if (! $user && $email === 'demo@startupsphere.com' && $data['password'] === 'password') {
            $user = ['name' => 'Demo User', 'email' => $email, 'phone' => '+91-9876543210', 'role' => 'User', 'bio' => 'StartupSphere user account.', 'skills' => 'Events, startups, reviews', 'city' => 'Mohali'];
        }

        if ($email === '123@gmail.com' && $data['password'] === '1234567890') {
            $user = ['name' => 'Nehaa', 'email' => $email, 'phone' => '+91-9000000001', 'role' => 'Admin', 'bio' => 'StartupSphere admin account.', 'skills' => 'Events, users, reports', 'city' => 'Mohali'];
        }

        if (! $user && $email === 'investor@startupsphere.com' && $data['password'] === 'password') {
            $user = ['name' => 'Startup Investor Demo', 'email' => $email, 'phone' => '+91-9000000002', 'role' => 'Startup Investor', 'bio' => 'Reviews event and startup investment opportunities.', 'skills' => 'Funding, diligence, portfolio', 'city' => 'Delhi'];
        }

        if (! $user || (isset($user['password']) && ! password_verify($data['password'], $user['password']))) {
            return back()->withErrors(['email' => 'Invalid email or password. Try demo@startupsphere.com / password.'])->onlyInput('email');
        }

        $user['role'] = $this->normalizeRole($user['role'] ?? 'User');
        if ($email === '123@gmail.com') {
            $user['name'] = 'Nehaa';
            $user['role'] = 'Admin';
        }

        $request->session()->put('startup_user', $user);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('startup_user');

        return redirect('/')->with('status', 'Logged out successfully.');
    }

    public function dashboard()
    {
        $events = $this->eventData();
        $startups = $this->startupData();

        return view('dashboard.index', [
            'events' => array_slice($events, 0, 5),
            'startups' => array_slice($startups, 0, 5),
            'investors' => array_slice($this->investorData(), 0, 4),
            'stats' => $this->dashboardStats($events, $startups),
            'bookings' => session('bookings', []),
            'saved' => $this->mongo->all('saved_startups', []),
            'notifications' => $this->notificationData(),
        ]);
    }

    public function module(string $module)
    {
        $titles = [
            'users' => 'Users',
            'events' => 'Events',
            'startups' => 'Startups',
            'investors' => 'Investors',
            'reports' => 'Reports',
            'my-startup' => 'My Startup',
            'my-events' => 'My Events',
            'investor-requests' => 'Investor Requests',
            'browse-startups' => 'Browse Startups',
            'interested-startups' => 'Interested Startups',
            'my-sessions' => 'My Sessions',
            'startup-requests' => 'Startup Requests',
            'browse-events' => 'Browse Events',
            'registered-events' => 'Registered Events',
            'saved-startups' => 'Saved Startups',
            'notifications' => 'Notifications',
            'reviews' => 'Event Reviews',
            'forum' => 'Discussion Forum',
            'profile' => 'Profile',
            'settings' => 'Settings',
        ];

        abort_unless(isset($titles[$module]), 404);

        $events = $this->eventData();
        $startups = $this->startupData();
        $registrations = $this->registrationData();
        $savedStartupRecords = $this->savedStartupData();
        
        if ($module === 'registered-events') {
            $registeredSlugs = $this->isAdmin()
                ? collect($registrations)->pluck('event_slug')->filter()->all()
                : collect($registrations)
                    ->where('user_email', session('startup_user.email'))
                    ->pluck('event_slug')
                    ->merge(array_keys(session('bookings', [])))
                    ->filter()
                    ->unique()
                    ->all();

            $events = collect($events)->whereIn('slug', $registeredSlugs)->values()->all();
        }

        if ($module === 'saved-startups') {
            $savedSlugs = $this->isAdmin()
                ? collect($savedStartupRecords)->pluck('startup_slug')->filter()->all()
                : collect($savedStartupRecords)
                    ->where('user_email', session('startup_user.email'))
                    ->pluck('startup_slug')
                    ->merge(array_keys(session('saved_startups', [])))
                    ->filter()
                    ->unique()
                    ->all();

            $startups = collect($startups)->whereIn('slug', $savedSlugs)->values()->all();
        }

        if ($module === 'notifications' && ! $this->isAdmin()) {
            return redirect('/dashboard')->with('status', 'Notifications are available only for admin.');
        }

        return view('dashboard.module', [
            'module' => $module,
            'title' => $titles[$module],
            'events' => $events,
            'startups' => $startups,
            'notifications' => $this->notificationData(),
            'reviews' => $this->reviewData(),
            'users' => $this->userData(),
            'investors' => $this->investorData(),
            'eventInvestmentRequests' => $this->eventInvestmentRequestData(),
            'startupInvestmentRequests' => $this->startupInvestmentRequestData(),
            'registrations' => $registrations,
            'savedStartupRecords' => $savedStartupRecords,
            'stats' => $this->dashboardStats($events, $startups),
        ]);
    }

    public function storeEvent(Request $request)
    {
        if (! $this->isAdmin()) {
            return back()->with('status', 'Only admin Nehaa can add events.');
        }

        $data = $request->validate([
            'title' => 'required|min:3',
            'category' => 'required',
            'date' => 'required',
            'venue' => 'required',
            'city' => 'required',
            'organizer' => 'required',
            'seats' => 'required|integer|min:1',
        ]);

        $data['slug'] = Str::slug($data['title']).'-'.Str::random(4);
        $data['time'] = '10:00 AM';
        $data['price'] = 'Free';
        $data['booked'] = 0;
        $data['left'] = (int) $data['seats'];
        $data['rating'] = 4.5;
        $data['description'] = 'Startup event added by admin from the dashboard.';

        $this->mongo->insert('events', $data);

        return back()->with('status', 'Event added successfully.');
    }

    public function storeStartup(Request $request)
    {
        if (! $this->isAdmin()) {
            return back()->with('status', 'Only admin Nehaa can add startups.');
        }

        $data = $request->validate([
            'name' => 'required|min:3',
            'category' => 'required',
            'founder' => 'required',
            'city' => 'required',
            'stage' => 'required',
            'description' => 'required|min:10',
        ]);

        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);
        $data['logo'] = 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=700&q=80';
        $data['valuation'] = '20 Cr';
        $data['rating'] = 4.4;
        $data['verified'] = true;
        $data['funding_raised'] = 20;
        $data['funding_goal'] = 50;
        $data['views'] = 0;
        $data['investor_clicks'] = 0;
        $data['saves'] = 0;
        $data['innovation_score'] = 70;
        $data['pitch_video'] = 'Startup pitch video pending';
        $data['fraud_score'] = 8;

        $this->mongo->insert('startups', $data);

        return back()->with('status', 'Startup added successfully.');
    }



    public function bookEvent(Request $request, string $slug)
    {
        if (! $this->canRegisterForEvents()) {
            return back()->with('status', 'Only users can register for events. Startup investors can send investment requests.');
        }

        $bookings = session('bookings', []);
        if (isset($bookings[$slug])) {
            return back()->with('status', 'You are already registered for this event.');
        }

        $event = collect($this->eventData())->firstWhere('slug', $slug);
        abort_unless($event, 404);

        if (($event['left'] ?? 0) <= 0) {
            return back()->with('status', 'No seats are left for this event.');
        }

        $bookings[$slug] = $slug;
        session(['bookings' => $bookings]);

        $updatedBooked = ($event['booked'] ?? 0) + 1;
        $updatedLeft = max(0, ($event['left'] ?? 0) - 1);
        $user = session('startup_user', []);

        $persisted = $this->mongo->updateOne('events', ['slug' => $slug], ['booked' => $updatedBooked, 'left' => $updatedLeft]);
        if (! $persisted) {
            $seatChanges = session('event_seat_changes', []);
            $seatChanges[$slug] = ($seatChanges[$slug] ?? 0) + 1;
            session(['event_seat_changes' => $seatChanges]);
        }

        $registrationData = [
            'event_slug' => $slug,
            'event_title' => $event['title'] ?? $slug,
            'user_email' => $user['email'] ?? null,
            'user_name' => $user['name'] ?? 'User',
            'user_role' => $user['role'] ?? null,
            'status' => 'Registered',
            'created_at' => now()->toDateTimeString(),
        ];

        $this->mongo->insert('registrations', $registrationData);
        $registrations = session('registrations', []);
        array_unshift($registrations, $registrationData);
        session(['registrations' => array_slice($registrations, 0, 50)]);

        $this->notifyAdmin($this->userLabel($user).' registered for event '.$event['title'].'. Seats left: '.$updatedLeft, [
            'event_slug' => $slug,
            'event_title' => $event['title'],
            'user_email' => $user['email'] ?? null,
            'user_name' => $user['name'] ?? 'User',
            'user_role' => $user['role'] ?? null,
            'user_phone' => $user['phone'] ?? null,
            'user_city' => $user['city'] ?? null,
            'seats_left' => $updatedLeft,
        ]);

        return back()->with('status', 'Event registered successfully.');
    }

    public function saveStartup(Request $request, string $slug)
    {
        $startup = collect($this->startupData())->firstWhere('slug', $slug);
        
        if ($startup) {
            $saved = session('saved_startups', []);
            $saved[$slug] = $slug;
            session(['saved_startups' => $saved]);
            $user = session('startup_user', []);

            $savedStartupData = [
                'startup_slug' => $slug,
                'startup_title' => $startup['name'],
                'user_email' => $user['email'] ?? null,
                'user_name' => $user['name'] ?? 'User',
                'user_role' => $user['role'] ?? null,
                'created_at' => now()->toDateTimeString(),
            ];

            $this->mongo->insert('saved_startups', $savedStartupData);
            $savedRecords = session('saved_startups_records', []);
            array_unshift($savedRecords, $savedStartupData);
            session(['saved_startups_records' => array_slice($savedRecords, 0, 50)]);

            $this->notifyAdmin($this->userLabel($user).' saved startup '.$startup['name'].'.', [
                'type' => 'startup_saved',
                'startup_slug' => $slug,
                'startup_title' => $startup['name'],
                'user_email' => $user['email'] ?? null,
                'user_name' => $user['name'] ?? 'User',
                'user_role' => $user['role'] ?? null,
                'user_phone' => $user['phone'] ?? null,
                'user_city' => $user['city'] ?? null,
            ]);
        }

        return back()->with('status', 'Startup saved to your dashboard.');
    }

    public function interestStartup(Request $request, string $slug)
    {
        if (! $this->isStartupInvestor()) {
            return back()->with('status', 'Only startup investors can send investment interest.');
        }

        $startup = collect($this->startupData())->firstWhere('slug', $slug);

        if ($startup) {
            $user = session('startup_user', []);
            $requestData = [
                'startup_slug' => $slug,
                'startup_title' => $startup['name'],
                'user_email' => $user['email'] ?? null,
                'user_name' => $user['name'] ?? 'Startup Investor',
                'status' => 'Interested',
                'created_at' => now()->toDateTimeString(),
            ];

            $this->mongo->insert('startup_interests', $requestData);
            $requests = session('startup_interests', []);
            array_unshift($requests, $requestData);
            session(['startup_interests' => array_slice($requests, 0, 20)]);

            $this->notifyAdmin(($user['name'] ?? 'Startup Investor').' wants to invest in startup '.$startup['name'].'.', [
                'type' => 'startup_investment_request',
                'startup_slug' => $slug,
                'startup_title' => $startup['name'],
                'user_email' => $user['email'] ?? null,
                'user_name' => $user['name'] ?? 'Startup Investor',
            ]);
        }

        return back()->with('status', 'Investment interest sent to admin.');
    }

    public function investEvent(Request $request, string $slug)
    {
        if (! $this->isStartupInvestor()) {
            return back()->with('status', 'Only startup investors can send investment requests.');
        }

        $event = collect($this->eventData())->firstWhere('slug', $slug);
        abort_unless($event, 404);

        $user = session('startup_user', []);
        $requestData = [
            'event_slug' => $slug,
            'event_title' => $event['title'],
            'user_email' => $user['email'] ?? null,
            'user_name' => $user['name'] ?? 'Startup Investor',
            'status' => 'Requested',
            'created_at' => now()->toDateTimeString(),
        ];

        $this->mongo->insert('event_investment_requests', $requestData);
        $requests = session('event_investment_requests', []);
        array_unshift($requests, $requestData);
        session(['event_investment_requests' => array_slice($requests, 0, 20)]);

        $this->notifyAdmin(($user['name'] ?? 'Startup Investor').' wants to invest in event '.$event['title'].'.', $requestData + [
            'type' => 'event_investment_request',
        ]);

        return back()->with('status', 'Investment request sent to admin.');
    }



    public function storeReview(Request $request)
    {
        $data = $request->validate([
            'event_slug' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|min:5',
        ]);

        $event = collect($this->eventData())->firstWhere('slug', $data['event_slug']);
        abort_unless($event, 404);

        $review = $data + [
            'target' => $event['title'],
            'user_email' => session('startup_user.email'),
            'user_name' => session('startup_user.name'),
            'user_role' => session('startup_user.role'),
        ];

        $reviews = session('reviews', []);
        $reviews[] = $review;
        session(['reviews' => $reviews]);

        $this->mongo->insert('reviews', $review);
        $this->notifyAdmin($this->userLabel(session('startup_user', [])).' reviewed event '.$event['title'].' with '.$data['rating'].' stars.', [
            'type' => 'event_review',
            'event_slug' => $data['event_slug'],
            'event_title' => $event['title'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'user_email' => session('startup_user.email'),
            'user_name' => session('startup_user.name'),
            'user_role' => session('startup_user.role'),
            'user_phone' => session('startup_user.phone'),
            'user_city' => session('startup_user.city'),
        ]);

        return back()->with('status', 'Review submitted successfully.');
    }

    private function canRegisterForEvents(): bool
    {
        return session('startup_user.role') === 'User';
    }

    private function isStartupInvestor(): bool
    {
        return session('startup_user.role') === 'Startup Investor';
    }

    private function isAdmin(): bool
    {
        return session('startup_user.role') === 'Admin' && session('startup_user.email') === '123@gmail.com';
    }

    private function normalizeRole(string $role): string
    {
        return match ($role) {
            'Admin' => 'Admin',
            'Investor', 'Startup Investor' => 'Startup Investor',
            default => 'User',
        };
    }

    private function userLabel(array $user): string
    {
        $name = $user['name'] ?? 'User';
        $email = $user['email'] ?? 'no email';
        $role = $user['role'] ?? 'User';
        $phone = $user['phone'] ?? 'no phone';
        $city = $user['city'] ?? 'no city';

        return "{$name} ({$email}, {$role}, {$phone}, {$city})";
    }

    public function storeFeedback(Request $request)
    {
        $data = $request->validate(['name' => 'required|min:2', 'email' => 'required|email', 'subject' => 'required|min:3', 'message' => 'required|min:10']);
        $this->mongo->insert('feedbacks', $data + ['submitted_at' => date('Y-m-d H:i:s')]);

        return back()->with('status', 'Thank you for your feedback! We appreciate your input.');
    }

    public function feedback(Request $request)
    {
        $data = $request->validate(['subject' => 'required|min:3', 'rating' => 'required|integer|min:1|max:5', 'message' => 'required|min:5']);
        $this->mongo->insert('feedbacks', $data + ['user_email' => session('startup_user.email')]);

        return back()->with('status', 'Thank you. Your feedback has been submitted.');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate(['name' => 'required', 'phone' => 'required', 'city' => 'required', 'bio' => 'required', 'skills' => 'required']);
        $user = array_merge(session('startup_user', []), $data);
        session(['startup_user' => $user]);
        $this->mongo->updateOne('users', ['email' => $user['email']], $data);

        return back()->with('status', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate(['password' => 'required|min:6|confirmed']);
        $user = session('startup_user', []);
        $user['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        session(['startup_user' => $user]);
        $this->mongo->updateOne('users', ['email' => $user['email']], ['password' => $user['password']]);

        return back()->with('status', 'Password changed successfully.');
    }

    private function startupData(): array
    {
        $categories = ['AI', 'FinTech', 'EdTech', 'HealthTech', 'SaaS', 'E-commerce', 'GreenTech', 'Cybersecurity', 'AgriTech', 'DeepTech', 'Logistics', 'FoodTech'];
        $names = ['NeuralX', 'PayNova', 'EduBridge', 'HealthMate', 'CloudDesk', 'ShopEase', 'GreenGrid', 'SecureByte', 'AgriPulse', 'VisionAI', 'FounderOS', 'MediCore', 'TalentFlow', 'MarketMint', 'EcoPulse', 'TraceChain', 'StudySphere', 'WellNest', 'FarmLink', 'DataDock', 'FinanceFox', 'RetailRun'];
        $items = [];

        foreach ($names as $index => $name) {
            $category = $categories[$index % count($categories)];
            $goal = 40 + ($index * 8);
            $raised = min($goal, 15 + ($index * 6));
            $logos = [
                'https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1551650975-87deedd944c3?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1552667466-07770ae110d0?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1573497019424-2b6f74a48e9f?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1545239351-1141bd82e8a6?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=700&q=80',
            ];
            $items[] = [
                'slug' => Str::slug($name),
                'name' => $name,
                'logo' => $logos[$index % count($logos)],
                'category' => $category,
                'founder' => ['Aarav Mehta', 'Neha Sharma', 'Kabir Singh', 'Riya Kapoor'][$index % 4],
                'city' => ['Mohali', 'Bengaluru', 'Delhi', 'Mumbai'][$index % 4],
                'stage' => ['Idea', 'MVP', 'Seed', 'Series A'][$index % 4],
                'valuation' => (25 + $index * 8).' Cr',
                'rating' => round(4 + (($index % 10) / 10), 1),
                'verified' => $index % 3 !== 1,
                'funding_raised' => $raised,
                'funding_goal' => $goal,
                'views' => 1200 + ($index * 180),
                'investor_clicks' => 35 + ($index * 9),
                'saves' => 80 + ($index * 12),
                'innovation_score' => 78 + ($index % 18),
                'description' => $category.' startup solving practical business problems for Indian founders and teams.',
                'pitch_video' => $index % 2 === 0 ? 'Founder pitch video uploaded' : 'Product demo pending',
                'fraud_score' => max(2, 18 - $index),
            ];
        }

        return array_map(function ($startup) {
            $raised = $startup['funding_raised'] ?? 20;
            $goal = $startup['funding_goal'] ?? 50;

            return $startup + [
                'slug' => Str::slug($startup['name'] ?? 'startup'),
                'logo' => 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=700&q=80',
                'category' => 'SaaS',
                'founder' => 'Demo Founder',
                'city' => 'Mohali',
                'stage' => 'Seed',
                'valuation' => '25 Cr',
                'rating' => 4.6,
                'verified' => false,
                'funding_raised' => $raised,
                'funding_goal' => $goal,
                'views' => 1200,
                'investor_clicks' => 35,
                'saves' => 80,
                'innovation_score' => 78,
                'description' => 'Startup solving practical business problems for founders and teams.',
                'pitch_video' => 'Founder pitch video pending',
                'fraud_score' => 8,
            ];
        }, $this->mongo->all('startups', $items));
    }

    private function eventData(): array
    {
        $categories = ['Pitch Competition', 'Hackathon', 'Workshop', 'Webinar', 'Investor Meetup', 'Incubation Program'];
        $titles = ['Startup Pitch Night', 'AI Innovation Summit', 'FinTech Expo', 'Founder Meetup', 'MVP Build Sprint', 'Investor Connect Day', 'Women Founder Forum', 'SaaS Growth Lab', 'HealthTech Demo Day', 'Campus Startup Fest', 'Legal Basics for Startups', 'Product Launch Bootcamp', 'Growth Marketing Clinic', 'Blockchain Startup Jam', 'EdTech Idea Lab', 'GreenTech Roundtable', 'Series A Prep Workshop', 'Startup Finance Seminar', 'Founders Retreat', 'Startup Demo Day'];
        $items = [];

        foreach ($titles as $index => $title) {
            $seats = 100 + ($index * 8);
            $booked = 45 + ($index * 5);
            $images = [
                'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1551650975-87deedd944c3?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1552667466-07770ae110d0?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1485217988980-11786ced9454?auto=format&fit=crop&w=900&q=80',
            ];
            $items[] = [
                'slug' => Str::slug($title),
                'title' => $title,
                'image' => $images[$index % count($images)],
                'category' => $categories[$index % count($categories)],
                'date' => now()->addDays($index + 3)->format('d M Y'),
                'time' => '10:00 AM',
                'city' => ['Mohali', 'Chandigarh', 'Bengaluru', 'Delhi'][$index % 4],
                'venue' => ['T-Hub Hall', 'Innovation Campus', 'Startup Studio', 'Virtual'][$index % 4],
                'organizer' => ['StartupSphere', 'Founder Club', 'Angel Network'][$index % 3],
                'price' => $index % 3 === 0 ? 'Free' : 'Rs '.(499 + $index * 100),
                'seats' => $seats,
                'booked' => $booked,
                'left' => max(0, $seats - $booked),
                'reminder' => $index < 4 ? '24h and 1h reminders enabled' : 'Reminder available after registration',
                'rating' => round(4.1 + (($index % 8) / 10), 1),
                'description' => 'A practical startup ecosystem event with founders, students, investors, product builders, and networking opportunities.',
            ];
        }

        return array_map(function ($event) {
            $seats = $event['seats'] ?? 100;
            $booked = $event['booked'] ?? max(0, $seats - ($event['left'] ?? 25));
            $seatChange = session('event_seat_changes.'.$event['slug'], 0);
            $booked += $seatChange;
            $event['booked'] = $booked;
            $event['left'] = max(0, $seats - $booked);
            $reviewRating = $this->eventReviewRating($event['slug'], $event['title'] ?? null);
            if ($reviewRating !== null) {
                $event['rating'] = $reviewRating;
            }

            return $event + [
                'slug' => Str::slug($event['title'] ?? 'event'),
                'image' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=900&q=80',
                'category' => 'Networking',
                'date' => now()->addDays(7)->format('d M Y'),
                'time' => '10:00 AM',
                'city' => 'Mohali',
                'venue' => 'Innovation Campus',
                'organizer' => 'StartupSphere',
                'price' => 'Free',
                'seats' => $seats,
                'booked' => $booked,
                'left' => $event['left'],
                'reminder' => '24h and 1h reminders available after registration',
                'rating' => 4.5,
                'description' => 'A practical startup ecosystem event with founders, students, investors, product builders, and networking opportunities.',
            ];
        }, $this->mongo->all('events', $items));
    }

    private function eventReviewRating(string $slug, ?string $title = null): ?float
    {
        $ratings = collect($this->reviewData())
            ->filter(fn ($review) => ($review['event_slug'] ?? null) === $slug || ($title && ($review['target'] ?? null) === $title))
            ->pluck('rating')
            ->filter(fn ($rating) => is_numeric($rating))
            ->map(fn ($rating) => (int) $rating);

        if ($ratings->isEmpty()) {
            return null;
        }

        return round($ratings->avg(), 1);
    }

    private function investorData(): array
    {
        return $this->mongo->all('investors', [
            ['name' => 'NorthStar Ventures', 'expertise' => 'Seed to Series A', 'industry' => 'AI', 'experience' => 'Rs 25L - Rs 5Cr', 'sessions' => 42, 'rating' => 4.8, 'verified' => true],
            ['name' => 'Punjab Angel Network', 'expertise' => 'Early Stage', 'industry' => 'FinTech', 'experience' => 'Rs 10L - Rs 2Cr', 'sessions' => 78, 'rating' => 4.7, 'verified' => true],
            ['name' => 'Catalyst Capital', 'expertise' => 'SaaS and AI', 'industry' => 'SaaS', 'experience' => 'Rs 1Cr - Rs 10Cr', 'sessions' => 35, 'rating' => 4.9, 'verified' => true],
        ]);
    }

    private function eventInvestmentRequestData(): array
    {
        return $this->mongo->all('event_investment_requests', session('event_investment_requests', []));
    }

    private function startupInvestmentRequestData(): array
    {
        return $this->mongo->all('startup_interests', session('startup_interests', []));
    }

    private function registrationData(): array
    {
        return $this->mongo->all('registrations', session('registrations', []));
    }

    private function savedStartupData(): array
    {
        return $this->mongo->all('saved_startups', session('saved_startups_records', []));
    }

    private function globalSearch(string $query, string $type = 'all'): array
    {
        $query = Str::lower($query);
        $sources = [
            'events' => collect($this->eventData())->map(fn ($item) => [
                'type' => 'Event',
                'title' => $item['title'],
                'subtitle' => $item['category'].' | '.$item['city'].' | '.$item['date'],
                'description' => $item['description'],
                'url' => '/events/'.$item['slug'],
                'meta' => 'Seats left: '.$item['left'].' | Rating '.$item['rating'],
                'search' => $item['title'].' '.$item['category'].' '.$item['city'].' '.$item['venue'].' '.$item['description'],
            ]),
            'startups' => collect($this->startupData())->map(fn ($item) => [
                'type' => 'Startup',
                'title' => $item['name'],
                'subtitle' => $item['category'].' | '.$item['stage'].' | '.$item['city'],
                'description' => $item['description'],
                'url' => '/startups?q='.urlencode($item['name']),
                'meta' => 'Funding Rs '.$item['funding_raised'].'L / Rs '.$item['funding_goal'].'L | Rating '.$item['rating'],
                'search' => $item['name'].' '.$item['category'].' '.$item['stage'].' '.$item['city'].' '.$item['founder'].' '.$item['description'],
            ]),
            'people' => collect($this->investorData())->map(fn ($item) => [
                'type' => 'Investor',
                'title' => $item['name'],
                'subtitle' => ($item['industry'] ?? 'Startup').' | '.$item['expertise'],
                'description' => 'Verified startup investor for funding interest, event sponsorship, and startup profile review.',
                'url' => '/investors',
                'meta' => $item['experience'].' | Rating '.$item['rating'],
                'search' => $item['name'].' '.$item['expertise'].' '.($item['industry'] ?? '').' '.$item['experience'],
            ]),
        ];

        return collect($sources)
            ->when($type !== 'all', fn ($collection) => $collection->only($type))
            ->flatMap(fn ($items) => $items)
            ->map(function ($item) use ($query) {
                $haystack = Str::lower($item['search']);
                $score = 0;
                foreach (preg_split('/\s+/', $query) ?: [] as $term) {
                    if ($term === '') {
                        continue;
                    }
                    $score += Str::contains(Str::lower($item['title']), $term) ? 40 : 0;
                    $score += Str::contains($haystack, $term) ? 20 : 0;
                    $score += Str::startsWith(Str::lower($item['title']), $term) ? 20 : 0;
                }
                $item['score'] = $score;

                return $item;
            })
            ->filter(fn ($item) => $item['score'] > 0)
            ->sortByDesc('score')
            ->values()
            ->all();
    }

    private function notificationData(): array
    {
        $default = [
            'New startup event added: AI Innovation Summit.',
            'Your event registration is confirmed.',
            'Investor requests appear here for admin review.',
            'New startup profile approved by admin.',
        ];

        if (session('startup_user.role') !== 'Admin') {
            return [];
        }

        $adminEmail = session('startup_user.email', '123@gmail.com');
        $stored = collect($this->mongo->all('admin_notifications', [], ['admin_email' => $adminEmail]))
            ->pluck('message')
            ->all();

        return array_merge(session('admin_notifications', []), $stored, $default);
    }

    private function notifyAdmin(string $message, array $data = []): void
    {
        $notification = $data + [
            'admin_email' => '123@gmail.com',
            'message' => $message,
            'type' => 'event_registration',
        ];

        $notifications = session('admin_notifications', []);
        array_unshift($notifications, $message);
        session(['admin_notifications' => array_slice($notifications, 0, 20)]);

        $this->mongo->insert('admin_notifications', $notification);
    }

    private function dashboardStats(array $events, array $startups): array
    {
        return [
            'events' => count($events),
            'startups' => count($startups),
            'investors' => count($this->investorData()),
            'users' => count($this->userData()),
        ];
    }

    private function userData(): array
    {
        return $this->mongo->all('users', [
            ['name' => 'Nehaa', 'email' => '123@gmail.com', 'phone' => '+91-9000000001', 'role' => 'Admin', 'city' => 'Mohali'],
            ['name' => 'Demo User', 'email' => 'demo@startupsphere.com', 'phone' => '+91-9876543210', 'role' => 'User', 'city' => 'Mohali'],
            ['name' => 'Startup Investor Demo', 'email' => 'investor@startupsphere.com', 'phone' => '+91-9000000002', 'role' => 'Startup Investor', 'city' => 'Delhi'],
        ]);
    }

    private function reviewData(): array
    {
        $fallback = [
            ['event_slug' => 'startup-pitch-night', 'target' => 'Startup Pitch Night', 'rating' => 5, 'comment' => 'Useful event for learning how startup pitches work.', 'user_email' => 'student@startupsphere.com'],
            ['event_slug' => 'ai-innovation-summit', 'target' => 'AI Innovation Summit', 'rating' => 4, 'comment' => 'Strong event for startup networking and product demos.', 'user_email' => 'student@startupsphere.com'],
        ];

        return array_merge($this->mongo->all('reviews', $fallback), session('reviews', []));
    }

    private function feedbackData(): array
    {
        return $this->mongo->all('feedbacks', [
            ['subject' => 'Event listing', 'rating' => 5, 'message' => 'The events are easy to browse and register for.', 'user_email' => 'demo@startupsphere.com'],
            ['subject' => 'Investor section', 'rating' => 4, 'message' => 'Investor request flow is simple and useful.', 'user_email' => 'student@startupsphere.com'],
        ]);
    }

}
