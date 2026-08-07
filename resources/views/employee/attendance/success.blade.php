@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-3">
    <div class="max-w-2xl mx-auto">
        <div class="rounded-2xl shadow-card bg-white overflow-hidden">
            <div class="p-8 text-center">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-green-500 text-6xl"></i>
                </div>
                <h4 class="text-xl font-bold mb-2">{{ $result['label'] }} Successful</h4>
                <p class="text-ink-soft mb-6">{{ $result['name'] }} — {{ $result['time'] }}</p>

                <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-primary text-white font-semibold px-4 py-2.5 hover:opacity-90 transition">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
