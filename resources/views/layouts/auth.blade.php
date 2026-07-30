<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') — PizzaHappyFamily Attendance</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Khmer:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand-dark: #111827;
            --brand-dark-soft: #1f2937;
            --brand-primary: #dc2626;
            --brand-primary-soft: #fee2e2;
            --text-main: #0f172a;
            --text-soft: #64748b;
            --border-soft: #e5e7eb;
            --bg-soft: #f8fafc;
            --card-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            --radius-lg: 18px;
        }

        * { box-sizing: border-box; }

        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            background: var(--bg-soft);
            color: var(--text-main);
            font-family: 'Inter', 'Noto Sans Khmer', sans-serif;
            display: flex;
            min-height: 100vh;
        }

        .auth-shell {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .auth-brand {
            flex: 1 1 44%;
            background: linear-gradient(160deg, var(--brand-dark) 0%, var(--brand-dark-soft) 55%, var(--brand-primary) 160%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 44px;
            position: relative;
            overflow: hidden;
        }

        .auth-brand::before {
            content: "";
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: rgba(220, 38, 38, 0.18);
            top: -160px;
            right: -140px;
        }

        .auth-brand::after {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            bottom: -120px;
            left: -100px;
        }

        .auth-brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 1;
        }

        .auth-brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.12);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .auth-brand-title {
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .auth-brand-subtitle {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.65);
        }

        .auth-brand-body {
            position: relative;
            z-index: 1;
            margin-top: 40px;
        }

        .auth-brand-headline {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.25;
            margin-bottom: 16px;
        }

        .auth-brand-desc {
            color: rgba(255, 255, 255, 0.72);
            font-size: 15px;
            line-height: 1.6;
            max-width: 380px;
        }

        .auth-feature-list {
            list-style: none;
            padding: 0;
            margin: 28px 0 0;
            position: relative;
            z-index: 1;
        }

        .auth-feature-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 14px;
        }

        .auth-feature-list i {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        .auth-brand-footer {
            position: relative;
            z-index: 1;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
        }

        .auth-form-side {
            flex: 1 1 56%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
        }

        .auth-card-header {
            margin-bottom: 28px;
        }

        .auth-card-header h1 {
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .auth-card-header p {
            color: var(--text-soft);
            font-size: 14px;
            margin-bottom: 0;
        }

        .auth-input-group {
            border-radius: 12px;
            overflow: hidden;
        }

        .auth-input-group .input-group-text {
            background: #fff;
            border-right: none;
            color: var(--text-soft);
        }

        .auth-input-group .form-control {
            border-left: none;
            padding-top: 11px;
            padding-bottom: 11px;
        }

        .auth-input-group .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }

        .auth-input-group:focus-within {
            box-shadow: 0 0 0 0.2rem rgba(220, 38, 38, 0.12);
            border-radius: 12px;
        }

        .form-label {
            font-weight: 600;
            font-size: 13px;
            color: var(--text-main);
        }

        .btn-brand {
            background: var(--brand-primary);
            border-color: var(--brand-primary);
            font-weight: 700;
            border-radius: 12px;
            padding: 11px 18px;
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.25);
            transition: all 0.2s ease;
        }

        .btn-brand:hover, .btn-brand:focus {
            background: #b91c1c;
            border-color: #b91c1c;
            box-shadow: 0 10px 24px rgba(220, 38, 38, 0.32);
        }

        .toggle-password {
            background: #fff;
            border-left: none;
            color: var(--text-soft);
            cursor: pointer;
        }

        .auth-alert {
            border-radius: 12px;
            border: 0;
            font-size: 14px;
        }

        .auth-card-footer {
            margin-top: 26px;
            text-align: center;
            font-size: 13px;
            color: var(--text-soft);
        }

        @media (max-width: 991.98px) {
            .auth-brand { display: none; }
            .auth-form-side { flex: 1 1 100%; }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="auth-brand">
            <div class="auth-brand-mark">
                <span class="auth-brand-icon"><i class="fas fa-store"></i></span>
                <div>
                    <div class="auth-brand-title">ភីហ្សា គ្រួសាររីករាយ</div>
                    <div class="auth-brand-subtitle">PizzaHappyFamily</div>
                </div>
            </div>

            <div class="auth-brand-body">
                <div class="auth-brand-headline">Attendance,<br>simplified.</div>
                <p class="auth-brand-desc">
                    Sign in to scan your attendance QR code, track your check-in history,
                    and keep the team in sync.
                </p>

                <ul class="auth-feature-list">
                    <li><i class="fas fa-qrcode"></i> Scan-to-check-in with QR codes</li>
                    <li><i class="fas fa-wifi"></i> Office WiFi verified attendance</li>
                    <li><i class="fab fa-telegram"></i> Instant Telegram alerts</li>
                </ul>
            </div>

            <div class="auth-brand-footer">
                &copy; {{ date('Y') }} PizzaHappyFamily &mdash; Attendance Management System
            </div>
        </div>

        <div class="auth-form-side">
            <div class="auth-card">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
