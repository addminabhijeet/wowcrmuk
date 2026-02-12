@extends('layout.layout')

@php
    $title = 'SMTP -> Add';
    $role = auth()->user()->role ?? '';
    if ($role === 'admin') {
        $subTitle = 'Super Admin';
    } elseif ($role === 'operation') {
        $subTitle = 'Operation Manager';
    } else {
        $subTitle = 'role';
    }
    $script = '<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>';
@endphp

@section('content')
    <div class="card h-100 p-0 radius-12">
        <div class="card-body p-24">
            <div class="row gy-4">
                <div class="col-xxl-12">
                    <div class="card radius-12 shadow-none border overflow-hidden">

                        {{-- Card Header --}}
                        <div
                            class="card-header bg-neutral-100 border-bottom py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
                            <div class="d-flex align-items-center gap-10">
                                <span
                                    class="w-36-px h-36-px bg-base rounded-circle d-flex justify-content-center align-items-center">
                                    <iconify-icon icon="material-symbols:smtp-outline" class="menu-icon"></iconify-icon>
                                </span>
                                <span class="text-lg fw-semibold text-primary-light">SMTP Settings</span>
                            </div>
                        </div>

                        <div class="card-body p-24">

                            {{-- Single Alert Container --}}
                            <div id="smtpAlertContainer" class="mb-3">
                                @if (session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif
                            </div>

                            {{-- SMTP Update Form --}}
                            <form action="{{ route('smtp.addupdate') }}" method="POST" class="mb-5">
                                @csrf
                                @method('PUT')
                                <div class="row gy-3">

                                    {{-- Mailer --}}
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8">Mailer <span
                                                class="text-danger-600">*</span></label>
                                        <div class="input-group radius-8">
                                            <span class="input-group-text bg-neutral-100 border-neutral-300">
                                                <iconify-icon icon="mdi:email-outline"></iconify-icon>
                                            </span>
                                            <input type="text" name="mailer" class="form-control radius-8">
                                        </div>
                                    </div>

                                    {{-- Host --}}
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8">Host <span
                                                class="text-danger-600">*</span></label>
                                        <div class="input-group radius-8">
                                            <span class="input-group-text bg-neutral-100 border-neutral-300">
                                                <iconify-icon icon="mdi:server-network"></iconify-icon>
                                            </span>
                                            <input type="text" name="host" class="form-control radius-8">
                                        </div>
                                    </div>

                                    {{-- Port --}}
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8">Port <span
                                                class="text-danger-600">*</span></label>
                                        <div class="input-group radius-8">
                                            <span class="input-group-text bg-neutral-100 border-neutral-300">
                                                <iconify-icon icon="mdi:port"></iconify-icon>
                                            </span>
                                            <input type="number" name="port" class="form-control radius-8">
                                        </div>
                                    </div>

                                    {{-- Username --}}
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8">Username <span
                                                class="text-danger-600">*</span></label>
                                        <div class="input-group radius-8">
                                            <span class="input-group-text bg-neutral-100 border-neutral-300">
                                                <iconify-icon icon="mdi:account-outline"></iconify-icon>
                                            </span>
                                            <input type="email" name="username" class="form-control radius-8">
                                        </div>
                                    </div>

                                    {{-- Password --}}
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8">Password
                                            <small>(Leave blank to keep current)</small></label>
                                        <div class="input-group radius-8">
                                            <span class="input-group-text bg-neutral-100 border-neutral-300">
                                                <iconify-icon icon="mdi:lock-outline"></iconify-icon>
                                            </span>
                                            <input type="password" name="password" id="smtpPassword"
                                                class="form-control radius-8">
                                            <span class="input-group-text bg-neutral-100 border-neutral-300 cursor-pointer"
                                                id="togglePassword">
                                                <iconify-icon icon="mdi:eye-outline" id="eyeIcon"></iconify-icon>
                                            </span>
                                            <span class="input-group-text bg-neutral-100 border-neutral-300 cursor-pointer"
                                                id="copyPassword">
                                                <iconify-icon icon="mdi:content-copy"></iconify-icon>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Encryption --}}
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8">Encryption
                                            <span class="text-danger-600">*</span></label>
                                        <div class="input-group radius-8">
                                            <span class="input-group-text bg-neutral-100 border-neutral-300">
                                                <iconify-icon icon="mdi:shield-outline"></iconify-icon>
                                            </span>
                                            <input type="text" name="encryption" class="form-control radius-8">
                                        </div>
                                    </div>

                                    {{-- From Address --}}
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8">From Address
                                            <span class="text-danger-600">*</span></label>
                                        <div class="input-group radius-8">
                                            <span class="input-group-text bg-neutral-100 border-neutral-300">
                                                <iconify-icon icon="mdi:email-open-outline"></iconify-icon>
                                            </span>
                                            <input type="email" name="from_address" class="form-control radius-8">
                                        </div>
                                    </div>

                                    {{-- From Name --}}
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8">From Name
                                            <span class="text-danger-600">*</span></label>
                                        <div class="input-group radius-8">
                                            <span class="input-group-text bg-neutral-100 border-neutral-300">
                                                <iconify-icon icon="mdi:account-box-outline"></iconify-icon>
                                            </span>
                                            <input type="text" name="from_name" class="form-control radius-8">
                                        </div>
                                    </div>

                                    {{-- From Name --}}
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8">
                                            User Name <span class="text-danger-600">*</span>
                                        </label>
                                        <div class="input-group radius-8">
                                            <span class="input-group-text bg-neutral-100 border-neutral-300">
                                                <iconify-icon icon="mdi:account-box-outline"></iconify-icon>
                                            </span>
                                            <select name="user_id" class="form-control radius-8">
                                                <option value="">Select User</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}"
                                                        {{ old('user_id', $smtp->user_id ?? '') == $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Submit Button --}}
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8"><span
                                                class="visibility-hidden">Save</span></label>
                                        <button type="submit"
                                            class="btn btn-primary border border-primary-600 text-md px-24 py-8 radius-8 w-100 text-center">Update</button>
                                    </div>

                                </div>
                            </form>

                            {{-- AJAX TEST EMAIL FORM --}}
                            <hr class="my-4">
                            <h5 class="text-primary-light mb-3">Send Test Email</h5>

                            <form id="testSmtpForm">
                                @csrf
                                <div class="row gy-3 align-items-end">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold text-primary-light text-md mb-8">Recipient
                                            Email</label>
                                        <div class="input-group radius-8">
                                            <span class="input-group-text bg-neutral-100 border-neutral-300">
                                                <iconify-icon icon="mdi:email-send-outline"></iconify-icon>
                                            </span>
                                            <input type="email" name="test_email" id="testEmail"
                                                class="form-control radius-8" placeholder="Enter test email address"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <button type="submit" id="sendTestBtn"
                                            class="btn btn-success border border-success-600 text-md px-24 py-8 radius-8 w-100 text-center">
                                            <span id="btnText">Send Test Email</span>
                                        </button>
                                    </div>
                                </div>
                            </form>

                        </div> <!-- card-body -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            try {
                console.log("[Debug] SMTP page initialized");

                const toggleBtn = $('#togglePassword');
                const passwordInput = $('#smtpPassword');
                const eyeIcon = $('#eyeIcon');
                const copyBtn = $('#copyPassword');
                const alertContainer = $('#smtpAlertContainer');
                const testForm = $('#testSmtpForm');
                const testEmailInput = $('#testEmail');
                const sendBtn = $('#sendTestBtn');
                const btnText = $('#btnText');

                // --- Toggle password ---
                if (toggleBtn.length && passwordInput.length && eyeIcon.length) {
                    toggleBtn.on('click', function() {
                        try {
                            console.log("[Debug] Toggle password clicked");
                            if (passwordInput.attr('type') === 'password') {
                                passwordInput.attr('type', 'text');
                                eyeIcon.attr('icon', 'mdi:eye-off-outline');
                            } else {
                                passwordInput.attr('type', 'password');
                                eyeIcon.attr('icon', 'mdi:eye-outline');
                            }
                        } catch (e) {
                            console.error("[Error] Toggle password failed:", e);
                        }
                    });
                } else {
                    console.warn("[Warning] Toggle password elements missing");
                }

                // --- Copy password ---
                if (copyBtn.length && passwordInput.length) {
                    copyBtn.on('click', function() {
                        try {
                            console.log("[Debug] Copy password clicked");
                            passwordInput.select();
                            document.execCommand('copy');
                            alert('Password copied to clipboard!');
                        } catch (e) {
                            console.error("[Error] Copy password failed:", e);
                        }
                    });
                } else {
                    console.warn("[Warning] Copy password elements missing");
                }

                // --- Show alert ---
                function showSmtpAlert(message, type = 'success') {
                    try {
                        console.log("[Debug] Show alert:", type, message);
                        if (alertContainer.length) {
                            const html = `
                        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                            <strong>${type==='success'?'Success!':'Error!'}</strong> ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
                            alertContainer.prepend(html);
                            setTimeout(() => {
                                $('.alert').alert('close');
                            }, 5000);
                        } else {
                            console.warn("[Warning] Alert container not found");
                        }
                    } catch (e) {
                        console.error("[Error] showSmtpAlert failed:", e);
                    }
                }

                // --- AJAX: Test email ---
                if (testForm.length) {
                    testForm.on('submit', function(e) {
                        e.preventDefault();
                        try {
                            const email = testEmailInput.val();
                            console.log("[Debug] Test email submitted:", email);
                            if (!email) return;

                            sendBtn.prop('disabled', true);
                            btnText.text('Sending...');

                            $.ajax({
                                url: "{{ route('smtp.test') }}",
                                method: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    test_email: email
                                },
                                success: function(res) {
                                    console.log("[Debug] AJAX success:", res);
                                    showSmtpAlert(res.message, 'success');
                                },
                                error: function(xhr, status, error) {
                                    console.error("[Debug] AJAX error", {
                                        status,
                                        error,
                                        xhr
                                    });
                                    let msg = 'Failed to send test email.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr
                                        .responseJSON.message;
                                    showSmtpAlert(msg, 'danger');
                                },
                                complete: function() {
                                    console.log("[Debug] AJAX request completed");
                                    sendBtn.prop('disabled', false);
                                    btnText.text('Send Test Email');
                                }
                            });
                        } catch (e) {
                            console.error("[Error] Test email submit failed:", e);
                        }
                    });
                } else {
                    console.warn("[Warning] Test email form not found");
                }

                // --- Global JS error catcher ---
                window.addEventListener('error', function(event) {
                    console.error("[Global Error] ", event.message, event.filename, event.lineno, event
                        .colno, event.error);
                });

            } catch (e) {
                console.error("[Error] SMTP page initialization failed:", e);
            }
        });
    </script>
@endsection
