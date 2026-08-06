@extends('layouts.auth')

@section('title', 'ចូលប្រើប្រាស់')

@section('content')
    @php $activeMode = old('login_type', 'employee'); @endphp

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

    <div class="login-mode-toggle" role="tablist">
        <button type="button" class="login-mode-btn {{ $activeMode === 'employee' ? 'active' : '' }}" data-mode="employee">
            <i class="fas fa-user me-1"></i>Employee
        </button>
        <button type="button" class="login-mode-btn {{ $activeMode === 'admin' ? 'active' : '' }}" data-mode="admin">
            <i class="fas fa-user-shield me-1"></i>Admin
        </button>
    </div>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <input type="hidden" name="login_type" id="login_type" value="{{ $activeMode }}">

        {{-- Employee fields: full name + PIN --}}
        <div id="employee-fields" style="display: {{ $activeMode === 'employee' ? 'block' : 'none' }};">
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <div class="input-group auth-input-group border">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input
                        id="full_name"
                        type="text"
                        name="full_name"
                        class="form-control @error('full_name') is-invalid @enderror"
                        value="{{ old('full_name') }}"
                        {{ $activeMode === 'employee' ? 'required autofocus' : '' }}
                        autocomplete="username"
                        placeholder="Your full name"
                    >
                </div>
            </div>

            <div class="mb-2">
                <label for="pin" class="form-label">PIN</label>
                <div class="input-group auth-input-group border">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input
                        id="pin"
                        type="password"
                        name="pin"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="4"
                        class="form-control @error('pin') is-invalid @enderror"
                        {{ $activeMode === 'employee' ? 'required' : '' }}
                        autocomplete="current-password"
                        placeholder="••••"
                    >
                    <span class="input-group-text toggle-password" id="togglePin">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>
        </div>

        {{-- Admin fields: email + password --}}
        <div id="admin-fields" style="display: {{ $activeMode === 'admin' ? 'block' : 'none' }};">
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
                        {{ $activeMode === 'admin' ? 'required autofocus' : '' }}
                        autocomplete="username"
                        placeholder="user1@gmail.com"
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
                        {{ $activeMode === 'admin' ? 'required' : '' }}
                        autocomplete="current-password"
                        placeholder="••••••••"
                    >
                    <span class="input-group-text toggle-password" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4 mt-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember" style="font-size: 14px; color: var(--text-soft);">
                    Remember me
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-brand w-100 text-white py-2">
            <i class="fas fa-right-to-bracket me-2"></i>Sign In
        </button>
    </form>

    <div class="auth-card-footer">
        &copy; {{ date('Y') }} PizzaHappyFamily Attendance System
    </div>
@endsection

@push('styles')
<style>
    .login-mode-toggle {
        display: flex;
        background: var(--bg-soft);
        border-radius: var(--radius-md);
        padding: 4px;
        margin-bottom: 24px;
        gap: 4px;
    }

    .login-mode-btn {
        flex: 1;
        border: 0;
        background: transparent;
        padding: 10px;
        font-weight: 700;
        font-size: 13.5px;
        color: var(--text-soft);
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }

    .login-mode-btn:hover:not(.active) {
        color: var(--text-main);
        background: rgba(255, 255, 255, 0.6);
    }

    .login-mode-btn.active {
        background: #fff;
        color: var(--text-main);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.1);
    }

    #pin {
        letter-spacing: 0.35em;
        font-size: 1.05rem;
    }

    #pin::placeholder {
        letter-spacing: normal;
        font-size: 1rem;
    }

    .btn-brand:disabled {
        opacity: 0.75;
        cursor: not-allowed;
        transform: none;
    }
</style>
@endpush

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

        document.getElementById('togglePin').addEventListener('click', function () {
            const input = document.getElementById('pin');
            const icon = this.querySelector('i');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });

        const modeButtons = document.querySelectorAll('.login-mode-btn');
        const employeeFields = document.getElementById('employee-fields');
        const adminFields = document.getElementById('admin-fields');
        const loginType = document.getElementById('login_type');
        const forgotLink = document.getElementById('forgot-password-link');

        modeButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                modeButtons.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');

                const mode = btn.dataset.mode;
                loginType.value = mode;

                const isEmployee = mode === 'employee';
                employeeFields.style.display = isEmployee ? 'block' : 'none';
                adminFields.style.display = isEmployee ? 'none' : 'block';
                if (forgotLink) {
                    forgotLink.style.display = isEmployee ? 'none' : 'inline';
                }

                document.getElementById('full_name').required = isEmployee;
                document.getElementById('pin').required = isEmployee;
                document.getElementById('email').required = !isEmployee;
                document.getElementById('password').required = !isEmployee;
            });
        });

        document.querySelector('form').addEventListener('submit', function () {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing in...';
        });
    </script>
@endpush
