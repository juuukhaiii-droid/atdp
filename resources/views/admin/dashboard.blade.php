@extends('layouts.admin')
@section('title', 'Dashboard · PizzaHappyFamily')

@section('content')
@php
    $selectedCarbon = \Illuminate\Support\Carbon::parse($selectedDate);
    $isToday = $selectedDate === now()->toDateString();
    $rate = $totalEmployees ? min(100, round($presentToday / $totalEmployees * 100)) : 0;
    $onTime = max(0, $presentToday - $lateToday);
@endphp
<div class="dash-shell" x-data="{ search: '', status: 'all' }">
    <header class="dash-heading">
        <div>
            <h1 class="font-khmer">ផ្ទាំងគ្រប់គ្រងវត្តមាន<span class="heading-dot">.</span></h1>
            <p>— តាមដានវត្តមានក្រុមការងាររបស់អ្នក។</p>
        </div>
        <a class="dash-button primary" href="{{ route('admin.employees.create') }}"><i class="fas fa-plus" aria-hidden="true"></i> បន្ថែមបុគ្គលិក</a>
    </header>

    <section class="dash-datebar" aria-label="Attendance date">
        <div class="datebar-label"><span class="dash-icon"><i class="far fa-calendar" aria-hidden="true"></i></span><div><strong>{{ $isToday ? 'ទិដ្ឋភាពថ្ងៃនេះ' : 'ទិដ្ឋភាពតាមកាលបរិច្ឆេទ' }}</strong><small>{{ $selectedCarbon->format('l, d F Y') }}</small></div></div>
        <form method="GET" action="{{ route('admin.dashboard') }}" class="date-controls">
            <a class="date-arrow" href="{{ route('admin.dashboard', ['date' => $selectedCarbon->copy()->subDay()->toDateString()]) }}" aria-label="Previous day"><i class="fas fa-chevron-left" aria-hidden="true"></i></a>
            <label for="dashboard-date" class="sr-only">Viewing date</label>
            <input id="dashboard-date" type="date" name="date" value="{{ $selectedDate }}" max="{{ now()->toDateString() }}" required>
            @if($selectedDate < now()->toDateString())
                <a class="date-arrow" href="{{ route('admin.dashboard', ['date' => $selectedCarbon->copy()->addDay()->toDateString()]) }}" aria-label="Next day"><i class="fas fa-chevron-right" aria-hidden="true"></i></a>
            @else
                <button class="date-arrow" type="button" disabled aria-label="Next day"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
            @endif
            <button class="dash-button dark" type="submit">មើលទិន្នន័យ <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
            @unless($isToday)<a class="today-link" href="{{ route('admin.dashboard') }}">ថ្ងៃនេះ</a>@endunless
        </form>
    </section>

    <section class="dash-stats" aria-label="Attendance statistics">
        @foreach([
            ['label' => 'បុគ្គលិកសរុប', 'value' => $totalEmployees, 'caption' => 'Active employees', 'icon' => 'fa-users', 'color' => 'neutral', 'tag' => 'TEAM'],
            ['label' => 'មានវត្តមាន', 'value' => $presentToday, 'caption' => 'Checked in on selected date', 'icon' => 'fa-user-check', 'color' => 'green', 'tag' => 'PRESENT'],
            ['label' => 'មកធ្វើការយឺត', 'value' => $lateToday, 'caption' => 'Included in present employees', 'icon' => 'fa-clock', 'color' => 'amber', 'tag' => 'LATE'],
            ['label' => 'អវត្តមាន', 'value' => $absentToday, 'caption' => 'No attendance recorded', 'icon' => 'fa-user-xmark', 'color' => 'red', 'tag' => 'ABSENT'],
        ] as $stat)
            <article class="stat-card {{ $stat['color'] }}">
                <div class="stat-top"><span class="stat-icon"><i class="fas {{ $stat['icon'] }}" aria-hidden="true"></i></span><span class="stat-tag">{{ $stat['tag'] }}</span></div>
                <div class="stat-value">{{ str_pad($stat['value'], 2, '0', STR_PAD_LEFT) }}<span>{{ $stat['label'] }}</span></div>
                <p>{{ $stat['caption'] }}</p>
            </article>
        @endforeach
    </section>

    <div class="dash-workspace">
        <section class="dash-panel attendance-panel" aria-labelledby="attendance-title">
            <div class="panel-heading"><div><h2 id="attendance-title">បញ្ជីវត្តមានបុគ្គលិក <span class="count-pill">{{ $todayRecords->count() }}</span></h2><p>Attendance records · {{ $selectedCarbon->format('d M Y') }}</p></div><a href="{{ route('admin.attendance.history', ['date' => $selectedDate]) }}" class="text-link">របាយការណ៍ <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a></div>
            <div class="table-toolbar">
                <div class="status-tabs" role="group" aria-label="Filter attendance status">
                    @foreach(['all' => 'ទាំងអស់', 'present' => 'ទាន់ម៉ោង', 'late' => 'មកយឺត'] as $value => $label)
                        <button type="button" @click="status = '{{ $value }}'" :class="{ 'active': status === '{{ $value }}' }" :aria-pressed="status === '{{ $value }}'">{{ $label }}</button>
                    @endforeach
                </div>
                <label class="table-search"><i class="fas fa-search" aria-hidden="true"></i><span class="sr-only">Search employees</span><input type="search" x-model="search" placeholder="ស្វែងរកបុគ្គលិក…"></label>
            </div>
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead><tr><th>បុគ្គលិក</th><th>ផ្នែក</th><th>ម៉ោងចូល</th><th>ម៉ោងចេញ</th><th>ស្ថានភាព</th><th><span class="sr-only">View details</span></th></tr></thead>
                    <tbody>
                        @foreach($todayRecords as $record)
                            @php
                                $searchText = mb_strtolower(($record->employee->full_name ?? '') . ' ' . ($record->employee->employee_code ?? '') . ' ' . ($record->employee->department->name ?? ''));
                            @endphp
                            <tr data-search="{{ $searchText }}" data-status="{{ $record->status }}" x-show="(status === 'all' || status === $el.dataset.status) && $el.dataset.search.includes(search.trim().toLocaleLowerCase())">
                                <td><a href="{{ route('admin.employees.show', $record->employee_id) }}" class="employee-cell"><span class="employee-avatar">{{ mb_substr($record->employee->full_name ?? '?', 0, 1) }}</span><span><strong>{{ $record->employee->full_name ?? '—' }}</strong><small>{{ $record->employee->employee_code ?? '—' }}</small></span></a></td>
                                <td>{{ $record->employee->department->name ?? '—' }}</td>
                                <td class="time-cell">{{ $record->check_in_time ? \Illuminate\Support\Carbon::parse($record->check_in_time)->format('h:i A') : '—' }}</td>
                                <td class="time-cell muted">{{ $record->check_out_time ? \Illuminate\Support\Carbon::parse($record->check_out_time)->format('h:i A') : '—' }}</td>
                                <td><span class="attendance-badge {{ $record->status }}"><span></span>{{ $record->status === 'present' ? 'ទាន់ម៉ោង' : ($record->status === 'late' ? 'មកយឺត' : 'អវត្តមាន') }}</span>@if($record->late_minutes > 0)<small class="late-duration">+{{ $record->late_minutes }} min</small>@endif</td>
                                <td><a class="row-link" href="{{ route('admin.attendances.show', ['employee' => $record->employee_id, 'date' => $record->attendance_date]) }}" aria-label="View attendance for {{ $record->employee->full_name }}"><i class="fas fa-chevron-right" aria-hidden="true"></i></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($todayRecords->isEmpty())
                <div class="dash-empty"><span><i class="far fa-calendar-check" aria-hidden="true"></i></span><h3>មិនទាន់មានទិន្នន័យវត្តមាន</h3><p>មិនមានការចុះវត្តមានសម្រាប់ថ្ងៃទី {{ $selectedCarbon->format('d M Y') }}។<br>ជ្រើសរើសថ្ងៃផ្សេង ដើម្បីមើលប្រវត្តិវត្តមាន។</p><a href="{{ route('admin.dashboard', ['date' => $selectedCarbon->copy()->subDay()->toDateString()]) }}" class="dash-button light">មើលថ្ងៃមុន <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
            @else
                <div class="dash-empty search-empty" x-cloak x-show="!Array.from($root.querySelectorAll('tbody tr')).some(row => (status === 'all' || status === row.dataset.status) && row.dataset.search.includes(search.trim().toLocaleLowerCase()))"><i class="fas fa-search" aria-hidden="true"></i><h3>រកមិនឃើញបុគ្គលិក</h3><p>សូមសាកល្បងឈ្មោះ លេខសម្គាល់ ឬស្ថានភាពផ្សេង។</p><button type="button" class="text-link" @click="search = ''; status = 'all'">សម្អាតការស្វែងរក</button></div>
            @endif
            <div class="table-footer"><span><span class="tiny-dot"></span> ទិន្នន័យថ្ងៃទី {{ $selectedCarbon->format('d M Y') }}</span><a href="{{ route('admin.attendances.index', ['date_from' => $selectedDate, 'date_to' => $selectedDate]) }}">មើលវត្តមានទាំងអស់ <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
        </section>

        <aside class="dash-aside">
            <section class="dash-panel overview-panel" aria-labelledby="overview-title">
                <div class="panel-heading"><div><h2 id="overview-title">សង្ខេបវត្តមាន</h2><p>Attendance overview</p></div><i class="fas fa-chart-pie muted" aria-hidden="true"></i></div>
                <div class="attendance-ring" style="--rate: {{ $rate }}" role="img" aria-label="Attendance rate {{ $rate }} percent"><div><strong>{{ $rate }}<small>%</small></strong><span>អត្រាវត្តមាន</span></div></div>
                <p class="ring-caption"><strong>{{ $presentToday }}</strong> នាក់ ក្នុងចំណោម {{ $totalEmployees }} នាក់</p>
                <div class="overview-legend"><div><span><i class="legend-dot green"></i> ទាន់ម៉ោង</span><strong>{{ $onTime }}</strong></div><div><span><i class="legend-dot amber"></i> មកយឺត</span><strong>{{ $lateToday }}</strong></div><div><span><i class="legend-dot red"></i> អវត្តមាន</span><strong>{{ $absentToday }}</strong></div></div>
            </section>
            <section class="dash-panel quick-panel"><h2>ចូលប្រើរហ័ស</h2><a href="{{ route('admin.attendance-points.index') }}"><span class="quick-icon"><i class="fas fa-qrcode" aria-hidden="true"></i></span><span><strong>គ្រប់គ្រង QR Code</strong><small>Attendance checkpoints</small></span><i class="fas fa-arrow-right" aria-hidden="true"></i></a><a href="{{ route('admin.shifts.index') }}"><span class="quick-icon"><i class="far fa-clock" aria-hidden="true"></i></span><span><strong>ម៉ោងធ្វើការ</strong><small>Manage work shifts</small></span><i class="fas fa-arrow-right" aria-hidden="true"></i></a></section>
        </aside>
    </div>
</div>
@endsection
