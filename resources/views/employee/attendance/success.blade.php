@extends('layouts.employee-app')
@section('title', 'Attendance saved')
@section('content')
<section class="attendance-success">
    <ol class="attendance-steps" aria-label="Attendance progress"><li class="complete"><span>1</span>QR scanned</li><li class="complete"><span>2</span>GPS checked</li><li class="complete" aria-current="step"><span>3</span>Done</li></ol>
    <div class="attendance-success-mark" aria-hidden="true"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 4 4L19 6"/></svg></div>
    <h1>{{ $result['type'] === 'in' ? "You're checked in!" : "You're checked out!" }}</h1>
    <p>{{ $result['type'] === 'in' ? 'Have a great shift.' : 'Thank you for your work today.' }}</p>
    <div class="attendance-place"><strong class="attendance-place-name">{{ $result['name'] }}</strong>@if(!empty($result['checkpoint']))<span>{{ $result['checkpoint'] }}</span>@endif<time>{{ $result['time'] }}</time><span class="attendance-saved-badge">Attendance saved</span></div>
    <a href="{{ route('employee.dashboard') }}" class="attendance-home-button">Done - Go home</a>
    <a href="{{ route('employee.attendance.history') }}" class="attendance-history-link">View attendance history</a>
</section>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('css/attendance-location.css') }}?v={{ filemtime(public_path('css/attendance-location.css')) }}">@endpush
