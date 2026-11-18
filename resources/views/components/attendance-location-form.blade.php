@props(['action', 'type', 'point' => null, 'points' => collect(), 'auto' => false, 'scanId' => null])
@php
    $selectedPoint = $point ?? ($points->count() === 1 ? $points->first() : null);
    $configured = $point ? ($point->latitude !== null && $point->longitude !== null) : $points->isNotEmpty();
    $label = $type === 'in' ? 'Check In' : 'Check Out';
    // Leave time to write a summary before checkout captures GPS and submits.
    $autoRun = $auto && $type === 'in' && $configured && !$errors->any();
@endphp
<form method="POST" action="{{ $action }}" class="gps-attendance-form" data-location-attendance data-auto-start="{{ $autoRun ? 'true' : 'false' }}" data-scan-id="{{ $scanId }}" data-location-configured="{{ $configured ? 'true' : 'false' }}" data-location-accuracy-limit="{{ min(100, $selectedPoint?->radius_meters ?? 100) }}">
    @csrf
    <input type="hidden" name="attendance_type" value="{{ $type }}">
    @if($scanId)<input type="hidden" name="scan_id" value="{{ $scanId }}">@endif
    @foreach(['latitude', 'longitude', 'location_accuracy', 'location_captured_at'] as $field)
        <input type="hidden" name="{{ $field }}" value="">
    @endforeach
    <ol class="attendance-steps" aria-label="Attendance progress">
        <li class="complete"><span aria-hidden="true">1</span>{{ $point ? 'QR scanned' : 'Checkpoint' }}</li>
        <li class="current" aria-current="step"><span aria-hidden="true">2</span>{{ $auto ? 'GPS check' : 'Confirm' }}</li>
        <li><span aria-hidden="true">3</span>Done</li>
    </ol>
    <div class="attendance-action-heading"><span class="attendance-action-icon {{ $type === 'out' ? 'is-out' : '' }}" aria-hidden="true">{{ $type === 'in' ? '→' : '←' }}</span><h1>{{ $label }}</h1><p data-attendance-description>{{ $autoRun ? 'Checking your location automatically...' : ($type === 'in' ? 'Ready to start your shift?' : 'Finished for today?') }}</p></div>
    <div class="attendance-place">
        <span class="attendance-place-label">{{ $selectedPoint ? 'Your checkpoint' : 'Choose your checkpoint' }}</span>
        @if($selectedPoint)
            <strong class="attendance-place-name">{{ $selectedPoint->name }}</strong>
            <input type="hidden" name="attendance_point_id" value="{{ $selectedPoint->id }}">
        @else
            <label for="attendance-point" class="sr-only">Checkpoint</label>
            <select id="attendance-point" name="attendance_point_id" required>
                <option value="">Select a checkpoint</option>
                @foreach($points as $availablePoint)
                    <option value="{{ $availablePoint->id }}" data-radius="{{ $availablePoint->radius_meters }}" @selected(old('attendance_point_id') == $availablePoint->id)>{{ $availablePoint->name }}</option>
                @endforeach
            </select>
        @endif
        <time datetime="{{ now()->toIso8601String() }}">{{ now()->format('D, d M Y') }} at {{ now()->format('h:i A') }}</time>
        @if($selectedPoint && $configured)<small>Be within {{ $selectedPoint->radius_meters }} m of this checkpoint.</small>@endif
    </div>
    @if($type === 'out')
        <div class="attendance-summary">
            <label for="work-summary" style="display:block;font-size:14px;font-weight:600;">ថ្ងៃនេះអ្នកបានធ្វើអ្វីខ្លះ? <span style="font-size:12px;font-weight:400;color:#66758a;">(មិនបង្ខំ)</span></label>
            <textarea id="work-summary" name="work_summary" rows="3" maxlength="2000" placeholder="សរសេរសង្ខេបការងារថ្ងៃនេះ…">{{ old('work_summary') }}</textarea>
        </div>
    @endif
    <div data-location-status role="status" aria-live="polite" class="attendance-status" data-state="{{ $autoRun ? 'busy' : ($configured ? 'ready' : 'error') }}">{{ $autoRun ? 'Please allow location access when asked.' : ($configured ? 'Tap ' . $label . ', then allow location when asked.' : 'GPS is not configured for this checkpoint. Please contact your administrator.') }}</div>
    <x-attendance-connection-help />
    <button type="submit" disabled @if($autoRun) hidden @endif data-location-submit>{{ $label }}</button>
    <p class="attendance-privacy" data-location-privacy>Location is saved only for this check-in or check-out.</p>
    <noscript><p>Enable JavaScript to share your attendance location.</p></noscript>
</form>
@once
    @push('styles')<link rel="stylesheet" href="{{ asset('css/attendance-location.css') }}?v={{ filemtime(public_path('css/attendance-location.css')) }}">@endpush
    @push('scripts')<script src="{{ asset('js/attendance-location.js') }}?v={{ filemtime(public_path('js/attendance-location.js')) }}" defer></script>@endpush
@endonce
