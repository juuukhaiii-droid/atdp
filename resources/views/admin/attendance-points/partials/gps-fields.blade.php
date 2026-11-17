<fieldset class="mb-5 p-4 bg-slate-50 rounded-[10px] border border-slate-200" data-checkpoint-gps>
    <legend class="font-semibold text-sm px-1">GPS location verification</legend>
    <p class="text-ink-soft text-sm mb-4">Employees must be within this distance when checking in or out. Set the shop location, not your home location.</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div><label for="checkpoint-latitude" class="block font-semibold text-sm mb-1.5">Latitude *</label><input id="checkpoint-latitude" name="latitude" type="number" step="0.0000001" min="-90" max="90" required value="{{ old('latitude', $attendancePoint->latitude ?? '') }}" class="w-full border border-slate-200 rounded-[10px]" placeholder="11.594184">@error('latitude')<small class="text-brand-danger">{{ $message }}</small>@enderror</div>
        <div><label for="checkpoint-longitude" class="block font-semibold text-sm mb-1.5">Longitude *</label><input id="checkpoint-longitude" name="longitude" type="number" step="0.0000001" min="-180" max="180" required value="{{ old('longitude', $attendancePoint->longitude ?? '') }}" class="w-full border border-slate-200 rounded-[10px]" placeholder="104.810963">@error('longitude')<small class="text-brand-danger">{{ $message }}</small>@enderror</div>
        <div><label for="checkpoint-radius" class="block font-semibold text-sm mb-1.5">Allowed distance (m) *</label><input id="checkpoint-radius" name="radius_meters" type="number" step="1" min="25" max="1000" required value="{{ old('radius_meters', $attendancePoint->radius_meters ?? 100) }}" class="w-full border border-slate-200 rounded-[10px]">@error('radius_meters')<small class="text-brand-danger">{{ $message }}</small>@enderror</div>
    </div>
    <button type="button" class="inline-flex mt-4 border border-slate-200 rounded-[10px] px-4 py-2" data-use-checkpoint-location>Use my current location</button>
    <div data-checkpoint-gps-review hidden class="mt-3 p-3 border border-slate-200 rounded-[10px]">
        <p class="text-sm mb-2" data-checkpoint-gps-estimate></p>
        <div class="flex flex-wrap gap-3 items-center">
            <a data-checkpoint-gps-map target="_blank" rel="noopener noreferrer" class="text-link">Check this location on map</a>
            <button type="button" data-use-approximate-location class="inline-flex border border-slate-200 rounded-[10px] px-3 py-2">Use this approximate location</button>
        </div>
        <p class="text-sm mt-2">An approximate reading can place the checkpoint at the wrong address. Review the map before using it, or enter the shop coordinates manually.</p>
    </div>
    <p data-checkpoint-gps-status class="text-ink-soft text-sm mt-2" role="status" aria-live="polite">Use this button only while standing at the shop, or enter coordinates from your map.</p>
</fieldset>
@once
@push('scripts')
<script src="{{ asset('js/checkpoint-location.js') }}?v={{ filemtime(public_path('js/checkpoint-location.js')) }}" defer></script>
@endpush
@endonce
