@extends('layouts.app')

@section('title', 'Attendance History')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center page-header flex-wrap gap-2">
        <div>
            <h1 class="page-title khmer-text">ប្រវត្តិវត្តមាន</h1>
            <p class="page-desc">Your check-in / check-out history</p>
        </div>
        <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <div class="card section-card">
        <div class="card-body table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->scanned_at->format('d M Y') }}</td>
                            <td>{{ $record->scanned_at->format('h:i A') }}</td>
                            <td>
                                @if ($record->type === 'in')
                                    <span class="status-badge status-present">Check In</span>
                                @else
                                    <span class="status-badge status-late">Check Out</span>
                                @endif
                            </td>
                            <td>{{ $record->attendancePoint->name ?? '-' }}</td>
                            <td class="text-muted">{{ $record->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No attendance records yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div class="card-body border-top">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
