@extends('layouts.app')
@section('content')
<form class="card auth" method="post" action="/register">@csrf
    <h1>Create Account</h1>
    <p>Select your role. Users register for events, startup investors send investment requests, and the admin account is fixed to Nehaa.</p>
    <label>Name</label><input name="name" value="{{ old('name') }}" required>
    <label>Email</label><input name="email" type="email" value="{{ old('email') }}" required>
    <label>Phone</label><input name="phone" value="{{ old('phone') }}" required>
    <label>Role</label>
    <select name="role">
        <option>User</option>
        <option>Startup Investor</option>
    </select>
    <label>Password</label><input name="password" type="password" required>
    <label>Confirm Password</label><input name="password_confirmation" type="password" required>
    <br><br><button class="btn">Register</button> <a class="btn light" href="/login">Login</a>
</form>
@endsection
