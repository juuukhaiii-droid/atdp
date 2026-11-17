@extends('layouts.auth')
@section('title', 'Verify email')
@section('content')
<div class="auth-card-header"><h1>Verify your email</h1><p>Open the verification link sent to your email address.</p></div>
@if(session('status'))<p role="status">{{ session('status') }}</p>@endif
<form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-brand w-100 text-white" type="submit">Resend verification email</button></form>
<form class="mt-3" method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary w-100" type="submit">Sign out</button></form>
@endsection
