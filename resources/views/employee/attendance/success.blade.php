@extends('layouts.employee-app')
@section('title', 'Attendance saved')
@section('content')
@php
    $checkedIn = $result['type'] === 'in';
    $savedAt = \Illuminate\Support\Carbon::parse($result['time']);
@endphp
<section class="attendance-success attendance-receipt" aria-labelledby="attendance-success-title">
    <div class="receipt-check" aria-hidden="true"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg></div>
    <h1 id="attendance-success-title">{{ $checkedIn ? 'ចូលធ្វើការជោគជ័យ' : 'ចេញពីធ្វើការជោគជ័យ' }}</h1>
    <p class="receipt-confirmation" role="status">វត្តមានរបស់អ្នកបានកត់ត្រារួចរាល់</p>
    <div class="receipt-card">
        <span class="receipt-type {{ $checkedIn ? '' : 'is-out' }}">{{ $checkedIn ? 'CHECK IN' : 'CHECK OUT' }}</span>
        <time class="receipt-time" datetime="{{ $savedAt->toIso8601String() }}">{{ $savedAt->format('H:i') }}<span>:{{ $savedAt->format('s') }}</span></time>
        <p class="receipt-date">{{ $savedAt->format('d / m / Y') }}</p>
        <dl class="receipt-details">
            <div><dt>បុគ្គលិក</dt><dd>{{ $result['name'] }}</dd></div>
            @if(!empty($result['checkpoint']))<div><dt>ទីតាំង</dt><dd>{{ $result['checkpoint'] }}</dd></div>@endif
        </dl>
        <div class="receipt-saved"><span aria-hidden="true">✓</span> បានរក្សាទុក</div>
    </div>
    <a href="{{ route('employee.dashboard') }}" class="attendance-home-button">រួចរាល់</a>
    <a href="{{ route('employee.attendance.history') }}" class="attendance-history-link">មើលប្រវត្តិវត្តមាន <span aria-hidden="true">↗</span></a>
</section>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-location.css') }}?v={{ filemtime(public_path('css/attendance-location.css')) }}">
<link rel="stylesheet" href="{{ asset('css/attendance-success.css') }}?v={{ filemtime(public_path('css/attendance-success.css')) }}">
@endpush
