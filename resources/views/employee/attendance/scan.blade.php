@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Camera Test</h2>
<video id="video" autoplay playsinline style="width:100%;height:400px;background:#000"></video>

<script>
const video = document.getElementById('video');

async function startCamera() {

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("This browser does not support camera.");
        return;
    }

    try {

        const stream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: { ideal: "environment" }
            },
            audio: false
        });

        video.srcObject = stream;

    } catch (e) {

        console.error(e);

        alert(
            "Camera Error\n\n" +
            e.name + "\n" +
            e.message
        );
    }
}

startCamera();
</script>