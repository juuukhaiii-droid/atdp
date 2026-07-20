@extends('layouts.app')
@section('content')
<div class="container-fluid px-3 py-3">
    <div class="row">
        <div class="col-12 col-md-8 mx-auto">
            <div class="card shadow-sm border-0" style="border-radius:16px;">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size:3.5rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">{{ $result['label'] }} Successful</h4>
                    <p class="text-muted mb-4">{{ $result['name'] }} — {{ $result['time'] }}</p>

                    <a href="{{ route('employee.dashboard') }}" class="btn btn-primary px-4 py-2">
                        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
