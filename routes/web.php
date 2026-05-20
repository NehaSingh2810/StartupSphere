<?php

use App\Http\Controllers\StartupSphereController;
use App\Http\Middleware\EnsureStartupUser;
use Illuminate\Support\Facades\Route;

Route::controller(StartupSphereController::class)->group(function () {
    Route::get('/', 'home');
    Route::match(['get', 'post'], '/register', 'register');
    Route::match(['get', 'post'], '/login', 'login');
    Route::post('/logout', 'logout');
    Route::post('/feedback', 'storeFeedback');
    Route::get('/search', 'search');
    Route::get('/startups', 'startups');
    Route::get('/events', 'events');
    Route::get('/events/{slug}', 'eventDetail');

    Route::get('/{page}', 'staticPage')->whereIn('page', ['about', 'success-stories', 'blogs', 'faq', 'contact', 'resources', 'investors', 'privacy', 'terms']);
});

Route::middleware(EnsureStartupUser::class)->controller(StartupSphereController::class)->group(function () {
    Route::get('/dashboard', 'dashboard');
    Route::get('/dashboard/{module}', 'module')->whereIn('module', ['users', 'events', 'startups', 'investors', 'reports', 'investor-requests', 'browse-startups', 'browse-events', 'registered-events', 'saved-startups', 'notifications', 'reviews', 'profile', 'settings']);
    Route::post('/dashboard/events', 'storeEvent');
    Route::post('/dashboard/startups', 'storeStartup');

    Route::post('/events/{slug}/book', 'bookEvent');
    Route::post('/events/{slug}/invest', 'investEvent');
    Route::post('/startups/{slug}/save', 'saveStartup');
    Route::post('/startups/{slug}/interest', 'interestStartup');

    Route::post('/reviews', 'storeReview');
    Route::post('/profile', 'updateProfile');
    Route::post('/settings/password', 'changePassword');
});
