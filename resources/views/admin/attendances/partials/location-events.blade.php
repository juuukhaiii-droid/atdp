<section class="rounded-brand-lg shadow-card bg-white overflow-hidden mt-6" aria-labelledby="gps-events-title">
    <div class="px-5 py-4 border-b border-slate-200"><h2 id="gps-events-title" class="font-semibold">GPS attendance locations</h2><p class="text-ink-soft text-sm">Location captured at each check-in and check-out. Manual corrections do not create GPS evidence.</p></div>
    <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr><th class="text-left">Scan</th><th class="text-left">Checkpoint</th><th class="text-left">Coordinates</th><th class="text-left">Distance</th><th class="text-left">GPS accuracy</th><th class="text-left">Map</th></tr></thead><tbody>
        @forelse($locationEvents as $event)
            <tr><td>{{ $event->type === 'in' ? 'Check in' : 'Check out' }}<div class="text-ink-soft">{{ $event->scanned_at->format('h:i:s A') }}</div></td><td>{{ $event->attendancePoint->name ?? '—' }}</td>
            @if($event->latitude !== null && $event->longitude !== null)
                <td>{{ number_format($event->latitude, 7) }}, {{ number_format($event->longitude, 7) }}<div class="text-ink-soft">{{ $event->location_captured_at?->format('d M Y H:i:s') }}</div></td><td>{{ number_format($event->distance_meters, 1) }} m</td><td>±{{ number_format($event->location_accuracy, 1) }} m</td><td><a class="inline-flex border border-slate-200 rounded-[10px] px-3 py-2" href="https://www.google.com/maps?q={{ $event->latitude }},{{ $event->longitude }}" target="_blank" rel="noopener noreferrer">View map</a></td>
            @else
                <td colspan="4" class="text-ink-soft">No GPS data recorded for this older event.</td>
            @endif</tr>
        @empty<tr><td colspan="6" class="text-ink-soft text-center">No scan events for this date.</td></tr>@endforelse
    </tbody></table></div>
</section>
