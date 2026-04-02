@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Edit Attendance Point</h3>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.attendance-points.update', $attendancePoint) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $attendancePoint->name }}" required>
                </div>

                <div class="mb-3">
                    <label>Code</label>
                    <input type="text" name="code" class="form-control" value="{{ $attendancePoint->code }}" required>
                </div>

                <div class="mb-3">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" value="{{ $attendancePoint->location }}">
                </div>

                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ $attendancePoint->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $attendancePoint->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <button class="btn btn-primary">Update</button>
                <a href="{{ route('admin.attendance-points.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>
@endsection
