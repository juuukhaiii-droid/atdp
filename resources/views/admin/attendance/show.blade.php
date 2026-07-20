<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .attendance-card {
            max-width: 520px;
            margin: auto;
            border-radius: 20px;
            border: none;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }

        .attendance-title {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
        }

        .point-badge {
            font-size: 1rem;
            padding: 10px 16px;
            border-radius: 30px;
        }

        .big-input {
            height: 54px;
            font-size: 1.05rem;
            border-radius: 12px;
        }

        .big-btn {
            height: 54px;
            font-size: 1.05rem;
            border-radius: 12px;
            font-weight: 600;
        }

        .auto-note {
            font-size: 0.9rem;
            color: #6c757d;
        }

        /* GPS status styles */
        .gps-box {
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 0.95rem;
            margin-bottom: 16px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card attendance-card p-4">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h1 class="attendance-title">Staff Attendance</h1>
                    <p class="mb-2">
                        <span class="badge bg-primary point-badge">{{ $attendancePoint->name }}</span>
                    </p>
                    <p class="text-muted mb-0">{{ $attendancePoint->location }}</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success text-center">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger text-center">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger text-center">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('attendance.submit', $attendancePoint->qr_token) }}"
                    id="attendanceForm">
                    @csrf

                    {{-- GPS hidden fields --}}
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    {{-- GPS Status Box --}}
                    <div id="gps-status" class="gps-box bg-warning-subtle text-warning-emphasis">
                        📍 Getting your location, please wait...
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Employee Code</label>
                        <input type="text" id="employee_code" name="employee_code" class="form-control big-input"
                            placeholder="Enter employee code" value="{{ old('employee_code') }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">PIN</label>
                        <input type="password" name="pin" class="form-control big-input" placeholder="Enter 4-digit PIN"
                            required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="rememberEmployee">
                        <label class="form-check-label" for="rememberEmployee">
                            Remember Employee Code
                        </label>
                    </div>

                    {{-- Button disabled until GPS is ready --}}
                    <button type="submit" class="btn btn-primary w-100 big-btn" id="submit-btn" disabled>
                        📍 Waiting for location...
                    </button>
                </form>

                <div class="text-center mt-3 auto-note">
                    First submit = check in, second submit = check out
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-reload after alert
        setTimeout(() => {
            const hasAlert = document.querySelector('.alert-success, .alert-danger');
            if (hasAlert) {
                window.location.href = window.location.href;
            }
        }, 3000);

        // Remember Employee Code
        document.addEventListener('DOMContentLoaded', function () {

            const employeeInput = document.getElementById('employee_code');
            const rememberCheckbox = document.getElementById('rememberEmployee');

            const savedCode = localStorage.getItem('employee_code');

            if (savedCode) {
                employeeInput.value = savedCode;
                rememberCheckbox.checked = true;
            }

            document.getElementById('attendanceForm').addEventListener('submit', function () {

                if (rememberCheckbox.checked) {
                    localStorage.setItem(
                        'employee_code',
                        employeeInput.value
                    );
                } else {
                    localStorage.removeItem('employee_code');
                }

            });

        });

        // GPS Detection
        const statusBox = document.getElementById('gps-status');
        const submitBtn = document.getElementById('submit-btn');

        if (!navigator.geolocation) {

            statusBox.className = 'gps-box bg-danger-subtle text-danger-emphasis';
            statusBox.innerHTML = '❌ GPS not supported on this device. Cannot submit.';

        } else {

            navigator.geolocation.getCurrentPosition(

                function (position) {

                    document.getElementById('latitude').value =
                        position.coords.latitude;

                    document.getElementById('longitude').value =
                        position.coords.longitude;

                    statusBox.className =
                        'gps-box bg-success-subtle text-success-emphasis';

                    statusBox.innerHTML =
                        '✅ Location captured. You may submit.';

                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Submit Attendance';
                },

                function (error) {

                    let message = '';

                    switch (error.code) {
                        case 1:
                            message = '❌ Location permission denied.';
                            break;
                        case 2:
                            message = '❌ Location unavailable.';
                            break;
                        case 3:
                            message = '❌ Location request timeout.';
                            break;
                        default:
                            message = '❌ Unable to get location.';
                    }

                    statusBox.className =
                        'gps-box bg-danger-subtle text-danger-emphasis';

                    statusBox.innerHTML = message;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '❌ Location Required';

                    console.log('GPS Error:', error);
                },

                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
            navigator.geolocation.getCurrentPosition(
                function (position) {

                    alert(
                        "Lat: " + position.coords.latitude +
                        "\nLng: " + position.coords.longitude
                    );

                    document.getElementById('latitude').value =
                        position.coords.latitude;

                    document.getElementById('longitude').value =
                        position.coords.longitude;

                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Submit Attendance';
                },
                function (error) {
                    alert(
                        "GPS Error\nCode: " +
                        error.code +
                        "\nMessage: " +
                        error.message
                    );
                }
            );
        }
    </script>
</body>

</html>
