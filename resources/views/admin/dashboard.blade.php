@extends('layouts.app')

@section('content')
<div class="container">
   <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4 dash-header">
    <div>
        <h1 class="page-title khmer-text">ប្រព័ន្ធត្រួតពិនិត្យវត្តមាន</h1>
        <p class="page-desc khmer-text">សង្ខេបព័ត៌មានវត្តមានបុគ្គលិកប្រចាំថ្ងៃ</p>
    </div>
    <div class="text-md-end dash-date">
        <div class="fw-semibold">{{ now()->format('d M Y') }}</div>
        <div class="text-muted">{{ now()->format('h:i A') }}</div>
    </div>
</div>

    <div class="row g-2 g-md-4 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="summary-top mb-3">
                        <div class="summary-label khmer-text">បុគ្គលិកសរុប</div>
                        <div class="summary-icon" style="background:#e2e8f0; color:#334155;">👥</div>
                    </div>
                    <h3 class="summary-value">{{ $totalEmployees }}</h3>
                    <div class="muted-small mt-2">Total active employees</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="summary-top mb-3">
                        <div class="summary-label khmer-text">មកធ្វើការទាន់ម៉ោង</div>
                        <div class="summary-icon" style="background:#dcfce7; color:#166534;">✓</div>
                    </div>
                    <h3 class="summary-value" style="color:#16a34a;">{{ $presentToday }}</h3>
                    <div class="muted-small mt-2">Checked in today</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="summary-top mb-3">
                        <div class="summary-label khmer-text">មកធ្វើការយឺត</div>
                        <div class="summary-icon" style="background:#fef3c7; color:#92400e;">⏰</div>
                    </div>
                    <h3 class="summary-value" style="color:#f59e0b;">{{ $lateToday }}</h3>
                    <div class="muted-small mt-2">Late attendance today</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="summary-top mb-3">
                        <div class="summary-label khmer-text">អវត្តមាន</div>
                        <div class="summary-icon" style="background:#fee2e2; color:#991b1b;">✕</div>
                    </div>
                    <h3 class="summary-value" style="color:#ef4444;">{{ $absentToday }}</h3>
                    <div class="muted-small mt-2">Absent employees today</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card section-card">
        <div class="card-header khmer-text">
            បញ្ជីវត្តមានប្រចាំថ្ងៃ
        </div>

        {{-- Desktop/tablet: full table --}}
        <div class="card-body table-responsive d-none d-md-block">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th class="khmer-text">លេខសម្គាល់បុគ្គលិក</th>
                        <th class="khmer-text">ឈ្មោះបុគ្គលិក</th>
                        <th class="khmer-text">ផ្នែកការងារ</th>
                        <th class="khmer-text">ប្រភេទវត្តមាន</th>
                        <th class="khmer-text">ម៉ោងចូល</th>
                        <th class="khmer-text">ម៉ោងចេញ</th>
                        <th>Status</th>
                        <th>Late Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($todayRecords as $record)
                        <tr>
                            <td>
                                <span class="code-pill">{{ $record->employee->employee_code ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $record->employee->full_name ?? '-' }}</div>
                            </td>
                            <td>{{ $record->employee->department->name ?? '-' }}</td>
                            <td>{{ $record->attendancePoint->name ?? '-' }}</td>
                            <td>{{ $record->check_in_time ?? '-' }}</td>
                            <td>{{ $record->check_out_time ?? '-' }}</td>
                            <td>
                                @if($record->status === 'late')
                                    <span class="status-badge status-late">Late</span>
                                @elseif($record->status === 'present')
                                    <span class="status-badge status-present">Present</span>
                                @else
                                    <span class="status-badge status-absent">{{ ucfirst($record->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $hours = floor($record->late_minutes / 60);
                                    $minutes = $record->late_minutes % 60;
                                @endphp

                                @if($record->late_minutes > 0)
                                    <span class="late-time-text">
                                        {{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @else
                                    <span class="text-muted">00:00</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No attendance records today.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile: card list --}}
        <div class="card-body d-md-none p-0">
            @forelse($todayRecords as $record)
                <div class="mobile-record-row">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">{{ $record->employee->full_name ?? '-' }}</div>
                            <span class="code-pill">{{ $record->employee->employee_code ?? '-' }}</span>
                        </div>
                        @if($record->status === 'late')
                            <span class="status-badge status-late">Late</span>
                        @elseif($record->status === 'present')
                            <span class="status-badge status-present">Present</span>
                        @else
                            <span class="status-badge status-absent">{{ ucfirst($record->status) }}</span>
                        @endif
                    </div>
                    <div class="mobile-record-meta">
                        <span><i class="fas fa-sitemap me-1"></i>{{ $record->employee->department->name ?? '-' }}</span>
                        <span><i class="fas fa-location-dot me-1"></i>{{ $record->attendancePoint->name ?? '-' }}</span>
                    </div>
                    <div class="mobile-record-times">
                        <span><i class="fas fa-arrow-right-to-bracket me-1 text-success"></i>{{ $record->check_in_time ?? '--:--' }}</span>
                        <span><i class="fas fa-arrow-right-from-bracket me-1 text-warning"></i>{{ $record->check_out_time ?? '--:--' }}</span>
                        @if($record->late_minutes > 0)
                            @php
                                $hours = floor($record->late_minutes / 60);
                                $minutes = $record->late_minutes % 60;
                            @endphp
                            <span class="late-time-text">
                                <i class="fas fa-clock me-1"></i>{{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    No attendance records today.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media (max-width: 767.98px) {
        .dash-header {
            gap: 4px !important;
        }

        .dash-date {
            font-size: 13px;
        }
    }

    .mobile-record-row {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-soft);
    }

    .mobile-record-row:last-child {
        border-bottom: 0;
    }

    .mobile-record-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        font-size: 12.5px;
        color: var(--text-soft);
        margin-top: 6px;
    }

    .mobile-record-times {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 12.5px;
        font-weight: 600;
        margin-top: 8px;
    }
</style>
@endpush
