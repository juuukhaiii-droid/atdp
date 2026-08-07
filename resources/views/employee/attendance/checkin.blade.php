@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center flex-wrap gap-3 mb-6">
        <div>
            <h1 class="font-khmer text-2xl sm:text-3xl font-extrabold tracking-tight mb-1">បានស្កែន</h1>
            <p class="text-ink-soft">Manage employee information and attendance access</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-brand-lg bg-green-100 text-green-800 shadow-sm px-5 py-4 mb-5">
            {{ session('success') }}
        </div>
    @endif
</div>
@endsection
