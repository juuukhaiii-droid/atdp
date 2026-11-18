@extends('layouts.auth')
@section('title', 'ចូលប្រើប្រាស់')
@section('content')
    <div class="auth-card-header">
        <h1>ចូលប្រើប្រាស់</h1>
    </div>
    @if(session('status'))
        <div class="alert alert-success auth-alert" role="status">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger auth-alert" role="alert">
            <strong>មិនអាចចូលគណនីបាន។</strong>
            @foreach($errors->all() as $error)
            <div>{{ $error }}</div>@endforeach
        </div>
    @endif
    <form id="login-form" method="POST" action="{{ route('login') }}">
        @csrf
        <div class="auth-field">
            <label for="identifier" class="form-label">ឈ្មោះពេញ ឬអ៊ីមែល</label>
            <div class="auth-input-group">
                <span class="auth-field-icon"><x-auth-icon name="user" /></span>
                <input id="identifier" name="identifier" type="text" class="form-control"
                    value="{{ old('identifier', old('full_name', old('email'))) }}" required autocomplete="username"
                    autocapitalize="none" spellcheck="false" placeholder="ឈ្មោះពេញ ឬអ៊ីមែល" @if($errors->has('identifier') || $errors->has('full_name') || $errors->has('email')) aria-invalid="true" @endif>
            </div>
        </div>
        <div class="auth-field">
            <label for="password" class="form-label">ពាក្យសម្ងាត់ ឬលេខ PIN</label>
            <div class="auth-input-group">
                <span class="auth-field-icon"><x-auth-icon name="lock" /></span>
                <input id="password" name="password" type="password" class="form-control" required
                    autocomplete="current-password" placeholder="ពាក្យសម្ងាត់ ឬ PIN ៤ ខ្ទង់" @if($errors->has('password') || $errors->has('pin')) aria-invalid="true" @endif>
                <button type="button" class="toggle-password" id="togglePassword" aria-label="បង្ហាញពាក្យសម្ងាត់ ឬ PIN"
                    aria-pressed="false"><span data-eye><x-auth-icon name="eye" /></span><span data-eye-off
                        hidden><x-auth-icon name="eye-off" /></span></button>
            </div>
        </div>
        <div class="login-options">
            <label class="remember-label" for="remember"><input type="checkbox" name="remember" value="1" id="remember"
                    @checked(old('remember'))> ចងចាំគណនី</label>
        </div>
        <button type="submit" class="btn btn-brand"><span>ចូលប្រើប្រាស់</span><x-auth-icon name="arrow" /></button>
    </form>
@endsection
@push('scripts')
    <script>
        (() => {
            const passwordToggle = document.getElementById('togglePassword');
            passwordToggle.addEventListener('click', () => {
                const input = document.getElementById('password');
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                passwordToggle.setAttribute('aria-pressed', String(show));
                passwordToggle.setAttribute('aria-label', show ? 'លាក់ពាក្យសម្ងាត់ ឬ PIN' : 'បង្ហាញពាក្យសម្ងាត់ ឬ PIN');
                passwordToggle.querySelector('[data-eye]').hidden = show;
                passwordToggle.querySelector('[data-eye-off]').hidden = !show;
            });
            const form = document.getElementById('login-form');
            const button = form.querySelector('button[type=submit]');
            form.addEventListener('submit', () => {
                button.disabled = true;
                button.querySelector('span').textContent = 'កំពុងចូល…';
                form.setAttribute('aria-busy', 'true');
            });
            window.addEventListener('pageshow', () => {
                button.disabled = false;
                button.querySelector('span').textContent = 'ចូលប្រើប្រាស់';
                form.setAttribute('aria-busy', 'false');
            });
        })();
    </script>
@endpush