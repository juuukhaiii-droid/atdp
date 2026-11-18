@extends('layouts.employee-app')
@section('title', $nextAction === 'in' ? 'ចូលធ្វើការ' : 'ចេញពីធ្វើការ')
@section('content')
<x-attendance-location-form :action="route('attendance.submit', $token)" :type="$nextAction" :point="$attendancePoint" :auto="true" :scan-id="$scanId" />
@endsection
