@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-1">Attendance Reports</h1>
        <p class="text-ink-soft">Filter attendance by employee, day, month, or year</p>
    </div>

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden mb-6">
        <div class="px-5 sm:px-6 py-4 border-b border-slate-200 font-bold">
            Report Filters
        </div>
        <div class="p-5 sm:p-6">
            <form method="GET" action="{{ route('admin.attendance.history') }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1.5">Employee</label>
                        <select name="employee_id" class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->employee_code }} - {{ $employee->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1.5">Day</label>
                        <input type="date" name="date" value="{{ request('date') }}" class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1.5">Month</label>
                        <input type="month" name="month" value="{{ request('month') }}" class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1.5">Year</label>
                        <input type="number" name="year" value="{{ request('year') }}" placeholder="2026" class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <button class="inline-flex items-center rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2.5 hover:opacity-90 transition">Filter</button>
                    <a href="{{ route('admin.attendance.history') }}" class="inline-flex items-center rounded-[10px] border border-slate-200 bg-white text-ink font-semibold px-4 py-2.5 hover:bg-slate-50 transition">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="rounded-brand-lg shadow-card bg-white p-5">
            <div class="text-ink-soft font-semibold text-sm mb-2">Total Records</div>
            <h3 class="text-2xl font-extrabold leading-none">{{ $totalRecords }}</h3>
        </div>

        <div class="rounded-brand-lg shadow-card bg-white p-5">
            <div class="text-ink-soft font-semibold text-sm mb-2">Present</div>
            <h3 class="text-2xl font-extrabold leading-none text-green-600">{{ $presentCount }}</h3>
        </div>

        <div class="rounded-brand-lg shadow-card bg-white p-5">
            <div class="text-ink-soft font-semibold text-sm mb-2">Late</div>
            <h3 class="text-2xl font-extrabold leading-none text-amber-500">{{ $lateCount }}</h3>
        </div>

        <div class="rounded-brand-lg shadow-card bg-white p-5">
            <div class="text-ink-soft font-semibold text-sm mb-2">Total Late Time</div>
            @php
                $lateHours = floor($totalLateMinutes / 60);
                $lateMinutes = $totalLateMinutes % 60;
            @endphp
            <h3 class="text-2xl font-extrabold leading-none text-orange-600">
                {{ str_pad($lateHours, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($lateMinutes, 2, '0', STR_PAD_LEFT) }}
            </h3>
        </div>
    </div>

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
        <div class="flex justify-between items-center px-5 sm:px-6 py-4 border-b border-slate-200 font-bold">
            <span>Attendance Result</span>
            <span class="text-ink-soft text-[13px] font-normal">{{ $records->count() }} result(s)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Date</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Employee</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Department</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Point</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Check In</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Check Out</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Status</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Late Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr class="hover:bg-slate-50/80 align-middle">
                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $record->attendance_date }}</td>

                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <div class="flex flex-col">
                                    <span class="font-semibold">{{ $record->employee->full_name ?? '-' }}</span>
                                    <span class="text-ink-soft text-[13px]">{{ $record->employee->employee_code ?? '-' }}</span>
                                </div>
                            </td>

                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $record->employee->department->name ?? '-' }}</td>
                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $record->attendancePoint->name ?? '-' }}</td>
                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $record->check_in_time ?? '-' }}</td>
                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $record->check_out_time ?? '-' }}</td>

                            <td class="px-3.5 py-4 border-b border-slate-100">
                                @if($record->status === 'late')
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-amber-100 text-amber-800">Late</span>
                                @elseif($record->status === 'present')
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-green-100 text-green-800">Present</span>
                                @else
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-red-100 text-red-800">{{ ucfirst($record->status) }}</span>
                                @endif
                            </td>

                            <td class="px-3.5 py-4 border-b border-slate-100">
                                @php
                                    $hours = floor($record->late_minutes / 60);
                                    $minutes = $record->late_minutes % 60;
                                @endphp

                                @if($record->late_minutes > 0)
                                    <span class="font-bold text-amber-700">
                                        {{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @else
                                    <span class="text-ink-soft">00:00</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-ink-soft">
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
