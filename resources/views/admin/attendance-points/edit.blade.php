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
                    <label>Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $attendancePoint->name) }}" required>
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label>Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code', $attendancePoint->code) }}" required>
                    @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                           value="{{ old('location', $attendancePoint->location) }}">
                    @error('location') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label>Department <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                        <option value="">-- Select Department --</option>
                        @foreach(App\Models\Department::all() as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $attendancePoint->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label>Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', $attendancePoint->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $attendancePoint->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label>QR Code</label><br>
                    @if($attendancePoint->qr_image)
                        <img src="{{ asset($attendancePoint->qr_image) }}" width="120" class="img-thumbnail mb-2">
                        <p class="text-muted text-sm">✓ QR Code is ready</p>
                    @else
                        <span class="text-muted">No QR Code Available</span>
                    @endif
                </div>

                <button class="btn btn-primary">Update</button>
                <a href="{{ route('admin.attendance-points.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>
@endsection
