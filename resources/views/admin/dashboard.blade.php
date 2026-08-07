@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    @php
        $isToday = $selectedDate === now()->toDateString();
        $selectedCarbon = \Illuminate\Support\Carbon::parse($selectedDate);
    @endphp

    <div class="flex justify-between items-start flex-wrap gap-2 mb-6">
        <div>
            <h1 class="font-khmer text-2xl sm:text-3xl font-extrabold tracking-tight mb-2">ប្រព័ន្ធត្រួតពិនិត្យវត្តមាន</h1>
            <p class="font-khmer text-ink-soft">សង្ខេបព័ត៌មានវត្តមានបុគ្គលិកប្រចាំថ្ងៃ</p>
        </div>
        <div class="text-right text-sm sm:text-base">
            <div class="font-semibold">{{ now()->format('d M Y') }}</div>
            <div class="text-ink-soft">{{ now()->format('h:i A') }}</div>
        </div>
    </div>

    <div class="rounded-brand-lg shadow-card bg-gradient-to-br from-white to-slate-50 mb-6 p-5">
        <form method="GET" class="flex flex-wrap items-end gap-3.5">
            <div class="w-11 h-11 rounded-brand-md bg-blue-100 text-blue-800 flex items-center justify-center text-lg shrink-0">
                <i class="fas fa-calendar-week"></i>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-bold text-ink-soft uppercase tracking-wide">Viewing Date</label>
                <input type="date" name="date" value="{{ $selectedDate }}" max="{{ now()->toDateString() }}"
                    onchange="this.form.submit()"
                    class="border border-slate-200 rounded-[10px] px-3 py-2.5 font-semibold text-sm min-w-[170px] bg-white focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
            </div>

            <div class="flex gap-1.5">
                <a href="{{ route('admin.dashboard', ['date' => $selectedCarbon->copy()->subDay()->toDateString()]) }}"
                    class="w-10 h-10 rounded-[10px] border border-slate-200 bg-white text-ink-soft flex items-center justify-center hover:bg-slate-100 hover:text-ink transition" title="Previous day">
                    <i class="fas fa-chevron-left"></i>
                </a>

                <a href="{{ $isToday ? '#' : route('admin.dashboard', ['date' => $selectedCarbon->copy()->addDay()->toDateString()]) }}"
                    class="w-10 h-10 rounded-[10px] border border-slate-200 bg-white text-ink-soft flex items-center justify-center hover:bg-slate-100 hover:text-ink transition {{ $isToday ? 'opacity-35 pointer-events-none' : '' }}" title="Next day">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>

            <button type="submit" class="inline-flex items-center gap-2 rounded-[10px] bg-brand-dark text-white font-bold text-sm px-[18px] py-2.5 hover:opacity-90 transition">
                <i class="fas fa-magnifying-glass"></i> View
            </button>

            @unless ($isToday)
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-[10px] bg-red-100 text-red-800 font-bold text-sm px-4 py-2.5 hover:bg-red-200 transition">
                    <i class="fas fa-clock-rotate-left"></i> Back to Today
                </a>
            @endunless
        </form>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="rounded-brand-lg shadow-card bg-white p-5 sm:p-6 hover:shadow-card-lg hover:-translate-y-0.5 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="font-khmer text-ink-soft font-semibold text-sm">បុគ្គលិកសរុប</div>
                <div class="w-11 h-11 rounded-brand-md bg-slate-200 text-slate-700 flex items-center justify-center text-lg">👥</div>
            </div>
            <h3 class="text-2xl sm:text-[2rem] font-extrabold leading-none">{{ $totalEmployees }}</h3>
            <div class="text-ink-soft text-[13px] mt-2">Total active employees</div>
        </div>

        <div class="rounded-brand-lg shadow-card bg-white p-5 sm:p-6 hover:shadow-card-lg hover:-translate-y-0.5 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="font-khmer text-ink-soft font-semibold text-sm">មកធ្វើការទាន់ម៉ោង</div>
                <div class="w-11 h-11 rounded-brand-md bg-green-100 text-green-800 flex items-center justify-center text-lg">✓</div>
            </div>
            <h3 class="text-2xl sm:text-[2rem] font-extrabold leading-none text-green-600">{{ $presentToday }}</h3>
            <div class="text-ink-soft text-[13px] mt-2">Checked in {{ $isToday ? 'today' : 'on ' . $selectedCarbon->format('d M') }}</div>
        </div>

        <div class="rounded-brand-lg shadow-card bg-white p-5 sm:p-6 hover:shadow-card-lg hover:-translate-y-0.5 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="font-khmer text-ink-soft font-semibold text-sm">មកធ្វើការយឺត</div>
                <div class="w-11 h-11 rounded-brand-md bg-amber-100 text-amber-800 flex items-center justify-center text-lg">⏰</div>
            </div>
            <h3 class="text-2xl sm:text-[2rem] font-extrabold leading-none text-amber-500">{{ $lateToday }}</h3>
            <div class="text-ink-soft text-[13px] mt-2">Late attendance {{ $isToday ? 'today' : 'on ' . $selectedCarbon->format('d M') }}</div>
        </div>

        <div class="rounded-brand-lg shadow-card bg-white p-5 sm:p-6 hover:shadow-card-lg hover:-translate-y-0.5 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="font-khmer text-ink-soft font-semibold text-sm">អវត្តមាន</div>
                <div class="w-11 h-11 rounded-brand-md bg-red-100 text-red-800 flex items-center justify-center text-lg">✕</div>
            </div>
            <h3 class="text-2xl sm:text-[2rem] font-extrabold leading-none text-red-500">{{ $absentToday }}</h3>
            <div class="text-ink-soft text-[13px] mt-2">Absent employees {{ $isToday ? 'today' : 'on ' . $selectedCarbon->format('d M') }}</div>
        </div>
    </div>

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
        <div class="font-khmer flex justify-between items-center flex-wrap gap-2 px-5 sm:px-6 py-4 border-b border-slate-200 font-bold">
            <span>បញ្ជីវត្តមានប្រចាំថ្ងៃ</span>
            <span class="text-ink-soft text-[13px] font-normal">{{ $selectedCarbon->format('l, d M Y') }}</span>
        </div>

        {{-- Desktop/tablet: full table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="font-khmer text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">លេខសម្គាល់បុគ្គលិក</th>
                        <th class="font-khmer text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">ឈ្មោះបុគ្គលិក</th>
                        <th class="font-khmer text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">ផ្នែកការងារ</th>
                        <th class="font-khmer text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">ប្រភេទវត្តមាន</th>
                        <th class="font-khmer text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">ម៉ោងចូល</th>
                        <th class="font-khmer text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">ម៉ោងចេញ</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Status</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Late Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($todayRecords as $record)
                        <tr class="hover:bg-slate-50/80 align-middle">
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <span class="inline-block px-2.5 py-1.5 rounded-[10px] bg-slate-100 text-ink text-[13px] font-bold">{{ $record->employee->employee_code ?? '-' }}</span>
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <div class="font-semibold">{{ $record->employee->full_name ?? '-' }}</div>
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
                            <td colspan="8" class="text-center text-ink-soft py-8">
                                No attendance records today.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile: card list --}}
        <div class="md:hidden">
            @forelse($todayRecords as $record)
                <div class="px-4 py-3.5 border-b border-slate-100 last:border-b-0">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-semibold">{{ $record->employee->full_name ?? '-' }}</div>
                            <span class="inline-block mt-1 px-2.5 py-1.5 rounded-[10px] bg-slate-100 text-ink text-[13px] font-bold">{{ $record->employee->employee_code ?? '-' }}</span>
                        </div>
                        @if($record->status === 'late')
                            <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-amber-100 text-amber-800">Late</span>
                        @elseif($record->status === 'present')
                            <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-green-100 text-green-800">Present</span>
                        @else
                            <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-red-100 text-red-800">{{ ucfirst($record->status) }}</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2.5 text-[12.5px] text-ink-soft mt-1.5">
                        <span><i class="fas fa-sitemap mr-1"></i>{{ $record->employee->department->name ?? '-' }}</span>
                        <span><i class="fas fa-location-dot mr-1"></i>{{ $record->attendancePoint->name ?? '-' }}</span>
                    </div>
                    <div class="flex flex-wrap gap-3 text-[12.5px] font-semibold mt-2">
                        <span><i class="fas fa-arrow-right-to-bracket mr-1 text-green-600"></i>{{ $record->check_in_time ?? '--:--' }}</span>
                        <span><i class="fas fa-arrow-right-from-bracket mr-1 text-amber-500"></i>{{ $record->check_out_time ?? '--:--' }}</span>
                        @if($record->late_minutes > 0)
                            @php
                                $hours = floor($record->late_minutes / 60);
                                $minutes = $record->late_minutes % 60;
                            @endphp
                            <span class="font-bold text-amber-700">
                                <i class="fas fa-clock mr-1"></i>{{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-ink-soft py-8">
                    No attendance records today.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
