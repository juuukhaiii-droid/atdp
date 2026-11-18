@extends('layouts.employee-app')
@section('title', 'ស្កេន QR')
@section('content')
    <section class="attendance-scanner" data-attendance-scanner data-auto-start="true">
        <h1>ស្កេន QR</h1>
        <x-attendance-connection-help />
        <details class="scanner-notice" data-browser-notice hidden>
            <summary>បើកតាម Telegram ឬកម្មវិធីផ្សេង?</summary>
            <p>បើកាមេរ៉ាមិនបើក សូមប្រើម៉ឺនុយដើម្បីបើកក្នុង Safari ឬ Chrome។</p><button type="button"
                data-copy-link>ចម្លងតំណ</button>
        </details>
        <div class="scanner-status" role="status" aria-live="polite" data-scanner-status>កំពុងបើកកាមេរ៉ា…</div>
        <div class="scanner-camera" data-camera-panel hidden><video data-scanner-video autoplay playsinline muted
                aria-label="កាមេរ៉ាស្កេន QR"></video>
            <div class="scanner-target" aria-hidden="true"></div>
        </div>
        <div class="scanner-actions" data-scanner-actions><button type="button" data-camera-start disabled>បើកកាមេរ៉ា</button><button type="button" data-camera-stop hidden>បិទកាមេរ៉ា</button></div>
        <noscript>
            <p>សូមបើក JavaScript ដើម្បីស្កេន QR និងពិនិត្យទីតាំង។</p>
        </noscript>
    </section>
@endsection
@push('styles')
    <link rel="stylesheet"
        href="{{ asset('css/attendance-location.css') }}?v={{ filemtime(public_path('css/attendance-location.css')) }}">
    <link rel="stylesheet"
        href="{{ asset('css/attendance-scanner.css') }}?v={{ filemtime(public_path('css/attendance-scanner.css')) }}">
@endpush
@push('scripts') @vite('resources/js/attendance-scanner.js') @endpush
