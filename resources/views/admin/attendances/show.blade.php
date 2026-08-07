@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-4">
    <div class="mb-6">
        <a href="{{ route('admin.attendances.index') }}" class="inline-flex items-center gap-2 rounded-[10px] border border-slate-200 bg-white text-ink font-semibold px-4 py-2.5 hover:bg-slate-50 transition mb-3">
            <i class="fas fa-arrow-left"></i> Back to Records
        </a>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight flex items-center gap-2">
            <i class="fas fa-eye"></i> Attendance Record Details
        </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <div class="md:col-span-8">
            <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
                <div class="px-5 sm:px-6 py-4 bg-brand-primary text-white font-bold">
                    Record Information
                </div>
                <div class="p-5 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-ink-soft text-xs mb-1">Employee Name</label>
                            <p class="text-lg font-bold">{{ $attendance->employee->full_name }}</p>
                        </div>
                        <div>
                            <label class="block text-ink-soft text-xs mb-1">Employee Code</label>
                            <p class="text-lg font-bold"><span class="inline-block px-2.5 py-1.5 rounded-[10px] bg-slate-100 text-ink text-[13px]">{{ $attendance->employee->employee_code }}</span></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-ink-soft text-xs mb-1">Department</label>
                            <p>{{ $attendance->employee->department->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-ink-soft text-xs mb-1">Shift</label>
                            <p>{{ $attendance->employee->shift->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr class="border-slate-200 my-4">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-ink-soft text-xs mb-1">Attendance Date</label>
                            <p class="text-lg font-bold">{{ $attendance->created_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-ink-soft text-xs mb-1">Status</label>
                            <p>
                                @if ($attendance->status === 'present')
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-green-100 text-green-800">Present</span>
                                @elseif ($attendance->status === 'late')
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-amber-100 text-amber-800">Late</span>
                                @else
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-red-100 text-red-800">Absent</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-ink-soft text-xs mb-1">Check In Time</label>
                            @if ($attendance->check_in_time)
                                <p class="text-lg font-bold text-green-600">
                                    {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s') }}
                                </p>
                            @else
                                <p class="text-ink-soft">Not checked in</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-ink-soft text-xs mb-1">Check Out Time</label>
                            @if ($attendance->check_out_time)
                                <p class="text-lg font-bold text-amber-500">
                                    {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i:s') }}
                                </p>
                            @else
                                <p class="text-ink-soft">Not checked out</p>
                            @endif
                        </div>
                    </div>

                    @if ($attendance->late_minutes > 0)
                        <div class="rounded-brand-lg bg-amber-100 text-amber-800 px-5 py-4">
                            <i class="fas fa-clock mr-2"></i>
                            <strong>Late by {{ $attendance->late_minutes }} minutes</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
