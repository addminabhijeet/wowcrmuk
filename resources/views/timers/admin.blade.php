@extends('layout.layout')

@php
    $title = 'Timer -> Settings';
    $role = auth()->user()->role ?? '';
    if ($role === 'admin') {
        $subTitle = 'Super Admin';
    } elseif ($role === 'operation') {
        $subTitle = 'Operation Manager';
    } else {
        $subTitle = 'role';
    }
@endphp

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-none border h-100">
                <div class="card-body p-24">
                    <h5 class="mb-16">Update Timer Settings</h5>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    {{-- Work Day Duration Form --}}
                    <form action="{{ route('timer.updateWorkDay') }}" method="POST" class="mb-24">
                        @csrf
                        <div class="mb-16">
                            <label class="form-label fw-medium">Work Day Duration</label>
                            @php
                                $workSeconds = old('work_day_seconds', $timersetting->work_day_seconds ?? 32400);
                                $hours = floor($workSeconds / 3600);
                                $minutes = floor(($workSeconds % 3600) / 60);
                            @endphp
                            <div class="d-flex gap-2">
                                <select name="hours" class="form-select rounded-pill px-16 py-6">
                                    @for ($h = 0; $h <= 24; $h++)
                                        <option value="{{ $h }}" {{ $h == $hours ? 'selected' : '' }}>
                                            {{ $h }} h</option>
                                    @endfor
                                </select>
                                <select name="minutes" class="form-select rounded-pill px-16 py-6">
                                    @for ($m = 0; $m < 60; $m += 5)
                                        <option value="{{ $m }}" {{ $m == $minutes ? 'selected' : '' }}>
                                            {{ $m }} m</option>
                                    @endfor
                                </select>
                            </div>
                            @error('hours')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            @error('minutes')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-24 py-6">Update Work Day</button>
                        </div>
                    </form>

                    {{-- Daily Base Time Form --}}
                    <form action="{{ route('timer.updateBaseTime') }}" method="POST">
                        @csrf
                        <div class="mb-16">
                            <label class="form-label fw-medium">Daily Base Time</label>
                            <input type="time" name="daily_base_time" class="form-control rounded-pill px-16 py-6"
                                value="{{ old('daily_base_time', $timersetting->daily_base_time ?? '20:00') }}" required>
                            @error('daily_base_time')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-24 py-6">Update Base Time</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
