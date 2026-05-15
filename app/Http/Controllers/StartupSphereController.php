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
        return view('public.home', [
            'events' => array_slice($this->eventData(), 0, 4),
            'startups' => array_slice($this->startupData(), 0, 4),
            'mentors' => array_slice($this->mentorData(), 0, 3),
            'investors' => array_slice($this->investorData(), 0, 3),
            'mongoOnline' => $this->mongo->available(),
        ]);
    }

    public function staticPage(string $page)
    {
        $pages = [
            'about' => ['About StartupSphere', 'StartupSphere is a role-based platform that lists startup-related events and helps founders, investors, mentors, and students participate in the startup ecosystem.'],
            'success-stories' => ['Success Stories', 'Read practical stories of founders who validated ideas, met mentors, joined pitch events, and found early customers.'],
            'blogs' => ['Startup Blog', 'Guides about MVP building, funding, pitch decks, legal basics, marketing, product launches, and startup growth.'],
            'faq' => ['Frequently Asked Questions', 'Find answers about event registration, ratings, startup reviews, mentor sessions, investor connections, and account settings.'],
            'contact' => ['Contact Us', 'Phone: +91-9876543210 | Email: support@startupsphere.com | Address: Mohali, Punjab, India'],
            'resources' => ['Resource Center', 'Download pitch deck templates, funding checklists, legal document samples, business model canvas, and launch plans.'],
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
            ->when($request->q, fn ($list) => $list->filter(fn ($item) => Str::contains(Str::lower($item['title'].' '.$item['category'].' '.$item['city']), Str::lower($request->q))))
            ->values()
            ->all();

        return view('public.listing', [
            'type' => 'events',
            'title' => 'Startup Event Listings',
            'items' => $items,
            'categories' => ['Workshop', 'Hackathon', 'Pitch Competition', 'Investor Meet', 'Webinar', 'Networking'],
        ]);
    }

    public function eventDetail(string $slug)
    {
        $event = collect($this->eventData())->firstWhere('slug', $slug);
        abort_unless($event, 404);

        return view('public.event-detail', [
            'event' => $event,
            'reviews' => array_slice($this->reviewData(), 0, 4),
        ]);
    }

    public function mentors()
    {
        return view('public.people', ['title' => 'Mentors', 'people' => $this->mentorData(), 'kind' => 'mentor']);
    }

    public function investors()
    {
        return view('public.people', ['title' => 'Investors', 'people' => $this->investorData(), 'kind' => 'investor']);
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
            'role' => 'required|in:Admin,Startup Founder,Investor',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'],
            'role' => $data['role'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'bio' => 'StartupSphere '.$data['role'].' account.',
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
            $user = ['name' => 'Demo Founder', 'email' => $email, 'phone' => '+91-9876543210', 'role' => 'Startup Founder', 'bio' => 'Building a SaaS startup.', 'skills' => 'Product, events, fundraising', 'city' => 'Mohali'];
        }

        if (! $user || (isset($user['password']) && ! password_verify($data['password'], $user['password']))) {
            return back()->withErrors(['email' => 'Invalid email or password. Try demo@startupsphere.com / password.'])->onlyInput('email');
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
            'mentors' => array_slice($this->mentorData(), 0, 4),
            'investors' => array_slice($this->investorData(), 0, 4),
            'stats' => $this->dashboardStats($events, $startups),
            'bookings' => session('bookings', []),
            'saved' => session('saved_startups', []),
            'notifications' => $this->notificationData(),
        ]);
    }

    public function module(string $module)
    {
        $titles = [
            'users' => 'Users',
            'events' => 'Events',
            'startups' => 'Startups',
            'mentors' => 'Mentors',
            'investors' => 'Investors',
            'feedback' => 'Feedback',
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
            'certificates' => 'Certificates',
            'notifications' => 'Notifications',
            'reviews' => 'Ratings and Reviews',
            'forum' => 'Discussion Forum',
            'profile' => 'Profile',
            'settings' => 'Settings',
        ];

        abort_unless(isset($titles[$module]), 404);

        $events = $this->eventData();
        $startups = $this->startupData();

        return view('dashboard.module', [
            'module' => $module,
            'title' => $titles[$module],
            'events' => $events,
            'startups' => $startups,
            'mentors' => $this->mentorData(),
            'investors' => $this->investorData(),
            'notifications' => $this->notificationData(),
            'reviews' => $this->reviewData(),
            'feedbacks' => $this->feedbackData(),
            'users' => $this->userData(),
            'stats' => $this->dashboardStats($events, $startups),
        ]);
    }

    public function storeEvent(Request $request)
    {
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

    public function storeMentor(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|min:3',
            'expertise' => 'required|min:3',
            'industry' => 'required|min:3',
            'experience' => 'required|min:3',
            'rating' => 'required|numeric|min:1|max:5',
        ]);

        $data['verified'] = true;
        $data['sessions'] = 0;

        $this->mongo->insert('mentors', $data);

        return back()->with('status', 'Mentor added successfully.');
    }

    public function storeInvestor(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|min:3',
            'expertise' => 'required|min:3',
            'industry' => 'required|min:3',
            'experience' => 'required|min:3',
            'rating' => 'required|numeric|min:1|max:5',
        ]);

        $data['verified'] = true;
        $this->mongo->insert('investors', $data);

        return back()->with('status', 'Investor added successfully.');
    }

    public function bookEvent(Request $request, string $slug)
    {
        $bookings = session('bookings', []);
        $bookings[$slug] = $slug;
        session(['bookings' => $bookings]);
        $event = collect($this->eventData())->firstWhere('slug', $slug);
        $this->mongo->insert('registrations', ['event_slug' => $slug, 'event_title' => $event['title'] ?? $slug, 'user_email' => session('startup_user.email'), 'status' => 'Registered']);

        return back()->with('status', 'Event registered successfully.');
    }

    public function saveStartup(Request $request, string $slug)
    {
        $saved = session('saved_startups', []);
        $saved[$slug] = $slug;
        session(['saved_startups' => $saved]);

        return back()->with('status', 'Startup saved to your dashboard.');
    }

    public function investorInterest(Request $request, string $slug)
    {
        $interests = session('investor_interests', []);
        $interests[$slug] = ($interests[$slug] ?? 0) + 1;
        session(['investor_interests' => $interests]);

        return back()->with('status', 'Startup marked as interested.');
    }

    public function storeReview(Request $request)
    {
        $data = $request->validate([
            'target' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|min:5',
        ]);

        $this->mongo->insert('reviews', $data + ['user_email' => session('startup_user.email')]);

        return back()->with('status', 'Review submitted successfully.');
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
        $categories = ['Workshop', 'Hackathon', 'Pitch Competition', 'Investor Meet', 'Webinar', 'Networking', 'Demo Day', 'Roundtable', 'Masterclass'];
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
                'description' => 'A practical startup ecosystem event with founders, mentors, investors, product builders, and networking opportunities.',
            ];
        }

        return array_map(function ($event) {
            $seats = $event['seats'] ?? 100;
            $booked = $event['booked'] ?? max(0, $seats - ($event['left'] ?? 25));

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
                'left' => max(0, $seats - $booked),
                'reminder' => '24h and 1h reminders available after registration',
                'rating' => 4.5,
                'description' => 'A practical startup ecosystem event with founders, mentors, investors, product builders, and networking opportunities.',
            ];
        }, $this->mongo->all('events', $items));
    }

    private function mentorData(): array
    {
        return $this->mongo->all('mentors', [
            ['name' => 'Ananya Rao', 'expertise' => 'Startup Strategy', 'industry' => 'AI', 'experience' => '12 years', 'sessions' => 240, 'rating' => 4.9, 'verified' => true],
            ['name' => 'Vikram Sethi', 'expertise' => 'Fundraising', 'industry' => 'FinTech', 'experience' => '15 years', 'sessions' => 310, 'rating' => 4.8, 'verified' => true],
            ['name' => 'Meera Iyer', 'expertise' => 'Product Growth', 'industry' => 'SaaS', 'experience' => '10 years', 'sessions' => 180, 'rating' => 4.7, 'verified' => true],
            ['name' => 'Rohan Batra', 'expertise' => 'Marketing', 'industry' => 'E-commerce', 'experience' => '9 years', 'sessions' => 160, 'rating' => 4.6, 'verified' => false],
        ]);
    }

    private function investorData(): array
    {
        return $this->mongo->all('investors', [
            ['name' => 'NorthStar Ventures', 'expertise' => 'Seed to Series A', 'industry' => 'AI', 'experience' => 'Rs 25L - Rs 5Cr', 'sessions' => 42, 'rating' => 4.8, 'verified' => true],
            ['name' => 'Punjab Angel Network', 'expertise' => 'Early Stage', 'industry' => 'FinTech', 'experience' => 'Rs 10L - Rs 2Cr', 'sessions' => 78, 'rating' => 4.7, 'verified' => true],
            ['name' => 'Catalyst Capital', 'expertise' => 'SaaS and AI', 'industry' => 'SaaS', 'experience' => 'Rs 1Cr - Rs 10Cr', 'sessions' => 35, 'rating' => 4.9, 'verified' => true],
        ]);
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
            'people' => collect(array_merge($this->mentorData(), $this->investorData()))->map(fn ($item) => [
                'type' => 'Expert',
                'title' => $item['name'],
                'subtitle' => ($item['industry'] ?? 'Startup').' | '.$item['expertise'],
                'description' => 'Verified startup ecosystem expert for guidance, funding, and network support.',
                'url' => Str::contains(Str::lower($item['expertise']), 'fund') || Str::contains(Str::lower($item['expertise']), 'seed') ? '/investors' : '/mentors',
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
        return [
            'New startup event added: AI Innovation Summit.',
            'Your event registration is confirmed.',
            'A mentor request was accepted.',
            'New startup profile approved by admin.',
        ];
    }

    private function dashboardStats(array $events, array $startups): array
    {
        return [
            'events' => count($events),
            'startups' => count($startups),
            'mentors' => count($this->mentorData()),
            'investors' => count($this->investorData()),
            'users' => count($this->userData()),
        ];
    }

    private function userData(): array
    {
        return $this->mongo->all('users', [
            ['name' => 'Admin User', 'email' => 'admin@startupsphere.com', 'phone' => '+91-9000000001', 'role' => 'Admin', 'city' => 'Mohali'],
            ['name' => 'Demo Founder', 'email' => 'demo@startupsphere.com', 'phone' => '+91-9876543210', 'role' => 'Startup Founder', 'city' => 'Mohali'],
            ['name' => 'Investor Demo', 'email' => 'investor@startupsphere.com', 'phone' => '+91-9000000002', 'role' => 'Investor', 'city' => 'Delhi'],
        ]);
    }

    private function reviewData(): array
    {
        return $this->mongo->all('reviews', [
            ['target' => 'Startup Pitch Night', 'rating' => 5, 'comment' => 'Useful event for learning how startup pitches work.', 'user_email' => 'student@startupsphere.com'],
            ['target' => 'NeuralX', 'rating' => 4, 'comment' => 'Strong AI startup profile with clear founder details.', 'user_email' => 'investor@startupsphere.com'],
        ]);
    }

    private function feedbackData(): array
    {
        return $this->mongo->all('feedbacks', [
            ['subject' => 'Event listing', 'rating' => 5, 'message' => 'The events are easy to browse and register for.', 'user_email' => 'demo@startupsphere.com'],
            ['subject' => 'Mentor section', 'rating' => 4, 'message' => 'Mentor profiles are simple and useful.', 'user_email' => 'student@startupsphere.com'],
        ]);
    }

}
