@extends('layouts.auth')

@section('title', 'ចូលប្រើប្រាស់')

@section('content')
    <div class="auth-card-header">
        <h1>ភីហ្សា គ្រួសាររីករាយ</h1>
        <p>សូមធ្វើការបំពេញព័ត៌មានខាងក្រោម.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success auth-alert mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger auth-alert mb-4" role="alert">
            <i class="fas fa-circle-exclamation me-2"></i>
            @foreach ($errors->all() as $error)
                {{ $error }}@if (!$loop->last)<br>@endif
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="input-group auth-input-group border">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="you@company.com"
                >
            </div>
        </div>

        <div class="mb-2">
            <label for="password" class="form-label">Password</label>
            <div class="input-group auth-input-group border">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                >
                <span class="input-group-text toggle-password" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4 mt-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember" style="font-size: 14px; color: var(--text-soft);">
                    Remember me
                </label>
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="font-size: 14px; color: var(--brand-primary); text-decoration: none; font-weight: 600;">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="btn btn-brand w-100 text-white py-2">
            <i class="fas fa-right-to-bracket me-2"></i>Sign In
        </button>
    </form>

    <div class="auth-card-footer">
        &copy; {{ date('Y') }} PizzaHappyFamily Attendance System
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    </script>
@endpush
