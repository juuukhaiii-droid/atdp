@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">ម៉ោងធ្វើការ</h1>
            <p class="page-desc mb-0">Manage working time, shift hours, and late attendance rules</p>
        </div>

        <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm">
            + បន្ថែមម៉ោងធ្វើការថ្មី
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card section-card">
        <div class="card-header">
             បញ្ជីម៉ោងធ្វើការ (Shifts) - Define work schedules and attendance rules for employees
        </div>

        <div class="card-body table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Late After</th>
                        <th width="200">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $shift->name }}</span>
                                    <span class="muted-small">Working schedule</span>
                                </div>
                            </td>
                            <td>
                                <span class="time-pill">{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}</span>
                            </td>
                            <td>
                                <span class="time-pill">{{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</span>
                            </td>
                            <td>
                                <span class="late-pill">{{ \Carbon\Carbon::parse($shift->late_after)->format('h:i A') }}</span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.shifts.edit', $shift) }}" class="btn btn-warning btn-sm rounded-3 px-3 fw-semibold">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.shifts.destroy', $shift) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Delete this shift?')" class="btn btn-danger btn-sm rounded-3 px-3 fw-semibold">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <div class="fs-5 fw-semibold mb-1">No shifts found</div>
                                    <div>Create your first shift to define attendance time rules.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .time-pill {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 10px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 13px;
        font-weight: 700;
    }

    .late-pill {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 10px;
        background: #fef3c7;
        color: #92400e;
        font-size: 13px;
        font-weight: 700;
    }
</style>
@endpush
