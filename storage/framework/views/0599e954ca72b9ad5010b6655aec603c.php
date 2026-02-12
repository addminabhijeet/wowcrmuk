<?php
    $title = 'Timer -> Settings';
    $role = auth()->user()->role ?? '';
    if ($role === 'admin') {
        $subTitle = 'Super Admin';
    } elseif ($role === 'operation') {
        $subTitle = 'Operation Manager';
    } else {
        $subTitle = 'role';
    }
?>

<?php $__env->startSection('content'); ?>
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-none border h-100">
                <div class="card-body p-24">
                    <h5 class="mb-16">Update Timer Settings</h5>

                    <?php if(session('success')): ?>
                        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                    <?php endif; ?>

                    
                    <form action="<?php echo e(route('timer.updateWorkDay')); ?>" method="POST" class="mb-24">
                        <?php echo csrf_field(); ?>
                        <div class="mb-16">
                            <label class="form-label fw-medium">Work Day Duration</label>
                            <?php
                                $workSeconds = old('work_day_seconds', $timersetting->work_day_seconds ?? 32400);
                                $hours = floor($workSeconds / 3600);
                                $minutes = floor(($workSeconds % 3600) / 60);
                            ?>
                            <div class="d-flex gap-2">
                                <select name="hours" class="form-select rounded-pill px-16 py-6">
                                    <?php for($h = 0; $h <= 24; $h++): ?>
                                        <option value="<?php echo e($h); ?>" <?php echo e($h == $hours ? 'selected' : ''); ?>>
                                            <?php echo e($h); ?> h</option>
                                    <?php endfor; ?>
                                </select>
                                <select name="minutes" class="form-select rounded-pill px-16 py-6">
                                    <?php for($m = 0; $m < 60; $m += 5): ?>
                                        <option value="<?php echo e($m); ?>" <?php echo e($m == $minutes ? 'selected' : ''); ?>>
                                            <?php echo e($m); ?> m</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <?php $__errorArgs = ['hours'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="text-danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php $__errorArgs = ['minutes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="text-danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-24 py-6">Update Work Day</button>
                        </div>
                    </form>

                    
                    <form action="<?php echo e(route('timer.updateBaseTime')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-16">
                            <label class="form-label fw-medium">Daily Base Time</label>
                            <input type="time" name="daily_base_time" class="form-control rounded-pill px-16 py-6"
                                value="<?php echo e(old('daily_base_time', $timersetting->daily_base_time ?? '20:00')); ?>" required>
                            <?php $__errorArgs = ['daily_base_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="text-danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-24 py-6">Update Base Time</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/norloxsolutionscrm.com/wowcrm/resources/views/timers/admin.blade.php ENDPATH**/ ?>