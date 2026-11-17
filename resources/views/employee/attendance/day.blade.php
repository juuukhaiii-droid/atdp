<div class="activity-row" style="border-bottom:1px solid var(--border-soft);padding:16px;">
    <div style="flex:1;min-width:0;">
        <div class="activity-title">{{ \Carbon\Carbon::parse($record->attendance_date)->format('d/m/Y') }} · {{ ['present' => 'មានវត្តមាន', 'late' => 'មកយឺត', 'absent' => 'អវត្តមាន'][$record->status] ?? $record->status }}</div>
        <div class="activity-sub">{{ $record->attendancePoint?->name ?? 'មិនបានកំណត់ទីតាំង' }}</div>
        <div style="display:flex;gap:24px;flex-wrap:wrap;margin-top:8px;font-size:14px;">
            <span>ចូល៖ <strong>{{ $record->check_in_time ? substr($record->check_in_time, 0, 5) : '--:--' }}</strong></span>
            <span>ចេញ៖ <strong>{{ $record->check_out_time ? substr($record->check_out_time, 0, 5) : '--:--' }}</strong></span>
        </div>
        @if($record->is_override)<div class="activity-sub" style="margin-top:6px;">កែតម្រូវដោយអ្នកគ្រប់គ្រង</div>@endif
        @if($record->note)<div class="activity-sub">{{ $record->note }}</div>@endif
    </div>
</div>
