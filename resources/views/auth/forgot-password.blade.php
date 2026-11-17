@extends('layouts.auth')
@section('title', 'Reset password')
@section('content')
<div class="auth-card-header"><h1>Reset admin password</h1><p>Enter your account email to request a reset link. Employees should ask their administrator to reset their PIN.</p></div>
@if(session('status'))<p class="alert alert-success" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.email') }}">@csrf<label class="form-label" for="reset-email">Email</label><input id="reset-email" type="email" name="email" value="{{ old('email') }}" class="form-control mb-3" required autocomplete="email"><button class="btn btn-brand w-100 text-white" type="submit">Send reset link</button></form>
<a class="d-block mt-3" href="{{ route('login') }}">Back to sign in</a>
@endsection
