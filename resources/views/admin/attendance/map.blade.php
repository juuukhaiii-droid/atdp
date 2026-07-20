
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Scan Attendance QR</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-body">

            <h2 class="text-center mb-4">
                Scan Attendance QR
            </h2>

            <div id="reader"></div>

            <div class="alert alert-info mt-3">
                Point your camera at the QR Code
            </div>

        </div>
    </div>

</div>

<script>

let scanned = false;

function onScanSuccess(decodedText)
{
    if(scanned) return;

    scanned = true;

    html5QrcodeScanner.clear();

    fetch("{{ route('attendance.store') }}", {

        method: "POST",

        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },

        body: JSON.stringify({
            qr_token: decodedText
        })

    })
    .then(response => response.json())
    .then(data => {

        alert(data.message);

        if(data.success){
            window.location.href = "{{ route('attendance.index') }}";
        }else{
            scanned = false;
        }

    })
    .catch(error => {

        console.log(error);

        alert("Error saving attendance");

        scanned = false;
    });
}

const html5QrcodeScanner = new Html5QrcodeScanner(
    "reader",
    {
        fps: 10,
        qrbox: 250,
        rememberLastUsedCamera: true
    },
    false
);

html5QrcodeScanner.render(onScanSuccess);

</script>

</body>
</html>
