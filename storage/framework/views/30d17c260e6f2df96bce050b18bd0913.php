<?php
$title='Users -> Trainer';
$subTitle = 'Super Admin';
$script= '<script src="' . asset('assets/js/homeOneChart.js') . '"></script>';
?>

<?php $__env->startSection('content'); ?>

<div class="row gy-4">
    <div class="col-12">
        <div class="card h-100 p-0">
            <div class="card-body p-24">

                <div class="d-flex flex-wrap align-items-center gap-1 justify-content-between mb-16">
                    <ul class="nav border-gradient-tab nav-pills mb-0" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex align-items-center active" id="pills-to-do-list-tab" data-bs-toggle="pill" data-bs-target="#pills-to-do-list" type="button" role="tab" aria-controls="pills-to-do-list" aria-selected="true">
                                Active
                                <span class="text-sm fw-semibold py-6 px-12 bg-neutral-500 rounded-pill text-white line-height-1 ms-12 notification-alert">35</span>
                            </button>
                        </li>
                    </ul>
                    <a  href="<?php echo e(route('users.trainer.create')); ?>" class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                        Add New Trainer
                    </a>
                </div>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-to-do-list" role="tabpanel" aria-labelledby="pills-to-do-list-tab" tabindex="0">
                        <div class="table-responsive scroll-sm">
                            <table class="table bordered-table sm-table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Users</th>
                                        <th scope="col" class="text-center">Role</th>
                                        <th scope="col" class="text-center">Edit</th>
                                        <th scope="col">Created At</th>
                                        <th scope="col">Updated At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <!-- User info (avatar + name + email) -->
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo e(asset('assets/images/users/user1.png')); ?>"
                                                    alt="<?php echo e($user->name); ?>"
                                                    class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                                                <div class="flex-grow-1">
                                                    <h6 class="text-md mb-0 fw-medium"><?php echo e($user->name); ?></h6>
                                                    <span class="text-sm text-secondary-light fw-medium"><?php echo e($user->email); ?></span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Role -->
                                        <td class="text-center">
                                            <span class="bg-primary-focus text-primary-main px-24 py-4 rounded-pill fw-medium text-sm">
                                                <?php echo e(ucfirst($user->role)); ?>

                                            </span>
                                        </td>

                                        <!-- Edit -->
                                        <td class="text-center">
                                            <div class="d-flex align-items-center gap-10 justify-content-center">
                                                <a href="<?php echo e(route('users.trainer.edit', $user->id)); ?>" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle">
                                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                                </a>
                                                <a href="<?php echo e(route('users.trainer.destroy', $user->id)); ?>" class="remove-item-btn bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle">
                                                    <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>

                                        <!-- Created at -->
                                        <td>
                                            <span class="text-sm text-secondary-light fw-medium">
                                                <?php echo e($user->created_at ? $user->created_at->format('d M Y') : '-'); ?>

                                            </span>
                                        </td>

                                        <!-- Updated at -->
                                        <td>
                                            <span class="text-sm text-secondary-light fw-medium">
                                                <?php echo e($user->updated_at ? $user->updated_at->format('d M Y') : '-'); ?>

                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u235777426/domains/admin.pdfreducer.com/public_html/resources/views/user/trainer.blade.php ENDPATH**/ ?>