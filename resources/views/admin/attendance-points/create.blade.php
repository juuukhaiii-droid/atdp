@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Create Attendance Point</h3>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.attendance-points.store') }}">
                @csrf

                <div class="mb-3">
                    <label>Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           placeholder="Warehouse Entrance" required value="{{ old('name') }}">
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label>Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           placeholder="WH001" required value="{{ old('code') }}">
                    @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                           placeholder="Main Warehouse" value="{{ old('location') }}">
                    @error('location') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label>Department <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label>Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3 p-3 bg-light rounded">
                    <label class="fw-bold">QR Code</label><br>
                    <span class="text-muted">✓ QR Code will be generated automatically after saving</span><br>
                    <span class="text-muted">✓ You can download and print it for employees to scan</span>
                </div>

                <button class="btn btn-primary">Save & Generate QR Code</button>
                <a href="{{ route('admin.attendance-points.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>
@endsection
