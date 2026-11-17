@extends('layouts.auth')
@section('title', 'Sign In')
@section('content')
    <div class="auth-card-header">
        <h1>ភីហ្សា គ្រួសាររីករាយ</h1>
        <p>សូមធ្វើការបំពេញព័ត៌មានខាងក្រោម.</p>
    </div>
    @if(session('status'))
    <div class="alert alert-success auth-alert mb-4" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger auth-alert mb-4" role="alert">
            @foreach($errors->all() as $error){{ $error }}@if(!$loop->last)<br>@endif @endforeach</div>
    @endif
    <form id="login-form" method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label for="identifier" class="form-label">Full name or email</label>
            <div class="input-group auth-input-group border">
                <span class="input-group-text"><i class="fas fa-user" aria-hidden="true"></i></span>
                <input id="identifier" name="identifier" type="text" class="form-control"
                    value="{{ old('identifier', old('full_name', old('email'))) }}" required autofocus
                    autocomplete="username" placeholder="Your full name or email" aria-describedby="login-help">
            </div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password or PIN</label>
            <div class="input-group auth-input-group border">
                <span class="input-group-text"><i class="fas fa-lock" aria-hidden="true"></i></span>
                <input id="password" name="password" type="password" class="form-control" required
                    autocomplete="current-password" placeholder="Your password or 4-digit PIN"
                    aria-describedby="login-help">
                <button type="button" class="input-group-text toggle-password" id="togglePassword"
                    aria-label="Show password or PIN" aria-pressed="false"><i class="fas fa-eye"
                        aria-hidden="true"></i></button>
            </div>
        </div>
        <p id="login-help" class="login-help">Use your full name and 4-digit PIN, or your admin email and password.</p>
        <a href="{{ route('password.request') }}" class="login-help">Forgot admin password?</a>
        <div class="form-check mt-3 mb-3">
            <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember" checked>
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <button type="submit" class="btn btn-brand w-100 text-white py-2"><i class="fas fa-right-to-bracket me-2"
                aria-hidden="true"></i>Sign In</button>
    </form>
    <div class="auth-card-footer">&copy; {{ date('Y') }} PizzaHappyFamily Attendance System</div>
@endsection
@push('styles')
    <style>
        .login-help {
            font-size: 14px;
            line-height: 1.7;
            color: var(--text-soft);
            margin: 12px 0 18px
        }

        .toggle-password:focus-visible {
            outline: 2px solid var(--brand-primary);
            outline-offset: 2px
        }

        .btn-brand:disabled {
            opacity: .75;
            cursor: not-allowed
        }
    </style>
@endpush
@push('scripts')
    <script>
        const passwordToggle = document.getElementById('togglePassword');
        passwordToggle.addEventListener('click', () => {
            const input = document.getElementById('password');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            passwordToggle.setAttribute('aria-pressed', String(show));
            passwordToggle.setAttribute('aria-label', show ? 'Hide password or PIN' : 'Show password or PIN');
            passwordToggle.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
        const loginForm = document.getElementById('login-form');
        loginForm.addEventListener('submit', () => {
            const button = loginForm.querySelector('button[type=submit]');
            button.disabled = true;
            button.textContent = 'Signing in?';
        });
        window.addEventListener('pageshow', () => {
            const button = loginForm.querySelector('button[type=submit]');
            button.disabled = false;
            button.textContent = 'Sign In';
        });
    </script>
@endpush
