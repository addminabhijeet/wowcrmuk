<?php
$title = 'SMTP';
$subTitle = 'Settings - SMTP';
$script = '<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>';
?>

<?php $__env->startSection('content'); ?>

<div class="card p-4">
    <h4>Edit Email Template</h4>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('template.update', $template->id)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="mb-3">
            <label>Subject</label>
            <input type="text" name="subject" class="form-control" value="<?php echo e($template->subject); ?>">
        </div>

        <div class="mb-3">
            <label>Body</label>
            <textarea name="body" id="editor" class="form-control" rows="10"><?php echo e($template->body); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Template</button>
    </form>
</div>


<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('editor', {
        height: 250,
        removeButtons: 'PasteFromWord'
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u235777426/domains/admin.pdfreducer.com/public_html/resources/views/smtp/edittemplate.blade.php ENDPATH**/ ?>