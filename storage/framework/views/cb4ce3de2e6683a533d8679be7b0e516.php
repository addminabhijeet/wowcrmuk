<?php
    $title = 'Report -> Trainer';
    $role = auth()->user()->role ?? '';
    if ($role === 'admin') {
        $subTitle = 'Super Admin';
    } elseif ($role === 'operation') {
        $subTitle = 'Operation Manager';
    } else {
        $subTitle = 'role';
    }
    $script = '<script>
        $(".remove-item-btn").on("click", function() {
            $(this).closest("tr").addClass("d-none")
        });
    </script>';
?>

<?php $__env->startSection('content'); ?>
    <div class="user-select-none">
        <div class="card h-100 p-0 radius-12">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <label for="selected_date" class="fw-semibold small mb-0">Select Date:</label>
                            <input type="date" id="selected_date" value="<?php echo e(request('selected_date', date('Y-m-d'))); ?>"
                                class="form-control form-control-sm">
                        </div>
                        <a href="javascript:void(0)" class="btn btn-primary btn-sm" id="multiReportBtn">
                            <i class="bi bi-people-fill me-1"></i>All Report
                        </a>


                    </div>
                </div>
            </div>

            <div class="card-body p-24">
                <div class="table-responsive scroll-sm">
                    <table class="table bordered-table sm-table mb-0 align-middle">
                        <thead>
                            <tr>

                                <th>S.L</th>
                                <th>Name</th>

                                <th>Role</th>
                                <th class="text-center" style="width:40px;">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="selectAllUsers">
                                    </div>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $juniorUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>

                                    <td><?php echo e($index + 1); ?></td>
                                    <td><?php echo e($user->name); ?></td>

                                    <td>
                                        <?php echo e($user->role === 'junior' ? 'IT Recruiter' : ucfirst($user->role)); ?>

                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input user-checkbox" type="checkbox" name="users[]"
                                                value="<?php echo e($user->id); ?>" data-role="<?php echo e($user->role); ?>">
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        document.getElementById('multiReportBtn').addEventListener('click', function() {

            const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked'))
                .map(cb => cb.value);

            if (selectedUsers.length === 0) {
                alert('Please select at least one user');
                return;
            }

            const selectedDate = document.getElementById('selected_date').value;

            let url = "<?php echo e(route('call.reports.allreport', ['userId' => '__USERS__'])); ?>";
            url = url.replace('__USERS__', selectedUsers.join(','));

            url += `?selected_date=${selectedDate}`;

            window.location.href = url;
        });
    </script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/norloxsolutionscrm.com/wowcrm/resources/views/reports/reportsender.blade.php ENDPATH**/ ?>