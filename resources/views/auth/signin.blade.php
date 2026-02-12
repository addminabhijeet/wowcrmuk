<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<x-head />

<body>

    <section class="auth bg-base d-flex flex-wrap">
        <div class="auth-left d-lg-block d-none">
            <div class="d-flex align-items-center flex-column h-100 justify-content-center">
                <img src="{{ asset('assets/images/auth/auth-img.png') }}" alt="">
            </div>
        </div>

        <div class="auth-right py-32 px-24 d-flex flex-column justify-content-center">
            <div class="max-w-464-px mx-auto w-100">
                <div style="text-align: center;">
                    <a href="" class="mb-40 max-w-290-px" style="width: 50%;">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="">
                    </a>
                    <h4 class="mb-12">Sign In to your Account</h4>
                    <p class="mb-32 text-secondary-light text-lg">Welcome back! Please enter your details</p>
                </div>

                {{-- ✅ Display All Error or Success Messages --}}
                @if ($errors->any())
                <div class="alert alert-danger radius-12 mb-20" style="padding: 12px; background:#ffe6e6; border:1px solid #ffb3b3; color:#b30000;">
                    <ul class="mb-0" style="list-style: none; padding-left: 0;">
                        @foreach ($errors->all() as $error)
                        <li>⚠️ {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('status'))
                <div class="alert alert-success radius-12 mb-20" style="padding: 12px; background:#e6ffed; border:1px solid #b3ffcc; color:#006622;">
                    ✅ {{ session('status') }}
                </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf

                    <!-- Email -->
                    <div class="icon-field mb-16">
                        <span class="icon top-50 translate-middle-y">
                            <iconify-icon icon="mage:email"></iconify-icon>
                        </span>
                        <input type="email" id="email" name="email"
                            value="{{ old('email') }}" required autocomplete="email"
                            class="form-control h-56-px bg-neutral-50 radius-12"
                            placeholder="Email" autofocus>
                    </div>

                    <!-- Password -->
                    <div class="position-relative mb-20">
                        <div class="icon-field">
                            <span class="icon top-50 translate-middle-y">
                                <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                            </span>
                            <input type="password" id="password" name="password"
                                required autocomplete="current-password"
                                class="form-control h-56-px bg-neutral-50 radius-12"
                                placeholder="Password">
                        </div>
                        <span class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light"
                            data-toggle="#password"></span>
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="d-flex justify-content-between gap-2">
                        <div class="form-check style-check d-flex align-items-center">
                            <input class="form-check-input border border-neutral-300"
                                type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="javascript:void(0)" class="text-primary-600 fw-medium">Forgot Password?</a>
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                        class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12 mt-32">
                        Sign In
                    </button>

                    <!-- Register Link -->
                    <div class="mt-32 text-center text-sm">
                        <p class="mb-0">
                            Don’t have an account?
                            <a href="" class="text-primary-600 fw-semibold">Please Contact Admin</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- Add jQuery before your custom script -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            $(".toggle-password").on("click", function() {
                try {
                    let input = $($(this).data("toggle"));
                    if (input.attr("type") === "password") {
                        input.attr("type", "text");
                        $(this).removeClass("ri-eye-line").addClass("ri-eye-off-line");
                    } else {
                        input.attr("type", "password");
                        $(this).removeClass("ri-eye-off-line").addClass("ri-eye-line");
                    }
                } catch (error) {
                    console.log("Password toggle error:", error);
                }
            });
        });
    </script>




</body>

</html>