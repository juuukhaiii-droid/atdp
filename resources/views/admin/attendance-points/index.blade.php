@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4" x-data="{ openQr: null }">
        <div class="flex justify-between items-center flex-wrap gap-3 mb-6">
            <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Attendance Points</h3>
            <a href="{{ route('admin.attendance-points.create') }}" class="inline-flex items-center rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2.5 shadow-sm hover:opacity-90 transition">Add Attendance Point</a>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                class="rounded-brand-lg bg-green-100 text-green-800 shadow-sm px-5 py-4 mb-5 flex items-center justify-between gap-3">
                <span>{{ session('success') }}</span>
                <button type="button" @click="show = false" class="text-green-800/70 hover:text-green-800" aria-label="Close"><i class="fas fa-xmark"></i></button>
            </div>
        @endif

        <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Department</th>
                            <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Name</th>
                            <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Code</th>
                            <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Location</th>
                            <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Status</th>
                            <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">QR Code</th>
                            <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200 w-[280px]">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendancePoints as $point)
                            <tr class="hover:bg-slate-50/80 align-middle">
                                <td class="px-3.5 py-4 border-b border-slate-100">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800">
                                        {{ $point->department->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-4 border-b border-slate-100 font-semibold">{{ $point->name }}</td>
                                <td class="px-3.5 py-4 border-b border-slate-100">{{ $point->code }}</td>
                                <td class="px-3.5 py-4 border-b border-slate-100">{{ $point->location }}</td>
                                <td class="px-3.5 py-4 border-b border-slate-100">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $point->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-700' }}">
                                        {{ ucfirst($point->status) }}
                                    </span>
                                </td>

                                <td class="px-3.5 py-4 border-b border-slate-100">
                                    @if($point->qr_image)
                                        <img src="{{ asset($point->qr_image) }}" width="60" class="rounded-lg border border-slate-200 p-1 cursor-pointer"
                                             @click="openQr = {{ $point->id }}">
                                    @else
                                        <span class="text-ink-soft text-sm">No QR</span>
                                    @endif
                                </td>

                                <td class="px-3.5 py-4 border-b border-slate-100">
                                    <div class="flex gap-2 items-center">
                                        @if($point->qr_image)
                                            <button class="inline-flex items-center gap-1.5 rounded-[10px] bg-green-600 text-white text-sm font-semibold px-3 py-1.5 hover:opacity-90 transition"
                                                    @click="openQr = {{ $point->id }}" title="Print QR Code">
                                                <i class="fas fa-print"></i> Print
                                            </button>
                                            <a href="{{ asset($point->qr_image) }}" download class="inline-flex items-center rounded-[10px] bg-sky-500 text-white text-sm font-semibold px-3 py-1.5 hover:bg-sky-600 transition" title="Download QR Code">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif

                                        <a href="{{ route('admin.attendance-points.edit', $point) }}" class="inline-flex items-center rounded-[10px] bg-amber-400 text-ink text-sm font-semibold px-3 py-1.5 hover:bg-amber-500 transition">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('admin.attendance-points.destroy', $point) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')

                                            <button onclick="return confirm('Delete this attendance point?')" class="inline-flex items-center rounded-[10px] bg-brand-danger text-white text-sm font-semibold px-3 py-1.5 hover:opacity-90 transition">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- QR Code Print Modal --}}
                            <tr x-show="openQr === {{ $point->id }}" x-cloak>
                                <td colspan="7" class="p-0 border-0">
                                    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                                        <div class="absolute inset-0 bg-black/50" @click="openQr = null"></div>
                                        <div class="relative bg-white rounded-brand-lg shadow-card-lg w-full max-w-sm overflow-hidden">
                                            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                                                <h5 class="font-bold">QR Code - {{ $point->name }}</h5>
                                                <button type="button" class="text-ink-soft hover:text-ink" @click="openQr = null" aria-label="Close"><i class="fas fa-xmark"></i></button>
                                            </div>
                                            <div class="text-center p-6">
                                                <img src="{{ asset($point->qr_image) }}" class="max-w-full mx-auto" alt="QR Code">
                                                <p class="mt-3 text-ink-soft text-sm">Department: <strong class="text-ink">{{ $point->department->name ?? 'N/A' }}</strong></p>
                                                <p class="text-ink-soft text-sm">Location: <strong class="text-ink">{{ $point->location }}</strong></p>
                                            </div>
                                            <div class="flex justify-end gap-2 px-5 py-4 border-t border-slate-200">
                                                <button type="button" class="inline-flex items-center rounded-[10px] border border-slate-200 bg-white text-ink font-semibold px-4 py-2 hover:bg-slate-50 transition" @click="openQr = null">Close</button>
                                                <button type="button" class="inline-flex items-center gap-1.5 rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2 hover:opacity-90 transition" onclick="window.print()">
                                                    <i class="fas fa-print"></i> Print
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-ink-soft">No attendance points found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
