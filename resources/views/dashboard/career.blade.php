@extends('layout.layout')

@php
$title='Dashboard';
$subTitle = 'Record System';
$script= '<script src="' . asset('assets/js/homeOneChart.js') . '"></script>';
@endphp

@section('content')

<div class="row row-cols-xxl-4 row-cols-md-4 row-cols-sm-2 row-cols-1 g-4">

    <!-- Resume Builder -->
    <div class="col">
        <div class="card h-100 border-0 shadow-sm career-card"
            style="background: linear-gradient(135deg, #e3f2fd, #90caf9); border-radius: 20px; color: #0d47a1;">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <h5 class="fw-bold mb-1">Resume Builder</h5>
                    <p class="mb-0" style="opacity: 0.8;">Create a professional resume</p>
                </div>
                <iconify-icon icon="mdi:file-document-edit" style="font-size: 45px; color: #0d47a1;"></iconify-icon>
            </div>
        </div>
    </div>

    <!-- Job Alerts -->
    <div class="col">
        <div class="card h-100 border-0 shadow-sm career-card"
            style="background: linear-gradient(135deg, #f3e5f5, #ce93d8); border-radius: 20px; color: #6a1b9a;">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <h5 class="fw-bold mb-1">Job Alerts</h5>
                    <p class="mb-0" style="opacity: 0.8;">Get instant job alerts</p>
                </div>
                <iconify-icon icon="mdi:bell-ring-outline" style="font-size: 45px; color: #6a1b9a;"></iconify-icon>
            </div>
        </div>
    </div>

    <!-- Skill Assessments -->
    <div class="col">
        <div class="card h-100 border-0 shadow-sm career-card"
            style="background: linear-gradient(135deg, #fff3e0, #ffcc80); border-radius: 20px; color: #ef6c00;">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <h5 class="fw-bold mb-1">Skill Assessments</h5>
                    <p class="mb-0" style="opacity: 0.8;">Test & improve your skills</p>
                </div>
                <iconify-icon icon="mdi:chart-line" style="font-size: 45px; color: #ef6c00;"></iconify-icon>
            </div>
        </div>
    </div>

    <!-- Profile Strength -->
    <div class="col">
        <div class="card h-100 border-0 shadow-sm career-card"
            style="background: linear-gradient(135deg, #e8f5e9, #a5d6a7); border-radius: 20px; color: #2e7d32;">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <h5 class="fw-bold mb-1">Profile Strength</h5>
                    <p class="mb-0" style="opacity: 0.8;">Boost your visibility</p>
                </div>
                <iconify-icon icon="mdi:account-badge" style="font-size: 45px; color: #2e7d32;"></iconify-icon>
            </div>
        </div>
    </div>

    <!-- Saved Jobs -->
    <div class="col">
        <div class="card h-100 border-0 shadow-sm career-card"
            style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); border-radius: 20px; color: #1565c0;">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <h5 class="fw-bold mb-1">Saved Jobs</h5>
                    <p class="mb-0" style="opacity: 0.8;">Jobs you saved</p>
                </div>
                <iconify-icon icon="mdi:bookmark-multiple" style="font-size: 45px; color: #1565c0;"></iconify-icon>
            </div>
        </div>
    </div>

    <!-- Applied Jobs -->
    <div class="col">
        <div class="card h-100 border-0 shadow-sm career-card"
            style="background: linear-gradient(135deg, #ffebee, #ffcdd2); border-radius: 20px; color: #c62828;">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <h5 class="fw-bold mb-1">Applied Jobs</h5>
                    <p class="mb-0" style="opacity: 0.8;">Track applied jobs</p>
                </div>
                <iconify-icon icon="mdi:briefcase-check" style="font-size: 45px; color: #c62828;"></iconify-icon>
            </div>
        </div>
    </div>

    <!-- Career Guidance -->
    <div class="col">
        <div class="card h-100 border-0 shadow-sm career-card"
            style="background: linear-gradient(135deg, #ede7f6, #d1c4e9); border-radius: 20px; color: #4527a0;">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <h5 class="fw-bold mb-1">Career Guidance</h5>
                    <p class="mb-0" style="opacity: 0.8;">Get expert advice</p>
                </div>
                <iconify-icon icon="mdi:lightbulb-on-outline" style="font-size: 45px; color: #4527a0;"></iconify-icon>
            </div>
        </div>
    </div>

    <!-- Recommended Jobs -->
    <div class="col">
        <div class="card h-100 border-0 shadow-sm career-card"
            style="background: linear-gradient(135deg, #fff8e1, #ffe082); border-radius: 20px; color: #f9a825;">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <h5 class="fw-bold mb-1">Recommended Jobs</h5>
                    <p class="mb-0" style="opacity: 0.8;">Jobs based on your profile</p>
                </div>
                <iconify-icon icon="mdi:thumb-up-outline" style="font-size: 45px; color: #f9a825;"></iconify-icon>
            </div>
        </div>
    </div>

</div>

<!-- Hover Animation -->
<style>
    .career-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .career-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.12) !important;
    }
</style>



@endsection