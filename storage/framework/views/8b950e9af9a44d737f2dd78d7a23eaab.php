<?php
$title='Notice';
$subTitle = 'Admin Dashboard';
$script ='<script>
    // ========================= Adjust Textarea Height depending of text lines(default height 40px) Js Start ===========================
    function adjustHeight(textarea) {
        // Calculate the scroll height of the content
        let scrollHeight = textarea.scrollHeight;

        // Set the textarea height to the scroll height, but not exceeding the maximum height
        if (scrollHeight > 44 && scrollHeight <= 60) {
            textarea.style.height = scrollHeight + "px";
        } else if (scrollHeight > 60) {
            // textarea.style.height = "60px !important";
            textarea.setAttribute("style", "height: 60px !important;");
        }
    }
    // ========================= Adjust Textarea Height depending of text lines(default height 40px) Js End ===========================
</script>';
?>

<?php $__env->startSection('content'); ?>

<div class="row gy-4">
    <div class="col-12"> <!-- FULL WIDTH -->
        <div class="card h-100 p-0 email-card w-100"> <!-- Ensure card is 100% -->
            <div class="d-flex flex-column justify-content-between h-100 w-100"> <!-- remove min width -->

                <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center gap-3 justify-content-between flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <button class="text-secondary-light d-flex me-8">
                            <iconify-icon icon="mingcute:arrow-left-line" class="icon fs-3 line-height-1"></iconify-icon>
                        </button>
                        <h6 class="mb-0 text-lg">Payment Notifications</h6>
                    </div>
                </div>

                <div class="card-body p-0">

                    <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                    $msg = $note->data ?? '';
                    $userName = $note->user->name ?? 'Unknown User';
                    $userEmail = $note->user->email ?? '';

                    $candidate = $note->candidate;
                    $candidateName = $candidate->Name ?? null;
                    $candidateEmail = $candidate->Email_Address ?? null;
                    $candidatePhone = $candidate->Phone_Number ?? null;
                    $candidateCourse = $candidate->Course ?? null;
                    ?>

                    <div class="py-16 px-24 border-bottom w-100">
                        <div class="d-flex align-items-start gap-3">

                            <img src="<?php echo e(asset('assets/images/user-list/user-list1.png')); ?>"
                                alt=""
                                class="w-40-px h-40-px rounded-pill">

                            <div>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <h6 class="mb-0 text-lg">
                                        <?php if($candidateName): ?>
                                        <span class="text-primary-600 ms-2"><?php echo e($candidateName); ?></span>
                                        <?php endif; ?>
                                    </h6>

                                    <span class="text-secondary-light text-md">
                                        <?php if($candidateEmail): ?>
                                        • <span class="text-primary-500"><?php echo e($candidateEmail); ?></span>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div class="mt-20">
                                    <p class="mb-8 text-primary-light"><?php echo nl2br(e($msg)); ?></p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-20 text-center text-primary-light">
                        No notifications found.
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/norloxsolutionscrm.com/wowcrm/resources/views/notice/veiwdetails.blade.php ENDPATH**/ ?>