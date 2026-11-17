@extends('layouts.auth')
@section('title', 'New password')
@section('content')
<div class="auth-card-header"><h1>Set a new password</h1></div>
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.store') }}">@csrf
<input type="hidden" name="token" value="{{ $request->route('token') }}">
<label class="form-label" for="reset-email">Email</label><input id="reset-email" type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control mb-3" required autocomplete="email">
<label class="form-label" for="new-password">New password</label><input id="new-password" type="password" name="password" class="form-control mb-3" required autocomplete="new-password">
<label class="form-label" for="confirm-password">Confirm password</label><input id="confirm-password" type="password" name="password_confirmation" class="form-control mb-3" required autocomplete="new-password">
<button class="btn btn-brand w-100 text-white" type="submit">Save password</button></form>
@endsection
