@extends('layouts.app')
@section('content')
<form class="card auth" method="post" action="/login">@csrf
    <h1>Login</h1>
    <p>Demo account: demo@startupsphere.com / password</p>
    <label>Email</label><input name="email" type="email" value="{{ old('email') }}" required>
    <label>Password</label><input name="password" type="password" required>
    <p><label><input type="checkbox" style="width:auto"> Remember me</label></p>
    <button class="btn">Login</button> <a class="btn light" href="/register">Create Account</a>
    <p><a href="/contact">Forgot password?</a></p>
</form>
@endsection
