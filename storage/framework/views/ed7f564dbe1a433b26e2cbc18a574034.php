<?php
    $title='Dashboard';
    $subTitle = 'Accountant';
    $script= '<script src="' . asset('assets/js/homeOneChart.js') . '"></script>';
?>

<?php $__env->startSection('content'); ?>

            <div class="row row-cols-xxl-4 row-cols-md-4 row-cols-sm-2 row-cols-1 g-4">

    <!-- Total Target Given -->
    <div class="col">
        <div class="card h-100 border-0 shadow-sm"
            style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); border-radius: 20px; color: #0d47a1; transition: all 0.3s ease; cursor: pointer;"
            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.1)';"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <p class="mb-1 fw-semibold" style="font-size: 15px; opacity: 0.8;">Total Target Given</p>
                    <h3 class="mb-0 fw-bold" style="font-size: 36px;">$</h3>
                </div>
                <div class="d-flex justify-content-center align-items-center"
                    style="width: 70px; height: 70px; background-color: rgba(13,71,161,0.1); border-radius: 50%;">
                    <iconify-icon icon="mdi:bullseye-arrow" style="font-size: 34px; color: #0d47a1;"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Target Achieved -->
    <div class="col">
        <div class="card h-100 border-0 shadow-sm"
            style="background: linear-gradient(135deg, #f3e5f5, #e1bee7); border-radius: 20px; color: #6a1b9a; transition: all 0.3s ease; cursor: pointer;"
            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.1)';"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <p class="mb-1 fw-semibold" style="font-size: 15px; opacity: 0.8;">Total Target Achieved</p>
                    <h3 class="mb-0 fw-bold" style="font-size: 36px;">$</h3>
                </div>
                <div class="d-flex justify-content-center align-items-center"
                    style="width: 70px; height: 70px; background-color: rgba(106,27,154,0.1); border-radius: 50%;">
                    <iconify-icon icon="fa-solid:trophy" style="font-size: 34px; color: #6a1b9a;"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <!-- Target Yet to Achieve -->
    <div class="col">
        <div class="card h-100 border-0 shadow-sm"
            style="background: linear-gradient(135deg, #fff3e0, #ffe0b2); border-radius: 20px; color: #ef6c00; transition: all 0.3s ease; cursor: pointer;"
            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.1)';"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <p class="mb-1 fw-semibold" style="font-size: 15px; opacity: 0.8;">Target Yet to Achieve</p>
                    <h3 class="mb-0 fw-bold" style="font-size: 36px;">$</h3>
                </div>
                <div class="d-flex justify-content-center align-items-center"
                    style="width: 70px; height: 70px; background-color: rgba(239,108,0,0.1); border-radius: 50%;">
                    <iconify-icon icon="mdi:progress-clock" style="font-size: 34px; color: #ef6c00;"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <!-- Days Left -->
    <div class="col">
        <div class="card h-100 border-0 shadow-sm"
            style="background: linear-gradient(135deg, #e8f5e9, #c8e6c9); border-radius: 20px; color: #2e7d32; transition: all 0.3s ease; cursor: pointer;"
            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.1)';"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';">
            <div class="card-body d-flex justify-content-between align-items-center p-4">
                <div>
                    <p class="mb-1 fw-semibold" style="font-size: 15px; opacity: 0.8;">Days Left</p>
                    <h3 class="mb-0 fw-bold" style="font-size: 36px;">0</h3>
                </div>
                <div class="d-flex justify-content-center align-items-center"
                    style="width: 70px; height: 70px; background-color: rgba(46,125,50,0.1); border-radius: 50%;">
                    <iconify-icon icon="mdi:calendar-clock" style="font-size: 34px; color: #2e7d32;"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

</div>



<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/norloxsolutionscrm.com/wowcrm/resources/views/dashboard/accountant.blade.php ENDPATH**/ ?>