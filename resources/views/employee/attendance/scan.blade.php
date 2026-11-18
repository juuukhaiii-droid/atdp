@extends('layouts.employee-app')
@section('title', 'Scan QR')
@section('content')
    <section class="attendance-scanner" data-attendance-scanner data-auto-start="true">
        <h1>ស្កេន QR</h1>
        <x-attendance-connection-help />
        <details class="scanner-notice" data-browser-notice hidden>
            <summary>Using Telegram or another app?</summary>
            <p>If the camera does not open, use the app menu to open this page in Safari or Chrome.</p><button type="button"
                data-copy-link>Copy link</button>
        </details>
        <div class="scanner-status" role="status" aria-live="polite" data-scanner-status>កំពុងបើកកាមេរ៉ា…</div>
        <div class="scanner-camera" data-camera-panel hidden><video data-scanner-video autoplay playsinline muted
                aria-label="QR camera preview"></video>
            <div class="scanner-target" aria-hidden="true"></div>
        </div>
        <div class="scanner-actions" data-scanner-actions><button type="button" data-camera-start disabled>Open
                camera</button><button type="button" data-camera-stop hidden>Cancel camera</button></div>
        <noscript>
            <p>Enable JavaScript to scan a QR or capture your attendance location.</p>
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
