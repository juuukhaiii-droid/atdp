@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="page-title mb-1">Edit Shift</h1>
        <p class="page-desc mb-0">Update shift time and attendance rule settings</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card section-card">
        <div class="card-header">
            Edit Shift Details
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.shifts.update', $shift) }}">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Shift Name</label>
                        <input type="text" name="name" class="form-control form-control-lg rounded-3" value="{{ $shift->name }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Start Time</label>
                        <input type="time" name="start_time" class="form-control form-control-lg rounded-3" value="{{ $shift->start_time }}" required>
                        <div class="form-text">Employee normal work start time</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">End Time</label>
                        <input type="time" name="end_time" class="form-control form-control-lg rounded-3" value="{{ $shift->end_time }}" required>
                        <div class="form-text">Employee normal work finish time</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Late After</label>
                        <input type="time" name="late_after" class="form-control form-control-lg rounded-3" value="{{ $shift->late_after }}" required>
                        <div class="form-text">Attendance after this time is late</div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary px-4 py-2 rounded-3 fw-semibold">Update Shift</button>
                    <a href="{{ route('admin.shifts.index') }}" class="btn btn-light border px-4 py-2 rounded-3 fw-semibold">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>  
@endsection
