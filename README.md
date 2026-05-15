# StartupSphere

StartupSphere is a Laravel 12 + MongoDB startup events discovery and networking platform.

It includes public pages, login and registration, startup listings, event listings, mentor and investor pages, dashboard modules, event registration, saved startups, feedback, profile editing, and settings with password update.

## Demo Login

- Email: `demo@startupsphere.com`
- Password: `password`

## MongoDB Setup

The app uses the MongoDB PHP library with the MongoDB extension already enabled in XAMPP PHP.

Configure these values in `.env`:

```env
MONGODB_URI=mongodb://127.0.0.1:27017
MONGODB_DATABASE=startupsphere
```

Start MongoDB locally, then the app will create and use these collections as data is viewed or submitted:

- users
- startups
- events
- mentors
- investors
- bookings
- feedbacks

The public listing pages also include seeded demo data so the website looks complete during presentation.

## Pages

Public pages:

- Home
- About
- Startup Listings
- Event Listings
- Event Detail
- Mentors
- Investors
- Success Stories
- Blog
- FAQ
- Contact
- Resources
- Login
- Register

After login:

- Dashboard
- My Events
- Startup Directory
- Networking Hub
- Resource Center
- Discussion Forum
- Feedback
- Profile
- Settings

## Run

```bash
composer install
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```
