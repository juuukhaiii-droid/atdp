@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="page-title mb-1 khmer-text">បង្កើតផ្នែកការងារ</h1>
        <p class="page-desc mb-0">Add a new department for employee organization</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card section-card">
        <div class="card-header khmer-text">
            ព័ត៌មានផ្នែកការងារ
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.departments.store') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Department Name</label>
                        <input type="text" name="name" class="form-control form-control-lg rounded-3" placeholder="Example: Office" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Description</label>
                        <input type="text" name="description" class="form-control form-control-lg rounded-3" placeholder="Example: Main office team">
                        <div class="form-text">Optional short description for this department</div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary px-4 py-2 rounded-3 fw-semibold">Save Department</button>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-light border px-4 py-2 rounded-3 fw-semibold">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
