@extends('layout.layout')
@php
$title='View Support Associate';
$role = auth()->user()->role ?? '';
if($role === 'admin'){
    $subTitle = 'Super Admin';
} elseif ($role === 'operation') {
    $subTitle = 'Operation Manager';
} else{
    $subTitle = 'role';
}
$script ='<script>
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
    <div class="col-lg-4">
        <div class="user-grid-card position-relative border radius-16 overflow-hidden bg-base h-100" style="padding-top: 100px;">
            <img src="{{ $user->image ? asset('assets/images/user-grid/' . $user->image) : asset('assets/images/users/user1.png') }}" alt="" class="w-100 object-fit-cover">
            <div class="pb-24 ms-16 mb-24 me-16  mt--100">
                <div class="text-center border border-top-0 border-start-0 border-end-0">
                    <img src="{{ $user->image ? asset('assets/images/user-grid/' . $user->image) : asset('assets/images/users/user1.png') }}" alt="" class="border br-white border-width-2-px w-200-px h-200-px rounded-circle object-fit-cover">
                    <h6 class="mb-0 mt-16">{{ $user->name }}</h6>
                    <span class="text-secondary-light mb-16">{{ $user->email }}</span>
                </div>
                <div class="mt-24">
                    <h6 class="text-xl mb-16">Personal Info</h6>
                    <ul>
                        <li class="d-flex align-items-center gap-1 mb-12">
                            <span class="w-30 text-md fw-semibold text-primary-light">Full Name</span>
                            <span class="w-70 text-secondary-light fw-medium">: {{ $user->name }}</span>
                        </li>
                        <li class="d-flex align-items-center gap-1 mb-12">
                            <span class="w-30 text-md fw-semibold text-primary-light"> Email</span>
                            <span class="w-70 text-secondary-light fw-medium">: {{ $user->email }}</span>
                        </li>
                        <li class="d-flex align-items-center gap-1 mb-12">
                            <span class="w-30 text-md fw-semibold text-primary-light"> Phone Number</span>
                            <span class="w-70 text-secondary-light fw-medium">: {{ $user->phone }}</span>
                        </li>
                        <li class="d-flex align-items-center gap-1 mb-12">
                            <span class="w-30 text-md fw-semibold text-primary-light"> Role</span>
                            <span class="w-70 text-secondary-light fw-medium">
                                : {{ $user->role === 'associate' ? 'Support Associate' : $user->role }}
                            </span>

                        </li>
                        <li class="d-flex align-items-center gap-1 mb-12">
                            <span class="w-30 text-md fw-semibold text-primary-light"> gender</span>
                            <span class="w-70 text-secondary-light fw-medium">: {{ $user->gender === 'associate' ? 'Support Associate' : $user->gender}}</span>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>


    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body p-24">

                <ul class="nav border-gradient-tab nav-pills mb-20 d-inline-flex" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center px-24 active" id="pills-edit-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-edit-profile" type="button" role="tab" aria-controls="pills-edit-profile" aria-selected="true">
                            Edit Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center px-24" id="pills-change-passwork-tab" data-bs-toggle="pill" data-bs-target="#pills-change-passwork" type="button" role="tab" aria-controls="pills-change-passwork" aria-selected="false" tabindex="-1">
                            Change Password
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center px-24" id="pills-notification-tab" data-bs-toggle="pill" data-bs-target="#pills-notification" type="button" role="tab" aria-controls="pills-notification" aria-selected="false" tabindex="-1">
                            Settings
                        </button>
                    </li>
                </ul>
                <form action="{{ route('users.associate.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-edit-profile" role="tabpanel">
                            <h6 class="text-md text-primary-light mb-16">Profile Image</h6>

                            <div class="mb-24 mt-16">
                                <div class="avatar-upload">
                                    <div class="avatar-edit position-absolute bottom-0 end-0 me-24 mt-16 z-1 cursor-pointer">
                                        <input type="file" name="image" id="imageUpload" accept=".png, .jpg, .jpeg" hidden>
                                        <label for="imageUpload"
                                            class="w-32-px h-32-px d-flex justify-content-center align-items-center bg-primary-50 text-primary-600 border border-primary-600 bg-hover-primary-100 text-lg rounded-circle">
                                            <iconify-icon icon="solar:camera-outline" class="icon"></iconify-icon>
                                        </label>
                                    </div>

                                    <div class="avatar-preview">
                                        <img id="imagePreview"
                                            src="{{ $user->image ? asset('storage/user_images/' . $user->image) : asset('assets/images/user-grid/user-grid-img14.png') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-20">
                                        <label for="name" class="form-label fw-semibold text-primary-light text-sm mb-8">Full Name <span class="text-danger-600">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control radius-8"
                                            value="{{ old('name', $user->name) }}" placeholder="Enter Full Name" required>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="mb-20">
                                        <label for="email" class="form-label fw-semibold text-primary-light text-sm mb-8">Email <span class="text-danger-600">*</span></label>
                                        <input type="email" name="email" id="email" class="form-control radius-8"
                                            value="{{ old('email', $user->email) }}" placeholder="Enter email address" required>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="mb-20">
                                        <label for="phone" class="form-label fw-semibold text-primary-light text-sm mb-8">Phone</label>
                                        <input type="number" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                                            min="1000000000" max="9999999999"
                                            oninput="this.value = this.value.slice(0, 10);"
                                            placeholder="Enter phone number" class="form-control radius-8">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="mb-20">
                                        <label for="role" class="form-label fw-semibold text-primary-light text-sm mb-8">
                                            Role <span class="text-danger-600">*</span>
                                        </label>
                                        <select name="role" id="role" class="form-control radius-8 form-select" required>
                                            <option value="" disabled>Select Role</option>
                                            <option value="junior" {{ $user->role == 'junior' ? 'selected' : '' }}>IT Recruiter</option>
                                            <option value="associate" {{ $user->role == 'associate' ? 'selected' : '' }}>Support Associate</option>
                                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="candidate" {{ $user->role == 'customer' ? 'selected' : '' }}>Customer</option>
                                            <option value="accountant" {{ $user->role == 'accountant' ? 'selected' : '' }}>Support</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="mb-20">
                                        <label for="gender" class="form-label fw-semibold text-primary-light text-sm mb-8">
                                            gender <span class="text-danger-600">*</span>
                                        </label>
                                        <select name="gender" id="gender" class="form-control radius-8 form-select" required>
                                            <option value="" disabled {{ !$user->gender ? 'selected' : '' }}>Select gender</option>
                                            <option value="Accountant" {{ $user->gender == 'Accountant' ? 'selected' : '' }}>Support</option>
                                            <option value="Admin" {{ $user->gender == 'Admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="Candidate" {{ $user->gender == 'Candidate' ? 'selected' : '' }}>Candidate</option>
                                            <option value="Junior" {{ $user->gender == 'Junior' ? 'selected' : '' }}>IT Recruiter</option>
                                            <option value="Associate" {{ $user->gender == 'Associate' ? 'selected' : '' }}>Support Associate</option>
                                            <option value="Trainer" {{ $user->gender == 'Trainer' ? 'selected' : '' }}>Trainer</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <button type="submit" class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8">
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pills-change-passwork" role="tabpanel">
                            <div class="mb-20">
                                <label for="your-password" class="form-label fw-semibold text-primary-light text-sm mb-8">New Password</label>
                                <input type="password" name="password" class="form-control radius-8" id="your-password" placeholder="Enter New Password">
                            </div>
                            <div class="mb-20">
                                <label for="confirm-password" class="form-label fw-semibold text-primary-light text-sm mb-8">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control radius-8" id="confirm-password" placeholder="Confirm Password">
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pills-notification" role="tabpanel">
                            <div class="form-switch switch-primary py-12 px-16 border radius-8 position-relative mb-16">
                                <div class="d-flex align-items-center gap-3 justify-content-between">
                                    <span class="form-check-label line-height-1 fw-medium text-secondary-light">Account Status</span>
                                    <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ $user->status ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



@endsection
