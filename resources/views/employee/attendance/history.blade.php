@extends('layouts.employee-app')
@section('title', 'ប្រវត្តិវត្តមាន')
@section('content')
<div class="history-heading"><div><span class="section-title">ប្រវត្តិវត្តមានរបស់ខ្ញុំ</span><h1>ប្រវត្តិវត្តមាន</h1></div><span class="history-count">{{ $records->total() }} ថ្ងៃ</span></div>
<form class="history-filters" method="GET" action="{{ route('employee.attendance.history') }}">
    <div><label for="history-month">ខែ</label><input type="month" id="history-month" name="month" value="{{ $month }}" required></div>
    <div><label for="history-status">ស្ថានភាព</label><select id="history-status" name="status"><option value="">ទាំងអស់</option value="present" @selected($status === 'present')>មានវត្តមាន</option><option value="late" @selected($status === 'late')>មកយឺត</option><option value="absent" @selected($status === 'absent')>អវត្តមាន</option><option value="day_off" @selected($status === 'day_off')>{{ __('employee.day_off') }}</option></select></div>
    <button type="submit">មើល</button>
</form>
<div class="history-stats">
    <div><span>ថ្ងៃមានវត្តមាន</span><strong>{{ $stats['present'] }} <small>ថ្ងៃ</small></strong></div>
    <div><span>ថ្ងៃមកយឺត</span><strong class="history-amber">{{ $stats['late'] }} <small>ថ្ងៃ</small></strong></div>
    <div><span>រយៈពេលចូល–ចេញ</span><strong>{{ intdiv($stats['span_minutes'], 60) }}<small>ម៉</small> {{ $stats['span_minutes'] % 60 }}<small>ន</small></strong></div>
    <div><span>យឺតសរុប</span><strong class="history-amber">{{ $stats['late_minutes'] }} <small>នាទី</small></strong></div>
</div>
<div class="history-list-title"><span>ខែ {{ \Carbon\Carbon::parse($month . '-01')->format('m / Y') }}</span><small>រយៈពេលមិនទាន់ដកម៉ោងសម្រាក</small></div>
@forelse ($records as $record)
    @php
        $date = \Carbon\Carbon::parse($record->attendance_date);
        $weekdays = ['អាទិត្យ', 'ចន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍'];
    @endphp
    <article class="history-day">
        <div class="history-day-header"><div class="history-day-date"><div class="history-date-tile"><strong>{{ $date->format('d') }}</strong><span>{{ $date->format('m/Y') }}</span></div><div><h2>ថ្ងៃ{{ $weekdays[$date->dayOfWeek] }}</h2><span>{{ $date->format('d/m/Y') }}</span></div></div><span class="history-badge is-{{ $record->status }}">{{ ['present' => 'មានវត្តមាន', 'late' => 'មកយឺត', 'day_off' => __('employee.day_off'), 'absent' => 'អវត្តមាន'][$record->status] ?? $record->status }}</span></div>
        <div class="history-time-grid"><div><span>ចូលធ្វើការ</span><strong>{{ $record->check_in_time ? substr($record->check_in_time, 0, 5) : '--:--' }}</strong></div><div><span>ចេញពីធ្វើការ</span><strong>{{ $record->check_out_time ? substr($record->check_out_time, 0, 5) : '--:--' }}</strong></div><div><span>រយៈពេល</span><strong>{{ $record->span_minutes !== null ? intdiv($record->span_minutes, 60) . 'ម៉ ' . $record->span_minutes % 60 . 'ន' : '—' }}</strong></div></div>
        <div class="history-day-bottom"><span>{{ $record->attendancePoint?->name ?? 'មិនបានកំណត់ទីតាំង' }}</span>@if($record->late_minutes > 0)<span class="history-amber">យឺត {{ $record->late_minutes }} នាទី</span>@endif</div>
        @if($record->is_override)<div class="history-source">កែតម្រូវដោយអ្នកគ្រប់គ្រង</div>@endif
        @if($record->note)<p class="history-note">{{ $record->note }}</p>@endif
        @if($record->check_in_time && !$record->check_out_time)<p class="history-note">មិនទាន់មានម៉ោងចេញ</p>@endif
    </article>
@empty
    <div class="history-empty"><strong>មិនមានទិន្នន័យសម្រាប់ជម្រើសនេះ</strong><p>មិនទាន់មានកំណត់ត្រាវត្តមានទេ។</p><a href="{{ route('attendance.show.qr') }}">ស្កេន QR</a></div>
@endforelse
@if ($records->hasPages())<div class="history-pagination">{{ $records->links() }}</div>@endif
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance-history.css') }}?v={{ filemtime(public_path('css/attendance-history.css')) }}">
@endpush
