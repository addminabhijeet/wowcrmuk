<?php
$title = 'SMTP -> Edit';
$subTitle = 'Super Admin';
$script = '<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>';
?>

<?php $__env->startSection('content'); ?>

<div class="card h-100 p-0 radius-12">
    <div class="card-body p-24">
        <div class="row gy-4">
            <div class="col-xxl-12">
                <div class="card radius-12 shadow-none border overflow-hidden">

                    
                    <div class="card-header bg-neutral-100 border-bottom py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
                        <div class="d-flex align-items-center gap-10">
                            <span class="w-36-px h-36-px bg-base rounded-circle d-flex justify-content-center align-items-center">
                                <iconify-icon icon="material-symbols:smtp-outline" class="menu-icon"></iconify-icon>
                            </span>
                            <span class="text-lg fw-semibold text-primary-light">SMTP Settings</span>
                        </div>
                    </div>

                    <div class="card-body p-24">

                        
                        <div id="smtpAlertContainer" class="mb-3">
                            <?php if(session('success')): ?>
                            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                            <?php endif; ?>
                            <?php if(session('error')): ?>
                            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
                            <?php endif; ?>
                        </div>

                        
                        <form action="<?php echo e(route('smtp.update', $smtp->user_id ?? auth()->id())); ?>" method="POST" class="mb-5">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div class="row gy-3">

                                
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold text-primary-light text-md mb-8">Mailer <span class="text-danger-600">*</span></label>
                                    <div class="input-group radius-8">
                                        <span class="input-group-text bg-neutral-100 border-neutral-300">
                                            <iconify-icon icon="mdi:email-outline"></iconify-icon>
                                        </span>
                                        <input type="text" name="mailer" class="form-control radius-8" value="<?php echo e(old('mailer', $smtp->mailer ?? 'smtp')); ?>">
                                    </div>
                                </div>

                                
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold text-primary-light text-md mb-8">Host <span class="text-danger-600">*</span></label>
                                    <div class="input-group radius-8">
                                        <span class="input-group-text bg-neutral-100 border-neutral-300">
                                            <iconify-icon icon="mdi:server-network"></iconify-icon>
                                        </span>
                                        <input type="text" name="host" class="form-control radius-8" value="<?php echo e(old('host', $smtp->host ?? '')); ?>">
                                    </div>
                                </div>

                                
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold text-primary-light text-md mb-8">Port <span class="text-danger-600">*</span></label>
                                    <div class="input-group radius-8">
                                        <span class="input-group-text bg-neutral-100 border-neutral-300">
                                            <iconify-icon icon="mdi:port"></iconify-icon>
                                        </span>
                                        <input type="number" name="port" class="form-control radius-8" value="<?php echo e(old('port', $smtp->port ?? 465)); ?>">
                                    </div>
                                </div>

                                
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold text-primary-light text-md mb-8">Username <span class="text-danger-600">*</span></label>
                                    <div class="input-group radius-8">
                                        <span class="input-group-text bg-neutral-100 border-neutral-300">
                                            <iconify-icon icon="mdi:account-outline"></iconify-icon>
                                        </span>
                                        <input type="email" name="username" class="form-control radius-8" value="<?php echo e(old('username', $smtp->username ?? '')); ?>">
                                    </div>
                                </div>

                                
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold text-primary-light text-md mb-8">Password <small>(Leave blank to keep current)</small></label>
                                    <div class="input-group radius-8">
                                        <span class="input-group-text bg-neutral-100 border-neutral-300">
                                            <iconify-icon icon="mdi:lock-outline"></iconify-icon>
                                        </span>
                                        <input type="password" name="password" id="smtpPassword" class="form-control radius-8">
                                        <span class="input-group-text bg-neutral-100 border-neutral-300 cursor-pointer" id="togglePassword">
                                            <iconify-icon icon="mdi:eye-outline" id="eyeIcon"></iconify-icon>
                                        </span>
                                        <span class="input-group-text bg-neutral-100 border-neutral-300 cursor-pointer" id="copyPassword">
                                            <iconify-icon icon="mdi:content-copy"></iconify-icon>
                                        </span>
                                    </div>
                                </div>

                                
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold text-primary-light text-md mb-8">Encryption <span class="text-danger-600">*</span></label>
                                    <div class="input-group radius-8">
                                        <span class="input-group-text bg-neutral-100 border-neutral-300">
                                            <iconify-icon icon="mdi:shield-outline"></iconify-icon>
                                        </span>
                                        <input type="text" name="encryption" class="form-control radius-8" value="<?php echo e(old('encryption', $smtp->encryption ?? 'ssl')); ?>">
                                    </div>
                                </div>

                                
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold text-primary-light text-md mb-8">From Address <span class="text-danger-600">*</span></label>
                                    <div class="input-group radius-8">
                                        <span class="input-group-text bg-neutral-100 border-neutral-300">
                                            <iconify-icon icon="mdi:email-open-outline"></iconify-icon>
                                        </span>
                                        <input type="email" name="from_address" class="form-control radius-8" value="<?php echo e(old('from_address', $smtp->from_address ?? '')); ?>">
                                    </div>
                                </div>

                                
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold text-primary-light text-md mb-8">From Name <span class="text-danger-600">*</span></label>
                                    <div class="input-group radius-8">
                                        <span class="input-group-text bg-neutral-100 border-neutral-300">
                                            <iconify-icon icon="mdi:account-box-outline"></iconify-icon>
                                        </span>
                                        <input type="text" name="from_name" class="form-control radius-8" value="<?php echo e(old('from_name', $smtp->from_name ?? '')); ?>">
                                    </div>
                                </div>

                                
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold text-primary-light text-md mb-8">
                                        Signature Image
                                    </label>
                                    <div class="input-group radius-8">
                                        <span class="input-group-text bg-neutral-100 border-neutral-300">
                                            <iconify-icon icon="mdi:image-outline"></iconify-icon>
                                        </span>
                                        <input type="file" name="signature_image" id="signatureImage" accept="image/*" class="form-control radius-8">
                                    </div>

                                    
                                    <?php if(!empty($smtp->signature_image)): ?>
                                    <div class="mt-2">
                                        <p class="text-sm text-primary-light mb-1">Current Signature Image:</p>
                                        <img src="<?php echo e(asset('storage/'.$smtp->signature_image)); ?>"
                                            alt="Signature Image"
                                            class="img-fluid rounded border"
                                            style="max-height: 120px;">
                                    </div>
                                    <?php endif; ?>

                                    
                                    <div id="previewContainer" class="mt-2" style="display:none;">
                                        <p class="text-sm text-primary-light mb-1">Preview:</p>
                                        <img id="previewImage" class="img-fluid rounded border" style="max-height: 120px;">
                                    </div>
                                </div>


                                
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold text-primary-light text-md mb-8">
                                        User Name <span class="text-danger-600">*</span>
                                    </label>
                                    <div class="input-group radius-8">
                                        <span class="input-group-text bg-neutral-100 border-neutral-300">
                                            <iconify-icon icon="mdi:account-box-outline"></iconify-icon>
                                        </span>
                                        <select name="user_id" class="form-control radius-8" disabled>
                                            <option value="">Select User</option>
                                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($user->id); ?>"
                                                <?php echo e(old('user_id', $smtp->user_id ?? '') == $user->id ? 'selected' : ''); ?>>
                                                <?php echo e($user->name); ?>

                                            </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>



                                
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold text-primary-light text-md mb-8"><span class="visibility-hidden">Save</span></label>
                                    <button type="submit" class="btn btn-primary border border-primary-600 text-md px-24 py-8 radius-8 w-100 text-center">Update</button>
                                </div>

                            </div>
                        </form>

                        
                        <hr class="my-4">
                        <h5 class="text-primary-light mb-3">Send Test Email</h5>

                        <form id="testSmtpForm">
                            <?php echo csrf_field(); ?>
                            <div class="row gy-3 align-items-end">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold text-primary-light text-md mb-8">Recipient Email</label>
                                    <div class="input-group radius-8">
                                        <span class="input-group-text bg-neutral-100 border-neutral-300">
                                            <iconify-icon icon="mdi:email-send-outline"></iconify-icon>
                                        </span>
                                        <input type="email" name="test_email" id="testEmail" class="form-control radius-8" placeholder="Enter test email address" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <button type="submit" id="sendTestBtn" class="btn btn-success border border-success-600 text-md px-24 py-8 radius-8 w-100 text-center">
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
                            url: "<?php echo e(route('smtp.test')); ?>",
                            method: "POST",
                            data: {
                                _token: "<?php echo e(csrf_token()); ?>",
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
                                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
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
                console.error("[Global Error] ", event.message, event.filename, event.lineno, event.colno, event.error);
            });

        } catch (e) {
            console.error("[Error] SMTP page initialization failed:", e);
        }
    });

    // --- Signature image preview ---
    $('#signatureImage').on('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImage').attr('src', e.target.result);
                $('#previewContainer').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#previewContainer').hide();
        }
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u235777426/domains/admin.pdfreducer.com/public_html/resources/views/smtp/edit.blade.php ENDPATH**/ ?>