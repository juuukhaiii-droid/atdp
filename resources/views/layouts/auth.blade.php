<!DOCTYPE html>
<html lang="km">

<head>
    <x-app-icons />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign In') · PizzaHappyFamily</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&family=Noto+Sans+Khmer:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}" rel="stylesheet">
    @stack('styles')
</head>

<body>
    <main class="auth-shell">
        <aside class="auth-brand" aria-label="PizzaHappyFamily attendance">
            <a class="auth-brand-mark" href="{{ route('login') }}" aria-label="PizzaHappyFamily sign in">
                <img src="{{ asset('images/pizzahappyfamily-logo.jpg') }}" width="52" height="52"
                    alt="PizzaHappyFamily logo">
                <span><strong>PizzaHappyFamily<span class="brand-dot">.</span></strong><small>
                        ATTENDANCE</small></span>
            </a>
            <div class="auth-brand-body">
                <div class="brand-eyebrow"><span></span> ចាប់ផ្ដើមថ្ងៃថ្មីជាមួយគ្នា</div>
                <h2>ក្រុមការងារយើង។<br>ពេលវេលាយើង។<br><em>រីករាយជាមួយគ្នា។</em></h2>
                <p class="auth-brand-desc">កត់ត្រាវត្តមានងាយៗ<br>សម្រាប់ក្រុមគ្រួសាររបស់យើង។</p>
                <div class="attendance-illustration"
                    aria-label="Scan your QR, verify your location, and start your day">
                    <div class="illustration-top"><span class="illustration-label">វត្តមានប្រចាំថ្ងៃ</span><span
                            class="illustration-dots" aria-hidden="true">•••</span></div>
                    <div class="routine-main">
                        <div class="routine-qr" aria-hidden="true"><x-auth-icon name="qr" /><span></span></div>
                        <div><strong>ស្កេនមួយដង។<br>រួចរាល់។</strong>
                            <p>ងាយស្រួលគ្រប់វេនការងារ។</p>
                        </div>
                    </div>
                    <div class="routine-steps"><span><b>01</b> ស្កេន QR</span><i aria-hidden="true">→</i><span><b>02</b>
                            ពិនិត្យទីតាំង</span><i aria-hidden="true">→</i><span><b>03</b> រួចរាល់</span></div>
                </div>
                <p class="brand-location-note"><x-auth-icon name="location" /> Location checked only when you clock in
                    or out.</p>
            </div>
            <div class="auth-brand-footer"><span>@Attendance Management</span><span>@SENSOTHI_Dev_Version1. ↗</span>
            </div>
        </aside>
        <section class="auth-form-side" aria-label="Account access">
            <div class="auth-mobile-mark"><img src="{{ asset('images/pizzahappyfamily-logo.jpg') }}" width="42"
                    height="42" alt="PizzaHappyFamily logo"><strong>PizzaHappyFamily<span
                        class="brand-dot">.</span></strong></div>
            <div class="auth-card">@yield('content')</div>
            <footer class="auth-page-footer">&copy; {{ now()->year }} PizzaHappyFamily <span>Team &amp;
                    Attendance</span></footer>
        </section>
    </main>
    @stack('scripts')
</body>

</html>
