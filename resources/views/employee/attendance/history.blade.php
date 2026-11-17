@extends('layouts.employee-app')

@section('title', 'History')

@section('content')

    <div class="section-title" style="margin-top:0;">Attendance History</div>

    @forelse ($records as $record)
        <div class="app-card mb-3">@include('employee.attendance.day')</div>
    @empty
        <div class="app-card"><div class="card-body text-center py-4">
            <p>No attendance records yet.</p>
            <a href="{{ route('attendance.show.qr') }}" class="btn btn-danger">Scan QR to Check In</a>
        </div></div>
    @endforelse

    @if ($records->hasPages())
        <div class="d-flex justify-content-center mt-2 mb-3">
            {{ $records->links() }}
        </div>
    @endif

@endsection

@push('styles')
<style>
    .history-date-label {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--text-soft);
        margin: 4px 2px 8px;
    }

    .activity-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
    }

    .activity-row-border { border-bottom: 1px solid var(--border-soft); }

    .activity-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .activity-icon-in { background: #dcfce7; color: #16a34a; }
    .activity-icon-out { background: #fef3c7; color: #f59e0b; }

    .activity-title { font-weight: 700; font-size: 13.5px; }
    .activity-sub { font-size: 12px; color: var(--text-soft); }
    .activity-time { font-weight: 700; font-size: 12.5px; }
</style>
@endpush
