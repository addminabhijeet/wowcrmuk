<?php
$title = 'Target -> Edit';
$role = auth()->user()->role ?? '';
if($role === 'admin'){
    $subTitle = 'Super Admin';
} elseif ($role === 'operation') {
    $subTitle = 'Operation Manager';
} else{
    $subTitle = 'role';
}
?>

<?php $__env->startSection('content'); ?>

<div class="card h-100 p-0 radius-12">
    <div
        class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
        <div class="d-flex align-items-center flex-wrap gap-3">
            <span class="text-md fw-medium text-secondary-light mb-0">Show</span>
            <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px">
                <option>1</option>
            </select>

            
            <button type="button"
                class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#userModal" data-mode="add">
                <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                Add Target
            </button>
        </div>
    </div>

    <div class="card-body p-24">
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Target</th>
                        <th>Target Month</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $targets = $targetUsers->target ? explode(' | ', $targetUsers->target) : [];
                        $targetDates = $targetUsers->target_date ? explode(' | ', $targetUsers->target_date) : [];
                    ?>

                    <?php $__currentLoopData = $targets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $target): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($targetUsers->name); ?></td>
                        <td><?php echo e($target); ?></td>
                        <td><?php echo e($targetDates[$index] ?? '-'); ?></td>
                        <td class="text-center d-flex gap-2 justify-content-center">

                            
                            <button type="button"
                                class="btn btn-sm btn-warning d-flex align-items-center gap-1"
                                data-bs-toggle="modal"
                                data-bs-target="#userModal"
                                data-mode="edit"
                                data-id="<?php echo e($targetUsers->id); ?>"
                                data-index="<?php echo e($index); ?>"
                                data-target="<?php echo e($target); ?>"
                                data-target_date="<?php echo e($targetDates[$index] ?? ''); ?>">
                                <iconify-icon icon="mdi:pencil" class="text-lg"></iconify-icon>
                                Edit
                            </button>

                            
                            <form action="<?php echo e(route('target.delete', $targetUsers->id)); ?>" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this target month?')">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="index" value="<?php echo e($index); ?>">
                                <button type="submit" class="btn btn-sm btn-danger d-flex align-items-center gap-1">
                                    <iconify-icon icon="mdi:trash-can" class="text-lg"></iconify-icon>
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content radius-16 bg-base">
            <div class="modal-header py-16 px-24 border-bottom">
                <h1 class="modal-title fs-5" id="userModalLabel">Add/Edit Target</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="userForm" method="POST" action="<?php echo e(route('target.save', $targetUsers->id)); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" id="user_id" value="<?php echo e($targetUsers->id); ?>">
                <input type="hidden" name="index" id="user_index">

                <div class="modal-body p-24">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target</label>
                            <input type="number" name="target" id="user_target" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Month</label>
                            <input type="month" name="target_date" id="user_target_date" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const userModal = document.getElementById("userModal");
    const form = document.getElementById("userForm");
    const modalTitle = document.getElementById("userModalLabel");

    userModal.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;
        const mode = button.getAttribute("data-mode");

        if (mode === "edit") {
            modalTitle.textContent = "Edit Target";
            form.querySelector("#user_index").value = button.dataset.index;
            form.querySelector("#user_target").value = button.dataset.target;
            form.querySelector("#user_target_date").value = button.dataset.target_date;
        } else {
            modalTitle.textContent = "Add Target";
            form.reset();
            form.querySelector("#user_index").value = "";
        }
    });
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/norloxsolutionscrm.com/wowcrm/resources/views/target/edit.blade.php ENDPATH**/ ?>