@extends('layout.layout')
@php
$title='Add Support Associate';
$role = auth()->user()->role ?? '';
if($role === 'admin'){
    $subTitle = 'Super Admin';
} elseif ($role === 'operation') {
    $subTitle = 'Operation Manager';
} else{
    $subTitle = 'role';
}
$script = '<script>
    // ================== Image Upload Js Start ===========================
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $("#imagePreview").css("background-image", "url(" + e.target.result + ")");
                $("#imagePreview").hide();
                $("#imagePreview").fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#imageUpload").change(function() {
        readURL(this);
    });
    // ================== Image Upload Js End ===========================
</script>';
@endphp

@section('content')

<div class="card h-100 p-0 radius-12">
    <div class="card-body p-24">
        <div class="row justify-content-center">
            <div class="col-xxl-6 col-xl-8 col-lg-10">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="text-md text-primary-light mb-16">Profile Image</h6>
                        <form action="{{ route('users.associate.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Upload Image Start -->
                            <div class="mb-24 mt-16">
                                <div class="avatar-upload">
                                    <div class="avatar-edit position-absolute bottom-0 end-0 me-24 mt-16 z-1 cursor-pointer">
                                        <input type="file" name="image" id="imageUpload" accept=".png, .jpg, .jpeg" hidden>
                                        <label for="imageUpload" class="w-32-px h-32-px d-flex justify-content-center align-items-center bg-primary-50 text-primary-600 border border-primary-600 bg-hover-primary-100 text-lg rounded-circle">
                                            <iconify-icon icon="solar:camera-outline" class="icon"></iconify-icon>
                                        </label>
                                    </div>
                                    <div class="avatar-preview">
                                        <div id="imagePreview"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Upload Image End -->

                            <div class="mb-20">
                                <label for="name" class="form-label fw-semibold text-primary-light text-sm mb-8">
                                    Full Name <span class="text-danger-600">*</span>
                                </label>
                                <input type="text" name="name" class="form-control radius-8" id="name" placeholder="Enter Full Name" required>
                            </div>

                            <div class="mb-20">
                                <label for="email" class="form-label fw-semibold text-primary-light text-sm mb-8">
                                    Email <span class="text-danger-600">*</span>
                                </label>
                                <input type="email" name="email" class="form-control radius-8" id="email" placeholder="Enter email address" required>
                            </div>

                            <div class="mb-20">
                                <label for="phone" class="form-label fw-semibold text-primary-light text-sm mb-8">
                                    Phone
                                </label>
                                <input type="text" name="phone" min="1000000000" max="9999999999" oninput="this.value = this.value.slice(0, 10);" class="form-control radius-8" id="phone" placeholder="Enter phone number">
                            </div>

                            <div class="mb-20">
                                <label for="gender" class="form-label fw-semibold text-primary-light text-sm mb-8">
                                    gender <span class="text-danger-600">*</span>
                                </label>
                                <select name="gender" id="gender" class="form-control radius-8 form-select" required>
                                    <option value="">Select gender</option>

                                    <option value="Caller">Caller</option>
                                </select>
                            </div>

                            <div class="mb-20">
                                <label for="role" class="form-label fw-semibold text-primary-light text-sm mb-8">
                                    Role <span class="text-danger-600">*</span>
                                </label>
                                <select name="role" id="role" class="form-control radius-8 form-select" disabled>
                                    <option value="junior">IT Recruiter</option>
                                    <option value="associate" selected>Support Associate</option>
                                    <option value="admin">Admin</option>
                                    <option value="customer">Customer</option>
                                    <option value="accountant">Support</option>
                                </select>
                                <!-- Hidden input to submit the value -->
                                <input type="hidden" name="role" value="junior">
                            </div>


                            <div class="mb-20">
                                <label for="password" class="form-label fw-semibold text-primary-light text-sm mb-8">
                                    Password <span class="text-danger-600">*</span>
                                </label>
                                <input type="password" name="password" class="form-control radius-8" id="password" placeholder="Enter password" required>
                            </div>

                            <div class="mb-20">
                                <label for="status" class="form-label fw-semibold text-primary-light text-sm mb-8">
                                    Status
                                </label>
                                <select name="status" id="status" class="form-control radius-8 form-select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="d-flex align-items-center justify-content-center gap-3">
                                <button type="submit" class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8">
                                    Save
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
