@extends('layouts.employee-app')
@section('title', $nextAction === 'in' ? 'Check In' : 'Check Out')
@section('content')
    <x-attendance-location-form :action="$nextAction === 'in' ? route('employee.attendance.checkin') : route('employee.attendance.checkout')" :type="$nextAction" :points="$attendancePoints" />
@endsection