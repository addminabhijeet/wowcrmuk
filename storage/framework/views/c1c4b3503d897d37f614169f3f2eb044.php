<?php if($data->isEmpty()): ?>
    <p class="text-muted">No data found. Fetch a Google Sheet first.</p>
<?php else: ?>
    <div class="table-responsive scroll-sm mb-2" id="top-scroll-wrapper"
        style="
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-gutter: stable;
        height: 20px;
    ">
        <div id="top-scroll" style="height: 1px;"></div>
    </div>
    <script>
        $(document).ready(function() {
            // Set top-scroll width equal to table width
            function syncTopScroll() {
                var tableWidth = $('#sheet-table')[0].scrollWidth;
                $('#top-scroll').width(tableWidth);
            }

            syncTopScroll(); // initial sync
            $(window).resize(syncTopScroll); // update on window resize

            // Scroll table when top-scroll is moved
            $('#top-scroll-wrapper').on('scroll', function() {
                $('.table-responsive.scroll-sm').scrollLeft($(this).scrollLeft());
            });

            // Scroll top-scroll when table is scrolled
            $('.table-responsive.scroll-sm').on('scroll', function() {
                $('#top-scroll-wrapper').scrollLeft($(this).scrollLeft());
            });
        });
    </script>

    <div class="table-responsive scroll-sm">

        <table class="table bordered-table sm-table mb-0">
            <thead>
                <tr>
                    <th scope="col" class="text-center">Row</th>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Name</th>
                    <th scope="col" class="text-center">Email Address</th>
                    <th scope="col" class="text-center">Phone Number</th>
                    <th scope="col" class="text-center">Location</th>

                    <th scope="col" class="text-center">Relocation</th>
                    <th scope="col" class="text-center">Graduation Date</th>
                    <th scope="col" class="text-center">Immigration</th>
                    <th scope="col" class="text-center">Course</th>
                    <th scope="col" class="text-center">Amount</th>
                    <th scope="col" class="text-center">Qualification</th>
                    <th scope="col" class="text-center">Time Zone</th>
                    <th scope="col" class="text-center">1st Follow Up Remarks</th>

                    <th scope="col" class="text-center">Forwarded By</th>
                    <th scope="col" class="text-center">Resume</th>
                    <th scope="col" class="text-center">Remark</th>
                    <th scope="col" class="text-center">Follow Up Remark</th>
                    <th scope="col" class="text-center">Status</th>
                    <?php if(auth()->guard()->check()): ?>
                        <?php if(auth()->user()->role !== 'operation'): ?>
                            <th scope="col" class="text-center">Actions</th>
                        <?php endif; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="sheet-table-body">
                <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr id="row-<?php echo e($row->id); ?>" data-id="<?php echo e($row->id); ?>">

                        <td><?php echo e($row->sheet_row_number); ?></td>

                        
                        <td>
                            <input type="text" class="form-control date-picker" data-key="Date"
                                value="<?php echo e($row->Date ? \Carbon\Carbon::parse($row->Date)->format('m/d/Y') : ''); ?>">
                        </td>

                        
                        <td>
                            <input type="text" class="form-control name-input" data-key="Name"
                                value="<?php echo e($row->Name ?? ''); ?>" placeholder="Name">
                        </td>

                        
                        <td>
                            <input type="email" class="form-control email-input" data-key="Email Address"
                                value="<?php echo e($row->Email_Address ?? ''); ?>" placeholder="E-mail">
                        </td>

                        
                        <td>
                            <input type="tel" class="form-control phone-input" data-key="Phone Number"
                                maxlength="14" value="<?php echo e($row->Phone_Number ?? ''); ?>" placeholder="US number">
                        </td>

                        
                        <td>
                            <input type="text" class="form-control location-autocomplete" data-key="Location"
                                value="<?php echo e($row->Location ?? ''); ?>" placeholder="Type location">
                        </td>



                        
                        <td>
                            <?php $relOptions = ['YES','NO']; ?>
                            <select class="form-select dynamic-dropdown" data-key="Relocation">
                                <option value="">-- Relocation --</option>
                                <?php $__currentLoopData = $relOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option); ?>"
                                        <?php echo e($row->Relocation === $option ? 'selected' : ''); ?>>
                                        <?php echo e($option); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </td>

                        
                        <td>
                            <input type="text" class="form-control date-picker" data-key="Graduation Date"
                                value="<?php echo e($row->Graduation_Date ? \Carbon\Carbon::parse($row->Graduation_Date)->format('m/d/Y') : ''); ?>">
                        </td>

                        
                        <td>
                            <?php $immOptions = ['F1 CPT','F1 OPT','STEM OPT','H1B','B2','B1','H4','H4 EAD', 'GC/PR','USC','L2S']; ?>
                            <select class="form-select dynamic-dropdown" data-key="Immigration">
                                <option value="">--Immigration --</option>
                                <?php $__currentLoopData = $immOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option); ?>"
                                        <?php echo e($row->Immigration === $option ? 'selected' : ''); ?>>
                                        <?php echo e($option); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </td>

                        
                        <td>
                            <?php $courseOptions = ['BA','SAS','JAVA','QA','SQL','PYTHON','DOT NET']; ?>
                            <select class="form-select dynamic-dropdown" data-key="Course">
                                <option value="">-- Course --</option>
                                <?php $__currentLoopData = $courseOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option); ?>"
                                        <?php echo e($row->Course === $option ? 'selected' : ''); ?>>
                                        <?php echo e($option); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </td>

                        
                        <td>
                            <input type="text" class="form-control amount-input" data-key="Amount"
                                value="<?php echo e($row->Amount !== null ? '$' . number_format($row->Amount, 2) : ''); ?>"
                                placeholder="Amount (469)">
                        </td>

                        
                        <td>
                            <?php
                                $qualificationOptions = [
                                    'Masters',
                                    'Masters of Science',
                                    'Bachelors',
                                    'PG',
                                    'MBA',
                                    'PG Diploma',
                                    'M.Tech',
                                    'B.Tech',
                                    'MA',
                                    'Associate Degree',
                                    'Aerospace Proj. Manag.',
                                ];
                            ?>

                            <select class="form-select dynamic-dropdown" data-key="Qualification">
                                <option value="">-- Qualification --</option>
                                <?php $__currentLoopData = $qualificationOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option); ?>"
                                        <?php echo e($row->Qualification === $option ? 'selected' : ''); ?>>
                                        <?php echo e($option); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </td>

                        
                        <td>
                            <?php $timezoneOptions = ['EST','CST','MST','PST']; ?>
                            <select class="form-select dynamic-dropdown" data-key="Time Zone">
                                <option value="">-- Time Zone --</option>
                                <?php $__currentLoopData = $timezoneOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option); ?>"
                                        <?php echo e($row->Time_Zone === $option ? 'selected' : ''); ?>>
                                        <?php echo e($option); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </td>

                        
                        <td>
                            <?php $followOptions = ['Interested','Doubt need Clarification','Money Issue','Not Interested','Don\'t Call']; ?>
                            <select class="form-select dynamic-dropdown" data-key="1st Follow Up Remarks">
                                <option value="">-- 1st Follow Up Remarks --</option>
                                <?php $__currentLoopData = $followOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option); ?>"
                                        <?php echo e($row->First_Follow_Up_Remarks === $option ? 'selected' : ''); ?>>
                                        <?php echo e($option); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </td>





                        
                        <td>
                            <input type="text" class="form-control forwardedBy-input" data-key="forwardedBy"
                                value="<?php echo e($row->forwarded_by ?? ''); ?>" placeholder="Forwarded By" readonly>
                        </td>

                        
                        <td>
                            <input type="file" accept=".pdf, .doc, .docx" class="d-none resume-input"
                                data-key="View">

                            <button type="button" class="btn btn-sm btn-info upload-btn">
                                <?php echo e(!empty($row->resume) ? 'Change File' : 'Upload'); ?>

                            </button>

                            <?php if(!empty($row->resume)): ?>
                                <a href="<?php echo e(url('dashboard/junior/google-sheet/view-resume/' . $row->id)); ?>"
                                    target="_blank" class="btn btn-sm btn-primary view-btn">View File</a>

                                <a href="<?php echo e(url('dashboard/junior/google-sheet/download-resume/' . $row->id)); ?>"
                                    class="btn btn-sm btn-secondary download-btn">Download</a>
                            <?php else: ?>
                                <a href="#" target="_blank" class="btn btn-sm btn-primary view-btn d-none">View
                                    File</a>
                                <a href="#" download
                                    class="btn btn-sm btn-secondary download-btn d-none">Download</a>
                            <?php endif; ?>
                        </td>

                        
                        <td>
                            <textarea type="text" name="Remark_hidden" class="form-control remark-autocomplete" placeholder="Type remark"
                                rows="6"><?php echo e($row->Remark ?? ''); ?></textarea>

                            <input type="hidden" name="Remark"
                                class="form-control remark-autocomplete remark-hidden" data-key="Remark"
                                value="<?php echo e($row->Remark ?? ''); ?>" placeholder="Type remark">
                        </td>

                        
                        <td>
                            <textarea class="form-control transferremark-autocomplete" rows="6" placeholder="Type remark"><?php echo e($row->TransferRemark ?? ''); ?></textarea>

                            <input type="hidden" name="TransferRemark" class="transferremark-hidden"
                                data-key="TransferRemark" value="<?php echo e($row->TransferRemark ?? ''); ?>">

                        </td>



                        
                        <td>
                            <?php $exeOptions = ['Called & Mailed','Ready To Pay']; ?>
                            <select class="form-select dynamic-dropdown" data-key="Exe Remarks">
                                <option value="">-- Status --</option>
                                <?php $__currentLoopData = $exeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option); ?>"
                                        <?php echo e($row->Exe_Remarks === $option ? 'selected' : ''); ?>>
                                        <?php echo e($option); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </td>

                        <?php if(auth()->guard()->check()): ?>
                            <?php if(auth()->user()->role !== 'operation'): ?>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-success save-btn" data-id="<?php echo e($row->id); ?>">
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                </td>
                            <?php endif; ?>
                        <?php endif; ?>
                    </tr>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const form = document.querySelector('form');
                            if (!form) return;

                            // Function to sync a textarea to its corresponding input
                            function syncTextareaToInput(textarea) {
                                const td = textarea.closest('td');
                                if (!td) return;

                                const textareaName = textarea.getAttribute('name');
                                if (!textareaName) return;

                                // Map _hidden textarea to input with same name minus _hidden
                                const inputName = textareaName.replace('_hidden', '');
                                const input = td.querySelector('input[name="' + inputName + '"]');
                                if (!input) return;

                                // Trim value before assigning
                                input.value = textarea.value.trim();
                            }

                            // 🔁 Real-time sync on input for all textareas with *_autocomplete class
                            document.querySelectorAll('textarea.remark-autocomplete, textarea.transferremark-autocomplete').forEach(
                                function(textarea) {
                                    textarea.addEventListener('input', function() {
                                        syncTextareaToInput(textarea);
                                    });
                                });

                            // 🛡️ Final sync before form submit
                            form.addEventListener('submit', function() {
                                document.querySelectorAll(
                                    'textarea.remark-autocomplete, textarea.transferremark-autocomplete').forEach(
                                    function(textarea) {
                                        syncTextareaToInput(textarea);
                                    });
                            });
                        });
                        $(document).ready(function() {
                            $('.save-btn').click(function() {
                                let rowId = $(this).data('id');
                                let $tr = $('#row-' + rowId);

                                // 🔁 Sync textarea values to hidden inputs BEFORE collecting data
                                $tr.find('textarea').each(function() {
                                    let $textarea = $(this);
                                    let $td = $textarea.closest('td');

                                    if ($textarea.hasClass('remark-autocomplete')) {
                                        $td.find('input[name="Remark"]').val($textarea.val().trim());
                                    }

                                    if ($textarea.hasClass('transferremark-autocomplete')) {
                                        $td.find('input[name="TransferRemark"]').val($textarea.val().trim());
                                    }
                                });

                                let data = {};

                                // ✅ Now safely collect data
                                $tr.find('input[data-key], select[data-key]').each(function() {
                                    let key = $(this).data('key');
                                    data[key] = $(this).val();
                                });

                                let formData = new FormData();
                                formData.append('id', rowId);
                                formData.append('data', JSON.stringify(data));

                                let fileInput = $tr.find('.resume-input')[0];
                                if (fileInput && fileInput.files.length > 0) {
                                    formData.append('resume', fileInput.files[0]);
                                }

                                $.ajax({
                                    url: '<?php echo e(route('seniorupdate')); ?>',
                                    type: 'POST',
                                    data: formData,
                                    contentType: false,
                                    processData: false,
                                    headers: {
                                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                                    },
                                    success: function(response) {
                                        alert(response.message);
                                    },
                                    error: function() {
                                        alert('AJAX error');
                                    }
                                });
                            });

                            // Show file input when clicking upload
                            $('.upload-btn').click(function() {
                                $(this).closest('td').find('input.resume-input').click();
                            });
                        });
                    </script>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
<?php endif; ?>
</div>

<?php if($data->hasPages()): ?>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
        <div>
            <?php echo e($data->links('pagination::bootstrap-5')); ?>

        </div>
    </div>
<?php endif; ?>
</div>
<?php /**PATH /var/www/norloxsolutionscrm.com/wowcrm/resources/views/database/partials/seniorfollow_table.blade.php ENDPATH**/ ?>