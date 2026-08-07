@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-3">
        <div class="max-w-2xl mx-auto">
            <div class="rounded-2xl shadow-card-lg bg-white overflow-hidden">
                <div class="p-6 bg-gradient-to-br from-blue-600 to-blue-800 text-white">
                    <h4 class="text-xl font-bold flex items-center gap-2">
                        <i class="fas fa-wifi"></i>
                        {{ $nextAction === 'checkin' ? 'Check In' : 'Check Out' }} (WiFi Verified)
                    </h4>
                </div>
                <div class="p-6">
                    @if ($errors->any())
                        <div x-data="{ show: true }" x-show="show" x-transition class="rounded-xl bg-red-100 text-red-800 px-5 py-4 mb-4 flex items-start justify-between gap-3">
                            <div>
                                <strong class="inline-flex items-center gap-2"><i class="fas fa-exclamation-circle"></i>Error:</strong>
                                <ul class="mt-2 ml-6 list-disc space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button type="button" @click="show = false" class="text-red-800/70 hover:text-red-800" aria-label="Close"><i class="fas fa-xmark"></i></button>
                        </div>
                    @endif

                    <div id="wifiStatusCard" class="rounded-xl border-l-4 border-sky-500 bg-sky-50 px-4 py-3 mb-6">
                        <div class="flex items-center text-sky-800">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            <span>Checking WiFi connection...</span>
                        </div>
                    </div>

                    <form id="checkinForm" method="POST"
                        action="{{ $nextAction === 'checkin' ? route('employee.attendance.checkin') : route('employee.attendance.checkout') }}">
                        @csrf
                        <input type="hidden" id="wifiVerified" name="wifi_verified" value="0">

                        <div class="mb-4">
                            <button type="button" id="checkinBtn"
                                class="w-full py-3.5 rounded-xl font-bold text-white bg-blue-600 shadow-[0_4px_12px_rgba(37,99,235,0.3)] enabled:hover:-translate-y-0.5 enabled:hover:shadow-[0_8px_20px_rgba(37,99,235,0.4)] disabled:opacity-60 disabled:cursor-not-allowed transition inline-flex items-center justify-center gap-2"
                                disabled>
                                <i class="fas fa-camera"></i>
                                {{ $nextAction === 'checkin' ? 'Check In' : 'Check Out' }}
                            </button>

                            <div id="connectionInfo" class="hidden mt-3 rounded-xl bg-slate-100 px-4 py-3 text-sm text-slate-700">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong id="connectionStatus"></strong><br>
                                <span id="connectionDetails"></span>
                            </div>
                        </div>
                    </form>

                    <div class="mt-6 text-center">
                        <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white text-ink font-semibold px-4 py-2.5 hover:bg-slate-50 transition">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <div class="rounded-xl shadow-card bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 mt-4 p-4">
                <small class="text-ink-soft block mb-2">
                    <i class="fas fa-wifi mr-1"></i> <strong>Office WiFi Required</strong>
                </small>
                <small class="text-ink-soft">
                    You must be connected to the office WiFi network to
                    {{ $nextAction === 'checkin' ? 'check in' : 'check out' }}.
                </small>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let wifiPollId = null;

    async function checkWiFiConnection() {
        try {
            const response = await fetch("{{ route('api.verify-network') }}", {
                cache: "no-cache",
                headers: { "Accept": "application/json" }
            });

            const data = await response.json();

            const statusCard = document.getElementById('wifiStatusCard');
            const button = document.getElementById('checkinBtn');

            if (data.verified) {
                statusCard.className = "rounded-xl border-l-4 border-green-500 bg-green-50 px-4 py-3 mb-6";
                statusCard.innerHTML = `
                <div class="flex items-center text-green-800">
                    <i class="fas fa-check-circle mr-2"></i>
                    <strong>Connected to Office WiFi</strong>
                </div>
                `;

                button.disabled = false;
                document.getElementById('wifiVerified').value = 1;

                document.getElementById('connectionInfo').classList.remove('hidden');
                document.getElementById('connectionStatus').innerHTML = "✓ Connected";
                document.getElementById('connectionDetails').innerHTML = data.network_info;
            } else {
                statusCard.className = "rounded-xl border-l-4 border-red-500 bg-red-50 px-4 py-3 mb-6";
                statusCard.innerHTML = `
                <div class="flex items-center text-red-800">
                    <i class="fas fa-times-circle mr-2"></i>
                    <strong>Office WiFi Not Detected</strong>
                </div>
                `;

                button.disabled = true;
                document.getElementById('wifiVerified').value = 0;

                document.getElementById('connectionInfo').classList.remove('hidden');
                document.getElementById('connectionStatus').innerHTML = "✗ Not Connected";
                document.getElementById('connectionDetails').innerHTML = "Please connect to Office WiFi.";
            }
        } catch (e) {
            console.log(e);
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        checkWiFiConnection();
        wifiPollId = setInterval(checkWiFiConnection, 5000);

        document.getElementById("checkinBtn").addEventListener("click", function () {
            if (document.getElementById("wifiVerified").value != 1) {
                alert("Please connect to Office WiFi.");
                return;
            }

            clearInterval(wifiPollId);
            window.location.href = "{{ route('attendance.show.qr') }}";
        });
    });
</script>
@endpush
