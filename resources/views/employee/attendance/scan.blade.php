@extends('layouts.employee-app')

@section('title', 'Scan QR')

@section('content')

    <div class="section-title" style="margin-top:0;">Scan Attendance QR</div>

    <div class="app-card wifi-status-card mb-3" id="wifiStatusCard">
        <div class="card-body d-flex align-items-center gap-2">
            <i class="fas fa-spinner fa-spin" id="wifiStatusIcon"></i>
            <span id="wifiStatusText">Checking office WiFi&hellip;</span>
        </div>
    </div>

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
                សូមដាក់ឲ្យចំ QR Code 
                សូមប្រាកដថាលោកអ្នកបានប្រើប្រាស់ WIFI ក្រុមហ៊ុន។
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

    .wifi-status-card .card-body {
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
    }

    .wifi-status-card.wifi-connected { background: #dcfce7; color: #166534; }
    .wifi-status-card.wifi-connected i { color: #16a34a; }

    .wifi-status-card.wifi-disconnected { background: #fee2e2; color: #991b1b; }
    .wifi-status-card.wifi-disconnected i { color: #dc2626; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
    const video = document.getElementById('video');
    const canvas = document.createElement('canvas');
    const canvasCtx = canvas.getContext('2d', { willReadFrequently: true });

    let activeStream = null;
    let scanning = false;

    // Only follow QR codes that point at this app's own attendance route -
    // a malicious sticker placed over/near a real QR code could otherwise
    // redirect a scanning employee anywhere.
    function isSafeAttendanceUrl(value) {
        try {
            const url = new URL(value, window.location.origin);
            return url.origin === window.location.origin && url.pathname.startsWith('/attendance/');
        } catch (e) {
            return false;
        }
    }

    function tick() {
        if (!scanning) return;

        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvasCtx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = canvasCtx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'dontInvert',
            });

            if (code && code.data && isSafeAttendanceUrl(code.data)) {
                scanning = false;
                activeStream?.getTracks().forEach(track => track.stop());
                window.location.href = code.data;
                return;
            }
        }

        requestAnimationFrame(tick);
    }

    async function startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert("This browser does not support camera access.");
            return;
        }

        try {
            activeStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: "environment" } },
                audio: false
            });

            video.srcObject = activeStream;
            scanning = true;
            requestAnimationFrame(tick);
        } catch (e) {
            console.error(e);
            alert("Camera Error\n\n" + e.name + "\n" + e.message);
        }
    }

    startCamera();

    (function () {
        const card = document.getElementById('wifiStatusCard');
        const icon = document.getElementById('wifiStatusIcon');
        const text = document.getElementById('wifiStatusText');

        async function checkOfficeWifi() {
            try {
                const res = await fetch("{{ route('api.verify-network') }}", {
                    cache: 'no-cache',
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();

                card.classList.remove('wifi-connected', 'wifi-disconnected');

                if (data.verified) {
                    card.classList.add('wifi-connected');
                    icon.className = 'fas fa-wifi';
                    text.textContent = 'Connected to Office WiFi';
                } else {
                    card.classList.add('wifi-disconnected');
                    icon.className = 'fas fa-triangle-exclamation';
                    text.textContent = 'Not on Office WiFi — check-in will be rejected';
                }
            } catch (e) {
                card.classList.remove('wifi-connected');
                card.classList.add('wifi-disconnected');
                icon.className = 'fas fa-triangle-exclamation';
                text.textContent = 'Unable to check WiFi status';
            }
        }

        checkOfficeWifi();
        setInterval(checkOfficeWifi, 10000);
    })();
</script>
@endpush
