@extends('layouts.employee-app')
@section('title', $nextAction === 'in' ? 'ចូលធ្វើការ' : 'ចេញពីធ្វើការ')
@section('content')
    <x-attendance-location-form :action="$nextAction === 'in' ? route('employee.attendance.checkin') : route('employee.attendance.checkout')" :type="$nextAction" :points="$attendancePoints" />
@endsection