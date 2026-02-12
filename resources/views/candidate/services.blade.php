@extends('layout.layout')
@php
    $title = $candidate->Name;
    $subTitle = $candidate->sheet_row_number;
    $script = '<script>
        // ======================== Upload Image Start =====================
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("#imagePreview").css("background-image", "url(" + e.target.result + ")");
                    $("#imagePreview").hide().fadeIn(650);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#imageUpload").change(function() {
            readURL(this);
        });

        // ================== Password Show Hide Js Start ==========
        function initializePasswordToggle(toggleSelector) {
            $(toggleSelector).on("click", function() {
                $(this).toggleClass("ri-eye-off-line");
                var input = $($(this).attr("data-toggle"));
                if (input.attr("type") === "password") {
                    input.attr("type", "text");
                } else {
                    input.attr("type", "password");
                }
            });
        }
        // Call the function
        initializePasswordToggle(".toggle-password");
        // ========================= Password Show Hide Js End ===========================
    </script>';
@endphp

@section('content')
    <div class="row gy-4">
        <div class="col-lg-12"><!-- full width column -->
            <div class="card h-100">
                <div class="card-body p-24">

                    <ul class="nav border-gradient-tab nav-pills mb-20 d-inline-flex" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center px-24" id="pills-resume-tab"
                                data-bs-toggle="pill" data-bs-target="#pills-resume" type="button" role="tab"
                                aria-controls="pills-resume" aria-selected="false" tabindex="-1">
                                Resume Improvement
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center px-24" id="pills-linkedin-tab"
                                data-bs-toggle="pill" data-bs-target="#pills-linkedin" type="button" role="tab"
                                aria-controls="pills-linkedin" aria-selected="false" tabindex="-1">
                                LinkedIn Profile
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center px-24" id="pills-coverletter-tab"
                                data-bs-toggle="pill" data-bs-target="#pills-coverletter" type="button" role="tab"
                                aria-controls="pills-coverletter" aria-selected="false" tabindex="-1">
                                Cover letter
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center px-24" id="pills-digitalcard-tab"
                                data-bs-toggle="pill" data-bs-target="#pills-digitalcard" type="button" role="tab"
                                aria-controls="pills-digitalcard" aria-selected="false" tabindex="-1">
                                Digital Card
                            </button>
                        </li>
                    </ul>
                    <form action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade " id="pills-coverletter" role="tabpanel">

                                <div class="row">
                                    <div class="col-12 d-flex">


                                        <!-- SINGLE VERTICAL DIVIDER (FULL HEIGHT) -->
                                        <div class="px-4 d-flex" style="align-items: stretch;">
                                            <div style="width: 1px; background: #ccc; height: 100%;"></div>
                                        </div>
                                        <p class="text-danger">No Cover Letter available.</p>

                                    </div>

                                </div>

                            </div>




                            <div class="tab-pane fade" id="pills-linkedin" role="tabpanel">
                                <div class="row">
                                    <div class="col-12 d-flex">

                                        <!-- SINGLE VERTICAL DIVIDER (FULL HEIGHT) -->
                                        <div class="px-4 d-flex" style="align-items: stretch;">
                                            <div style="width: 1px; background: #ccc; height: 100%;"></div>
                                        </div>
                                        <p class="text-danger">No LinkedIn available.</p>
                                    </div>

                                </div>

                            </div>



                            <div class="tab-pane fade" id="pills-digitalcard" role="tabpanel">
                                <div class="row">
                                    <div class="col-12 d-flex">

                                        <!-- DIVIDER -->
                                        <div class="px-4 d-flex" style="align-items: stretch;">
                                            <div style="width: 1px; background: #ccc; height: 100%;"></div>
                                        </div>
                                        <p class="text-danger">No  Digital Card available.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="pills-resume" role="tabpanel">

                                <!-- Payment -->
                                <h5
                                    style="font-weight:700; color:#0d6efd; margin-bottom:10px; padding:10px 15px;
                                    background:#ffffff; border-left:4px solid #0d6efd; border-radius:6px;
                                    box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                                    Resume
                                </h5>

                                @if (!empty($candidate->resume))
                                    <div
                                        style="width:100%; margin-bottom:30px; height:85vh;
                                    background:#ffffff; border-radius:10px; overflow:hidden;
                                    box-shadow:0 3px 10px rgba(0,0,0,0.12);">
                                        <iframe
                                            src="{{ url('dashboard/senior/google-sheet/view-resume/' . $candidate->id) }}"
                                            style="width:100%; height:100%; border:none;" allowfullscreen>
                                        </iframe>
                                    </div>
                                @else
                                    <p style="color:#dc3545; margin-bottom:30px; font-weight:600;">No resume available.
                                    </p>
                                @endif

                                <!-- Payment -->
                                <h5
                                    style="font-weight:700; color:#0d6efd; margin-bottom:10px; padding:10px 15px;
                                background:#ffffff; border-left:4px solid #0d6efd; border-radius:6px;
                                box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                                    Resume Update
                                </h5>

                                @if (!empty($candidate->updateresume))
                                    <div
                                        style="width:100%; margin-bottom:30px; height:85vh;
                                background:#ffffff; border-radius:10px; overflow:hidden;
                                box-shadow:0 3px 10px rgba(0,0,0,0.12);">
                                        <iframe
                                            src="{{ url('dashboard/senior/google-sheet/view-updateresume/' . $candidate->id) }}"
                                            style="width:100%; height:100%; border:none;" allowfullscreen>
                                        </iframe>
                                    </div>
                                @else
                                    <p style="color:#dc3545; margin-bottom:30px; font-weight:600;">No updated resume
                                        available.
                                    </p>
                                @endif

                            </div>


                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
