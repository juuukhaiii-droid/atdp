@extends('layouts.employee-app')

@section('title', 'Scan QR')

@section('content')

    <div class="section-title" style="margin-top:0;">Scan Attendance QR</div>

    <div class="app-card scan-card mb-3">
        <div class="card-body p-0">
            <div class="scan-viewport">
                <video id="video" autoplay playsinline muted></video>
                <div class="scan-frame"></div>
            </div>
        </div>
    </div>

    <div class="app-card">
        <div class="card-body">
            <div class="scan-hint">
                <i class="fas fa-circle-info me-2"></i>
                Point your camera at the attendance QR code at your location. Make sure you're connected to office WiFi before scanning.
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .scan-card { overflow: hidden; background: #000; }

    .scan-viewport {
        position: relative;
        width: 100%;
        aspect-ratio: 3 / 4;
        background: #000;
        border-radius: var(--radius-lg);
        overflow: hidden;
    }

    .scan-viewport video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .scan-frame {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 62%;
        aspect-ratio: 1 / 1;
        transform: translate(-50%, -50%);
        border: 3px solid rgba(255, 255, 255, 0.85);
        border-radius: 20px;
        box-shadow: 0 0 0 999px rgba(0, 0, 0, 0.35);
        pointer-events: none;
    }

    .scan-hint {
        font-size: 13px;
        color: var(--text-soft);
        line-height: 1.5;
    }

    .scan-hint i { color: var(--brand-primary); }
</style>
@endpush

@push('scripts')
<script>
    const video = document.getElementById('video');

    async function startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert("This browser does not support camera access.");
            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: "environment" } },
                audio: false
            });

            video.srcObject = stream;
        } catch (e) {
            console.error(e);
            alert("Camera Error\n\n" + e.name + "\n" + e.message);
        }
    }

    startCamera();
</script>
@endpush
