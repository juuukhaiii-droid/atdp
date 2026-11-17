@extends('layouts.auth')
@section('title', 'Confirm password')
@section('content')
<div class="auth-card-header"><h1>Confirm your password</h1></div>
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.confirm') }}">@csrf<label class="form-label" for="password">Password</label><input id="password" type="password" name="password" class="form-control mb-3" required autocomplete="current-password"><button class="btn btn-brand w-100 text-white" type="submit">Confirm</button></form>
@endsection
