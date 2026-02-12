<?php
    $title = 'Database';
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

    <div class="card radius-12 mb-3">
        <div class="card-header border-bottom bg-base py-16 px-24">

            <div class="table-responsive w-100">
                <table class="table table-sm table-bordered mb-0 text-center align-middle w-100">
                    <thead>
                        <tr>
                            <th class="text-nowrap text-center text-primary">Total Calls</th>
                            <th class="text-nowrap text-center text-danger">Not Interested</th>
                            <th class="text-nowrap text-center text-warning">Voice Mail</th>
                            <th class="text-nowrap text-center text-dark">Busy</th>
                            <th class="text-nowrap text-center text-secondary">Others</th>
                            <th class="text-nowrap text-center text-info">Interested</th>
                            <th class="text-nowrap text-center text-success">Called & Mailed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold text-primary">
                                <?php echo e($exeRemarkCounts['total_calls'] ?? 0); ?>

                            </td>
                            <td class="fw-semibold text-danger">
                                <?php echo e($exeRemarkCounts['not_interested'] ?? 0); ?>

                            </td>
                            <td class="fw-semibold text-warning">
                                <?php echo e($exeRemarkCounts['vm'] ?? 0); ?>

                            </td>
                            <td class="fw-semibold text-dark">
                                <?php echo e($exeRemarkCounts['busy'] ?? 0); ?>

                            </td>
                            <td class="fw-semibold text-secondary">
                                <?php echo e($exeRemarkCounts['others'] ?? 0); ?>

                            </td>
                            <td class="fw-semibold text-info">
                                <?php echo e($exeRemarkCounts['interested'] ?? 0); ?>

                            </td>
                            <td class="fw-semibold text-success">
                                <?php echo e($exeRemarkCounts['called_and_mailed'] ?? 0); ?>

                            </td>
                        </tr>
                    </tbody>

                </table>
            </div>

        </div>
    </div>


    <div class="card h-100 p-0 radius-12">
        <div
            class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
            <div class="d-flex align-items-center flex-wrap gap-3">

                <form class="navbar-search position-relative d-flex gap-2" autocomplete="off">
                    <input type="text" id="senior-search" class="bg-base h-40-px w-auto form-control"
                        placeholder="Search Name, Email, Phone">

                    <input type="date" id="date-filter" class="bg-base h-40-px w-auto form-control"
                        title="Filter by Date">

                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>

                    <div id="search-suggestions" class="list-group position-absolute w-100" style="z-index:1000;"></div>
                </form>

            </div>
        </div>


        <div class="card-body p-24" id="senior-table-wrapper">
            <!-- Extra Scroll Bar Above -->
            <!-- Extra Scroll Bar Above -->
            <div class="table-responsive scroll-sm mb-2" id="top-scroll-wrapper">
                <div id="top-scroll"></div>
            </div>

            <!-- Main Table Scroll -->
            <div class="table-responsive scroll-sm" id="bottom-scroll-wrapper">


                <?php if($data->isEmpty()): ?>
                    <p class="text-muted">No data found. Fetch a Google Sheet first.</p>
                <?php else: ?>
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

                                <th scope="col" class="text-center">1st Follow Up Remarks</th>
                                <th scope="col" class="text-center">Time Zone</th>
                                <th scope="col" class="text-center">Resume</th>
                                <th scope="col" class="text-center">Remarks</th>
                                <th scope="col" class="text-center">Rejected Remark</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-center">Actions</th>

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
                                            maxlength="14" value="<?php echo e($row->Phone_Number ?? ''); ?>"
                                            placeholder="US number">
                                    </td>

                                    
                                    <td>
                                        <input type="text" class="form-control location-autocomplete"
                                            data-key="Location" value="<?php echo e($row->Location ?? ''); ?>"
                                            placeholder="Type location">
                                    </td>




                                    
                                    <td>
                                        <?php $relOptions = ['YES','NO','']; ?>
                                        <select class="form-select dynamic-dropdown" data-key="Relocation">
                                            <option value="">-- Select --</option>
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
                                        <?php $immOptions = ['F1 CPT','F1 OPT','STEM OPT','H1B','B2','B1','H4','H4 EAD', 'GC/PR','USC','L2S','']; ?>
                                        <select class="form-select dynamic-dropdown" data-key="Immigration">
                                            <option value="">-- Select --</option>
                                            <?php $__currentLoopData = $immOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option); ?>"
                                                    <?php echo e($row->Immigration === $option ? 'selected' : ''); ?>>
                                                    <?php echo e($option); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </td>

                                    
                                    <td>
                                        <?php $courseOptions = ['BA','SAS','JAVA','QA','SQL','PYTHON','DOT NET','']; ?>
                                        <select class="form-select dynamic-dropdown" data-key="Course">
                                            <option value="">-- Select --</option>
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
                                            value="<?php echo e($row->Amount ? '$' . number_format($row->Amount, 2) : ''); ?>"
                                            placeholder="Amount(469)">
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
                                                '',
                                            ];
                                        ?>

                                        <select class="form-select dynamic-dropdown" data-key="Qualification">
                                            <option value="">-- Select --</option>
                                            <?php $__currentLoopData = $qualificationOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option); ?>"
                                                    <?php echo e($row->Qualification === $option ? 'selected' : ''); ?>>
                                                    <?php echo e($option); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </td>




                                    
                                    <td>
                                        <?php $followOptions = ['Interested','Doubt need Clarification','Money Issue','Not Interested','Don\'t Call','']; ?>
                                        <select class="form-select dynamic-dropdown" data-key="1st Follow Up Remarks">
                                            <option value="">-- Select --</option>
                                            <?php $__currentLoopData = $followOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option); ?>"
                                                    <?php echo e($row->First_Follow_Up_Remarks === $option ? 'selected' : ''); ?>>
                                                    <?php echo e($option); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </td>

                                    
                                    <td>
                                        <?php $timezoneOptions = ['EST','CST','MST','PST','']; ?>
                                        <select class="form-select dynamic-dropdown" data-key="Time Zone">
                                            <option value="">-- Select --</option>
                                            <?php $__currentLoopData = $timezoneOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option); ?>"
                                                    <?php echo e($row->Time_Zone === $option ? 'selected' : ''); ?>>
                                                    <?php echo e($option); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
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
                                            <a href="#" target="_blank"
                                                class="btn btn-sm btn-primary view-btn d-none">View File</a>
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
                                        <textarea type="text" name="RejectedRemark_hidden" class="form-control rejectedremark-autocomplete data-field"
                                            data-key="RejectedRemark" placeholder="Type rejected remark" rows="6"><?php echo e($row->RejectedRemark ?? ''); ?></textarea>

                                        <input type="hidden" name="RejectedRemark"
                                            class="form-control rejectedremark-autocomplete rejectedremark-hidden"
                                            data-key="RejectedRemark" value="<?php echo e($row->RejectedRemark ?? ''); ?>"
                                            placeholder="Type rejected remark">
                                    </td>


                                    
                                    <td>
                                        <?php $exeOptions = ['Called & Mailed','Not Interested','Interested','Others','VM','Busy']; ?>
                                        <select class="form-select dynamic-dropdown" data-key="Exe Remarks">
                                            <option value="">-- Select Status--</option>
                                            <?php $__currentLoopData = $exeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option); ?>"
                                                    <?php echo e($row->Exe_Remarks === $option ? 'selected' : ''); ?>>
                                                    <?php echo e($option); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </td>


                                    <td class="text-center">

                                        <button class="btn btn-sm btn-success save-btn" data-id="<?php echo e($row->id); ?>">
                                            <i class="fas fa-save"></i> Save
                                        </button>


                                    </td>

                                </tr>
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
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .input-hint {
            font-size: .85rem;
            color: #6c757d;
        }

        .pagination {
            margin-left: 280px;
            /* adjust as needed */
        }


        select.dynamic-dropdown {
            min-width: 160px;
        }

        input.valid {
            background-color: #d4edda;
        }

        input.invalid {
            background-color: #f8d7da;
        }

        input.neutral {
            background-color: #ffffff;
        }

        select.neutral {
            background-color: #ffffff;
        }

        select.valid {
            background-color: #d4edda;
        }

        .phone-hint {
            font-size: .8rem;
            color: #6c757d;
            margin-top: 3px;
            display: block;
        }

        .small-hint {
            font-size: .8rem;
            color: #6c757d;
            display: block;
            margin-top: 2px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tableBody = document.getElementById("sheet-table-body");


            const exeColors = {
                'Called & Mailed': '#d4edda',
                'Ready To Pay': '#d4edda',
                'Not Connected': '#f8d7da',
                'Did Not Pickup': '#d4edda',
                'Not Interested': '#f8d7da',
                'Others': '#d1ecf1',
                'N/A': '#e2e3e5',
                'VM': '#fff3cd',
                'Busy': '#cce5ff'
            };
            const immColors = {
                'F1 CPT': '#d1ecf1',
                'F1 OPT': '#cce5ff',
                'STEM OPT': '#d4edda',
                'H1B': '#fff3cd',
                'B2': '#e2e3e5',
                'B1': '#f8d7da',
                'H4': '#ffe5b4',
                'H4 EAD': '#e6ccff',
                'GC/PR': '#d0f0c0',
                'USC': '#f5c6cb'
            };

            const relColors = {
                'YES': '#d4edda',
                'NO': '#f8d7da'
            };
            const followColors = {
                'Interested': '#d4edda',
                'Doubt need Clarification': '#fff3cd',
                'Money Issue': '#f8d7da',
                'Not Interested': '#f8d7da',
                "Don't Call": '#e2e3e5'
            };
            const courseColors = {
                'BA': '#e2f0d9',
                'SAS': '#d1ecf1',
                'JAVA': '#cce5ff',
                'QA': '#fff3cd',
                'SQL': '#fbe7d0',
                'PYTHON': '#d4edda',
                'DOT NET': '#f8d7da'
            };
            const timezoneColors = {
                'EST': '#e2f0d9',
                'CST': '#d1ecf1',
                'MST': '#cce5ff',
                'PST': '#fff3cd'
            };
            const qualificationColors = {
                'Masters': '#e2f0d9',
                'Masters of Science': '#cce5ff',
                'Bachelors': '#e2f0d9',
                'PG': '#cce5ff',
                'MBA': '#e2f0d9',
                'PG Diploma': '#e2f0d9',
                'M.Tech': '#cce5ff',
                'B.Tech': '#e2f0d9',
                'MA': '#e2f0d9',
                'Associate Degree': '#cce5ff',
                'Aerospace Proj. Manag.': '#e2f0d9',
            };
            const dateColor = "#e0f7fa";
            const amountColors = "#e0f7fa";

            function updateSelectColor(select) {
                const val = select.value;
                const key = select.dataset.key;
                let color = '#ffffff';
                if (key === 'Exe Remarks') color = exeColors[val] || color;
                else if (key === 'Immigration') color = immColors[val] || color;
                else if (key === 'Relocation') color = relColors[val] || color;
                else if (key === '1st Follow Up Remarks') color = followColors[val] || color;
                else if (key === 'Course') color = courseColors[val] || color;
                else if (key === 'Time Zone') color = timezoneColors[val] || color;
                else if (key === 'Qualification') color = qualificationColors[val] || color;
                select.style.backgroundColor = color;
            }

            function formatPhoneNumber(value) {
                const digits = value.replace(/\D/g, "").slice(0, 10);
                const part1 = digits.slice(0, 3),
                    part2 = digits.slice(3, 6),
                    part3 = digits.slice(6, 10);
                if (digits.length > 6) return `${part1}-${part2}-${part3}`;
                if (digits.length > 3) return `${part1}-${part2}`;
                if (digits.length > 0) return part1;
                return "";
            }

            function validatePhoneInput(inp) {
                const v = inp.value.replace(/\D/g, "");
                if (v.length === 10) {
                    inp.classList.remove("invalid");
                    inp.classList.add("valid");
                } else if (v.length === 0) {
                    inp.classList.remove("invalid");
                    inp.classList.remove("valid");
                    inp.classList.add("neutral");
                } else {
                    inp.classList.add("invalid");
                    inp.classList.remove("valid");
                }
            }

            function validateEmailInput(inp) {
                const v = inp.value;
                const lower = v === v.toLowerCase();
                const ok = /^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/.test(v) && lower;
                if (ok) {
                    inp.classList.remove('invalid');
                    inp.classList.add('valid');
                } else if (v.length === 0) {
                    inp.classList.remove('invalid');
                    inp.classList.remove('valid');
                    inp.classList.add('neutral');
                } else {
                    inp.classList.add('invalid');
                    inp.classList.remove('valid');
                }
            }

            function validateNameInput(inp) {
                const v = inp.value;
                const ok = /^[a-zA-Z\s]+$/.test(v) && v.length > 0;
                if (ok) {
                    inp.classList.remove('invalid');
                    inp.classList.add('valid');
                } else if (v.length === 0) {
                    inp.classList.remove('invalid');
                    inp.classList.remove('valid');
                    inp.classList.add('neutral');
                } else {
                    inp.classList.add('invalid');
                    inp.classList.remove('valid');
                }
            }

            function validateAmountInput(inp) {
                let v = inp.value.trim();

                // Remove everything except digits and a single dot
                let clean = '';
                let dotFound = false;
                for (let char of v) {
                    if (char >= '0' && char <= '9') {
                        clean += char;
                    } else if (char === '.' && !dotFound) {
                        clean += '.';
                        dotFound = true;
                    }
                }

                // Prepend $ if not already
                if (clean !== '' && !clean.startsWith('$')) {
                    clean = '$' + clean;
                }

                inp.value = clean;

                // Apply CSS classes
                const numericPart = clean.startsWith('$') ? clean.slice(1) : clean;
                if (numericPart === '') {
                    inp.classList.remove('invalid', 'valid');
                    inp.classList.add('neutral');
                } else if (!isNaN(Number(numericPart))) {
                    inp.classList.remove('invalid', 'neutral');
                    inp.classList.add('valid');
                } else {
                    inp.classList.add('invalid');
                    inp.classList.remove('valid', 'neutral');
                }
            }

            function initDatePickers(context = document) {
                const laravelToday =
                    "<?php echo e(\Carbon\Carbon::now('America/New_York')->format('m/d/Y')); ?>"; // 🕒 Server-side today

                context.querySelectorAll('input.date-picker').forEach(input => {
                    const key = input.dataset.key;
                    const opts = {
                        dateFormat: "m/d/Y",
                        allowInput: true,
                        onChange: function(selectedDates, dateStr) {
                            input.style.backgroundColor = dateStr ? dateColor : '#fff';
                        },
                        onReady: function(selectedDates, dateStr) {
                            if (input.value) input.style.backgroundColor = dateColor;
                        }
                    };

                    // ✅ Use Laravel's timezone-based today
                    if (key === "Date") opts.maxDate = laravelToday;
                    if (key === "Date") opts.minDate = laravelToday;

                    flatpickr(input, opts);

                    input.addEventListener('blur', function() {
                        if (input.value && !/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(input.value)) {
                            input.style.backgroundColor = '#fff';
                        }
                    });
                });
            }


            function initLocationAutocomplete(context = document) {
                $(context).find('input.location-autocomplete').each(function() {
                    const $input = $(this);

                    function applyCss(value) {
                        if (!value) {
                            $input.removeClass('valid invalid').addClass('neutral');
                        } else {
                            $input.removeClass('invalid neutral').addClass('valid');
                        }
                    }

                    // Initial state
                    applyCss($input.val());

                    $input.on('input', function() {
                        const q = $(this).val().trim();
                        applyCss(q);

                        if (q.length < 2) {
                            $('#loc-suggestions').remove();
                            return;
                        }

                        const key = 'pk.e91481c6e5f0a93703159ae988e641a0';
                        $.getJSON(
                                `https://us1.locationiq.com/v1/autocomplete.php?key=${key}&q=${encodeURIComponent(q)}&limit=5&dedupe=1&normalizecity=1&accept-language=en`
                            )
                            .done(function(results) {
                                $('#loc-suggestions').remove();
                                const $list = $(
                                    '<div id="loc-suggestions" class="list-group" style="position:absolute; z-index:9999; max-height:200px; overflow:auto;"></div>'
                                );

                                results.forEach(r => {
                                    const addr = r.address || {};
                                    const city = addr.city || addr.town || addr
                                        .village || '';
                                    const state = addr.state || addr.region || '';
                                    const country = addr.country || '';
                                    const display = [city, state, country].filter(
                                        Boolean).join(', ');

                                    const item = $(
                                        '<a href="#" class="list-group-item list-group-item-action"></a>'
                                    ).text(display || r.display_name);
                                    item.on('click', function(e) {
                                        e.preventDefault();
                                        $input.val(display || r.display_name);
                                        applyCss(display || r
                                            .display_name); // Apply valid class
                                        $input.css('background-color',
                                            '#d4edda'); // optional highlight
                                        $('#loc-suggestions').remove();
                                    });
                                    $list.append(item);
                                });

                                $('body').append($list);
                                const offset = $input.offset();
                                $list.css({
                                    top: offset.top + $input.outerHeight(),
                                    left: offset.left,
                                    width: $input.outerWidth()
                                });
                            })
                            .fail(function() {
                                $('#loc-suggestions').remove();
                            });
                    });

                    $input.on('blur', function() {
                        setTimeout(() => $('#loc-suggestions').remove(), 200);
                    });
                });
            }

            function applyInitialState(context = document) {
                context.querySelectorAll('select.dynamic-dropdown').forEach(s => updateSelectColor(s));
                initDatePickers(context);
                initLocationAutocomplete(context);

                context.querySelectorAll('input.amount-input').forEach(i => {
                    validateAmountInput(i);
                    i.addEventListener('input', () => validateAmountInput(i));
                });

                context.querySelectorAll("input.phone-input").forEach(i => {
                    i.value = formatPhoneNumber(i.value);
                    validatePhoneInput(i);
                    i.addEventListener("input", () => {
                        i.value = formatPhoneNumber(i.value);
                        validatePhoneInput(i);
                    });
                });

                context.querySelectorAll('input.email-input').forEach(i => {
                    validateEmailInput(i);
                    i.addEventListener('input', () => {
                        let value = i.value.toLowerCase();

                        // allow only a-z 0-9 _ . - before @
                        // allow only a-z 0-9 . after @
                        value = value.replace(/[^a-z0-9@._-]/g, '');

                        // allow only one @
                        const parts = value.split('@');
                        if (parts.length > 2) {
                            value = parts[0] + '@' + parts.slice(1).join('');
                        }

                        if (parts.length === 2) {
                            // remove hyphen after @
                            parts[1] = parts[1].replace(/-/g, '');

                            // allow only one dot after @
                            const domainParts = parts[1].split('.');
                            if (domainParts.length > 2) {
                                parts[1] = domainParts[0] + '.' + domainParts[1];
                            }

                            value = parts[0] + '@' + parts[1];
                        }

                        i.value = value;
                        validateEmailInput(i);
                    });
                });

                context.querySelectorAll('input.name-input').forEach(i => {
                    validateNameInput(i);
                    i.addEventListener('input', () => {
                        i.value = i.value.toLowerCase().replace(/[^a-zA-Z\s]/g, '');
                        validateNameInput(i);
                    });
                });
            }


            function addBlankRow() {
                let colKeys = [];
                let firstRow = tableBody.querySelector("tr");
                if (firstRow) {
                    firstRow.querySelectorAll("input[data-key], select[data-key]").forEach(cell => colKeys.push(cell
                        .dataset.key));
                }

                let newRow = document.createElement("tr");
                newRow.setAttribute("data-id", "new");
                let cells = `<td>—</td>`;

                colKeys.forEach(k => {
                    //             if (['Exe Remarks', 'Immigration', 'Relocation', '1st Follow Up Remarks', 'Course',
                    //                     'Time Zone', 'Qualification'
                    //                 ].includes(k)) {
                    //                 let opts = [];
                    //                 if (k === 'Qualification') opts = ['Masters', 'Masters of Science', 'Bachelors',
                    //                     'PG', 'MBA', 'PG Diploma', 'M.Tech', 'B.Tech', 'MA', 'Associate Degree',
                    //                     'Aerospace Proj. Manag.'
                    //                 ];
                    //                 if (k === 'Exe Remarks') opts = ['Called & Mailed', 'Not Interested',
                    //                     'Interested', 'Others', 'VM', 'Busy'
                    //                 ];
                    //                 if (k === 'Immigration') opts = ['F1 CPT', 'F1 OPT', 'STEM OPT', 'H1B', 'B2', 'B1',
                    //                     'H4', 'H4 EAD', 'GC/PR', 'GC EAD', 'USC', 'L2S'
                    //                 ];
                    //                 if (k === 'Relocation') opts = ['YES', 'NO'];
                    //                 if (k === '1st Follow Up Remarks') opts = ['Interested', 'Doubt need Clarification',
                    //                     'Money Issue', 'Not Interested', "Don't Call"
                    //                 ];
                    //                 if (k === 'Course') opts = ['BA', 'SAS', 'JAVA', 'QA', 'SQL', 'PYTHON', 'DOT NET'];
                    //                 if (k === 'Time Zone') opts = ['EST', 'CST', 'MST', 'PST'];
                    //                 cells +=
                    //                     `<td><select class="form-select dynamic-dropdown" data-key="${k}"><option value="" disabled selected>-- Select ${k} --</option>${opts.map(o=>`<option value="${o}">${o}</option>`).join('')}</select></td>`;
                    //             } else if (k === 'Amount') {
                    //                 cells +=
                    //                     `<td><input type="text" class="form-control amount-input" data-key="${k}" placeholder="Amount(469)"></td>`;
                    //             } else if (k === 'Location') {
                    //                 cells +=
                    //                     `<td><input type="text" class="form-control location-autocomplete" data-key="${k}" placeholder="Location"><span class="small-hint"></span></td>`;
                    //             } else if (k === 'Remark') {
                    //                 cells +=
                    //                     `<td><input type="text" class="form-control Remark-autocomplete" data-key="${k}" placeholder="Remark"><span class="small-hint"></span></td>`;
                    //             } else if (k === 'Date' || k === 'Graduation Date') {
                    //                 cells +=
                    //                     `<td><input type="text" class="form-control date-picker" data-key="${k}" placeholder="${k} (MM/DD/YYYY)"><span class="small-hint"></span></td>`;
                    //             } else if (k === 'Phone Number') {
                    //                 cells +=
                    //                     `<td><input type="tel" class="form-control phone-input" data-key="${k}" maxlength="12" placeholder="US number"><span class="phone-hint"></span></td>`;
                    //             } else if (k === 'Email Address') {
                    //                 cells +=
                    //                     `<td><input type="email" class="form-control email-input" data-key="${k}" placeholder="Email"><span class="small-hint"></span></td>`;
                    //             } else if (k === 'Name') {
                    //                 cells +=
                    //                     `<td><input type="text" class="form-control name-input" data-key="${k}" placeholder="Name"><span class="small-hint"></span></td>`;
                    //             } else if (k === 'View') {
                    //                 cells += `<td>
                //     <input type="file" accept=".pdf, .doc, .docx" class="d-none resume-input" data-key="View">
                //     <button type="button" class="btn btn-sm btn-info upload-btn">Upload</button>
                //     <a href="#" target="_blank" class="btn btn-sm btn-primary view-btn d-none">View File</a>
                //     <a href="#" download class="btn btn-sm btn-secondary download-btn d-none">Download</a>
                // </td>`;
                    //             }

                });

                cells +=
                    // `<td><button class="btn btn-sm btn-success save-btn" data-id="new"><i class="fas fa-save"></i> Save</button></td>`;
                    newRow.innerHTML = cells;
                tableBody.appendChild(newRow);
                applyInitialState(newRow);
            }

            // Check if we need to add a blank row on page load
            // Only add if there are no existing "new" rows
            const hasNewRow = tableBody.querySelector('tr[data-id="new"]');
            const hasAnyRows = tableBody.querySelector('tr');

            if (!hasNewRow && !hasAnyRows) {
                // No rows at all - add one blank row
                addBlankRow();
            } else if (!hasNewRow && hasAnyRows) {
                // Has existing rows but no "new" row - add blank row at the end
                addBlankRow();
            }
            // If hasNewRow exists, we don't need to add another one

            // Handle select color changes
            tableBody.addEventListener('change', function(e) {
                if (e.target.matches('select.dynamic-dropdown')) updateSelectColor(e.target);
            });

            // Event delegation for save buttons (handles both existing and dynamically added buttons)
            tableBody.addEventListener('click', function(e) {
                if (e.target.matches('.save-btn') || e.target.closest('.save-btn')) {
                    e.preventDefault();
                    let saveBtn = e.target.matches('.save-btn') ? e.target : e.target.closest('.save-btn');
                    let id = saveBtn.dataset.id;
                    let row = saveBtn.closest("tr");
                    console.log("Saving row with id:", id);

                    // Collect all data from the row
                    let rowData = {};
                    row.querySelectorAll("input[data-key], select[data-key]").forEach(cell => {
                        let key = cell.dataset.key;
                        let value = cell.value;
                        rowData[key] = value;
                    });
                    console.log("Row data:", rowData);

                    // Create FormData object
                    let formData = new FormData();
                    formData.append("data", JSON.stringify(rowData));
                    formData.append("_token", "<?php echo e(csrf_token()); ?>");

                    // Handle resume file upload
                    let resumeInput = row.querySelector("input.resume-input");
                    if (resumeInput && resumeInput.files.length > 0) {
                        formData.append("resume", resumeInput.files[0]);
                    }

                    // Determine URL and method
                    let url, method;
                    if (id === "new") {
                        url = "<?php echo e(route('juniorstore')); ?>";
                        method = "POST";
                    } else {
                        url = "<?php echo e(route('juniorupdaterejected')); ?>";
                        method = "POST";
                        formData.append("id", id);
                    }

                    console.log("Sending to:", url, "Method:", method);

                    // Send the request
                    fetch(url, {
                            method: method,
                            body: formData
                        })
                        .then(res => {
                            if (!res.ok) {
                                throw new Error(`HTTP error! status: ${res.status}`);
                            }
                            return res.json();
                        })
                        // In the save button click event handler, update the success callback:
                        .then(data => {
                            console.log("Response from server:", data);
                            if (data.success) {
                                alert("Saved successfully");
                                setTimeout(() => {
                                    location.reload();
                                }, 1000);
                                if (id === "new") {
                                    // Update row with new ID
                                    row.dataset.id = data.id;
                                    saveBtn.dataset.id = data.id;
                                    row.querySelector("td:first-child").innerText = data
                                        .sheet_row_number;

                                    const viewBtn = row.querySelector('.view-btn');
                                    const downloadBtn = row.querySelector('.download-btn');

                                    if (viewBtn && data.resume_path) {
                                        viewBtn.href =
                                            `/dashboard/junior/google-sheet/view-resume/${data.id}`;
                                        viewBtn.classList.remove('d-none');
                                    }

                                    if (downloadBtn && data.resume_path) {
                                        downloadBtn.href =
                                            `/dashboard/junior/google-sheet/download-resume/${data.id}`;
                                        downloadBtn.classList.remove('d-none');
                                    }

                                    // Only add new blank row if none exists
                                    const existingNewRows = tableBody.querySelectorAll(
                                        'tr[data-id="new"]');
                                    if (existingNewRows.length === 0) {
                                        addBlankRow();
                                    }
                                }

                            } else {
                                console.error("Server error:", data.message);
                                alert("Error: " + (data.message || "Unknown error"));
                            }
                        })
                        .catch(err => {
                            console.error("Fetch error:", err);
                            alert("Save failed. Check console for details.");
                        });
                }
            });

            // Handle file upload button clicks
            tableBody.addEventListener('click', function(e) {
                if (e.target.matches('.upload-btn')) {
                    const row = e.target.closest('tr');
                    const fileInput = row.querySelector('.resume-input');
                    fileInput.click();
                }

                // Handle view and download buttons for unsaved rows
                if (e.target.matches('.view-btn') || e.target.matches('.download-btn')) {
                    const row = e.target.closest('tr');
                    const id = row.dataset.id;

                    if (id === "new") {
                        e.preventDefault();
                        alert("Please save the row first before viewing/downloading the resume.");
                        return;
                    }
                }
            });

            // Handle file selection
            tableBody.addEventListener('change', function(e) {
                if (e.target.matches('.resume-input')) {
                    const row = e.target.closest('tr');
                    const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';

                    // Show view and download buttons temporarily
                    const viewBtn = row.querySelector('.view-btn');
                    const downloadBtn = row.querySelector('.download-btn');

                    if (viewBtn) viewBtn.classList.remove('d-none');
                    if (downloadBtn) downloadBtn.classList.remove('d-none');

                    // Update button text
                    const uploadBtn = row.querySelector('.upload-btn');
                    if (uploadBtn) uploadBtn.textContent = 'Change File';

                    console.log('File selected:', fileName);
                }
            });

            // Apply initial state to all existing rows
            applyInitialState(document);

            // Cleanup location suggestions
            document.addEventListener('click', function(e) {
                if (!$(e.target).closest('#loc-suggestions, .location-autocomplete').length)
                    $('#loc-suggestions').remove();
            });

            // Real-time validation
            tableBody.addEventListener('input', function(e) {
                if (e.target.matches('input.phone-input')) validatePhoneInput(e.target);
                if (e.target.matches('input.email-input')) {
                    e.target.value = e.target.value.toLowerCase();
                    validateEmailInput(e.target);
                }
                if (e.target.matches('input.name-input')) {
                    let v = e.target.value.replace(/[^a-zA-Z\s]/g, '');
                    v = v.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                    e.target.value = v;
                    validateNameInput(e.target);
                }
            });
        });
    </script>

    <style>
        .input-hint {
            font-size: .85rem;
            color: #6c757d;
        }

        select.dynamic-dropdown {
            min-width: 160px;
        }

        input.valid {
            background-color: #d4edda;
        }

        input.invalid {
            background-color: #f8d7da;
        }

        input.neutral {
            background-color: #ffffff;
        }

        select.neutral {
            background-color: #ffffff;
        }

        select.valid {
            background-color: #d4edda;
        }

        .phone-hint,
        .small-hint {
            font-size: .8rem;
            color: #6c757d;
            display: block;
            margin-top: 2px;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        $(document).ready(function() {

            let activeRequest = null;

            function debounce(func, wait) {
                let timeout;
                return function() {
                    const context = this,
                        args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }

            function fetchTable(search = '', page = 1, junior_user = '', row_id = '', date = '') {

                // 🔒 Abort previous request to prevent hang
                if (activeRequest) {
                    activeRequest.abort();
                }

                activeRequest = $.ajax({
                    url: "<?php echo e(route('google.sheet.juniorrej')); ?>",
                    type: 'GET',
                    data: {
                        search: search,
                        page: page,
                        junior_user: junior_user,
                        row_id: row_id,
                        date: date
                    },
                    success: function(res) {
                        $('#senior-table-wrapper').html(res);
                    },
                    error: function(err) {
                        if (err.statusText !== 'abort') {
                            console.error(err);
                        }
                    },
                    complete: function() {
                        activeRequest = null;
                    }
                });
            }

            // ----------------------------------
            // Live Search Suggestions
            // ----------------------------------
            const showSuggestions = debounce(function() {

                const search = $('#senior-search').val().trim();
                const junior_user = $('#junior-filter').val();
                const date = $('#date-filter').val();

                if (search.length < 3) {
                    $('#search-suggestions').empty().hide();

                    // 🔁 behave EXACTLY like cleared search
                    fetchTable('', 1, junior_user, '', date);
                    return;
                }

                $.ajax({
                    url: "<?php echo e(route('juniorrej.suggestions')); ?>",
                    type: 'GET',
                    data: {
                        query: search, // ✅ required
                        junior_user: junior_user
                        // ❌ date intentionally excluded (backend logic)
                    },
                    success: function(res) {

                        let suggestions = '';

                        if (res.length) {
                            res.forEach(item => {
                                suggestions += `
                            <a href="#"
                               class="list-group-item list-group-item-action"
                               data-id="${item.id}"
                               data-name="${item.Name}">
                               ${item.sheet_row_number} |
                               ${item.Name} |
                               ${item.Email_Address} |
                               ${item.Phone_Number} |
                               ${item.Exe_Remarks} |
                               ${item.forwarded_by}
                            </a>`;
                            });
                        } else {
                            suggestions =
                                '<span class="list-group-item">No results found</span>';
                        }

                        $('#search-suggestions').html(suggestions).show();
                    }
                });

            }, 300);

            $('#senior-search').on('input', showSuggestions);

            // ----------------------------------
            // Suggestion click
            // ----------------------------------
            $(document).on('click', '#search-suggestions a', function(e) {
                e.preventDefault();

                const rowId = $(this).data('id');
                const name = $(this).data('name');
                const junior_user = $('#junior-filter').val();
                const date = $('#date-filter').val();

                $('#senior-search').val(name);
                $('#search-suggestions').empty().hide();

                // 🎯 row_id intentionally set
                fetchTable('', 1, junior_user, rowId, date);
            });

            // ----------------------------------
            // Date Filter (FIXED)
            // ----------------------------------
            $('#date-filter').on('change', function() {

                // ❗ reset row_id EXACTLY like search
                fetchTable(
                    $('#senior-search').val().trim(),
                    1,
                    $('#junior-filter').val(),
                    '',
                    $(this).val()
                );
            });

            // ----------------------------------
            // Junior Filter (FIXED)
            // ----------------------------------
            $(document).on('change', '#junior-filter', function() {

                fetchTable(
                    $('#senior-search').val().trim(),
                    1,
                    $(this).val(),
                    '',
                    $('#date-filter').val()
                );
            });

            // ----------------------------------
            // AJAX Pagination
            // ----------------------------------


            // ----------------------------------
            // Hide suggestions on outside click
            // ----------------------------------
            $(document).click(function(e) {
                if (!$(e.target).closest('#senior-search, #search-suggestions').length) {
                    $('#search-suggestions').empty().hide();
                }
            });

        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const topScrollWrapper = document.getElementById("top-scroll-wrapper");
            const topScroll = document.getElementById("top-scroll");
            const bottomScrollWrapper = document.getElementById("bottom-scroll-wrapper");

            function updateTopScrollWidth() {
                const table = bottomScrollWrapper.querySelector("table");
                if (table) {
                    topScroll.style.width = table.scrollWidth + "px";
                }
            }

            // Sync: top → bottom
            topScrollWrapper.addEventListener("scroll", function() {
                bottomScrollWrapper.scrollLeft = topScrollWrapper.scrollLeft;
            });

            // Sync: bottom → top
            bottomScrollWrapper.addEventListener("scroll", function() {
                topScrollWrapper.scrollLeft = bottomScrollWrapper.scrollLeft;
            });

            // Resize after pagination or DOM update
            const observer = new MutationObserver(updateTopScrollWidth);
            observer.observe(bottomScrollWrapper, {
                childList: true,
                subtree: true
            });

            // Initial load
            updateTopScrollWidth();
        });
    </script>


    <style>
        .scroll-sm {
            overflow-x: scroll;
            overflow-y: hidden;
            /* always show scrollbar */
            scrollbar-gutter: stable;
            /* prevent layout shift */
        }

        /* === Chrome, Edge, Safari === */
        .scroll-sm::-webkit-scrollbar {
            height: 36px;
            /* horizontal scrollbar thickness */
            width: 0;
            /* vertical scrollbar thickness */
        }

        .scroll-sm::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #888, #666);
            border-radius: 18px;
            /* rounded ends */
            border: 6px solid #f1f1f1;
            /* gives space inside thumb */
            transition: background 0.3s, border-color 0.3s, height 0.3s;
        }

        .scroll-sm::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #555, #333);
            border-color: #e0e0e0;
        }

        .scroll-sm::-webkit-scrollbar-track {
            background-color: #f1f1f1;
            border-radius: 18px;
        }

        /* === Firefox === */
        .scroll-sm {
            scrollbar-width: auto;
            /* thicker style */
            scrollbar-color: #666 #f1f1f1;
            /* thumb + track */
        }

        #top-scroll-wrapper {
            overflow-x: scroll;
            overflow-y: hidden;
            height: 20px;
        }

        #top-scroll {
            height: 1px;
            /* required */
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const scrollContainer = document.querySelector('.scroll-sm');
            const allRows = Array.from(document.querySelectorAll('#sheet-table-body tr'));

            // =========================
            // Smooth horizontal scroll + mouse-based + drag scroll
            // =========================
            if (scrollContainer) {
                // Auto-scroll interval
                let autoScrollInterval;

                // Mouse move scroll
                scrollContainer.addEventListener('mousemove', e => {
                    if (isDragging) return; // skip if dragging
                    const rect = scrollContainer.getBoundingClientRect();
                    const scrollPercent = (e.clientX - rect.left) / rect.width;
                    scrollContainer.scrollLeft = scrollPercent * (scrollContainer.scrollWidth -
                        scrollContainer.clientWidth);
                });

                // Auto-scroll if mouse leaves
                scrollContainer.addEventListener('mouseenter', () => clearInterval(autoScrollInterval));
                scrollContainer.addEventListener('mouseleave', () => {
                    autoScrollInterval = setInterval(() => {
                        scrollContainer.scrollLeft += 1;
                        if (scrollContainer.scrollLeft >= scrollContainer.scrollWidth -
                            scrollContainer.clientWidth) {
                            scrollContainer.scrollLeft = 0;
                        }
                    }, 20);
                });

                // =========================
                // Mouse-drag scroll
                // =========================
                let isDragging = false,
                    startX, scrollLeftStart;

                scrollContainer.addEventListener('mousedown', e => {
                    isDragging = true;
                    scrollContainer.classList.add('dragging');
                    startX = e.pageX - scrollContainer.offsetLeft;
                    scrollLeftStart = scrollContainer.scrollLeft;
                });

                scrollContainer.addEventListener('mouseup', () => {
                    isDragging = false;
                    scrollContainer.classList.remove('dragging');
                });
                scrollContainer.addEventListener('mouseleave', () => {
                    isDragging = false;
                    scrollContainer.classList.remove('dragging');
                });

                scrollContainer.addEventListener('mousemove', e => {
                    if (!isDragging) return;
                    e.preventDefault();
                    const x = e.pageX - scrollContainer.offsetLeft;
                    const walk = (x - startX) * 1; // scroll speed multiplier
                    scrollContainer.scrollLeft = scrollLeftStart - walk;
                });
            }

            // =========================
            // Helper functions
            // =========================
            const getFields = row => Array.from(row.querySelectorAll('input, select, textarea'))
                .filter(el => el.offsetParent && !el.disabled && !el.readOnly && el.type !== 'hidden');

            const focusField = el => {
                el.focus();
                if (el.tagName === 'INPUT' && el.type === 'text') el.select();
                el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'center'
                });
                el.classList.add('focus-highlight');
                setTimeout(() => el.classList.remove('focus-highlight'), 500);
            };

            const moveFocus = (field, forward = true) => {
                const row = field.closest('tr');
                const idx = allRows.indexOf(row);
                const fields = getFields(row);
                const saveBtn = row.querySelector('.save-btn');
                const fIdx = fields.indexOf(field);
                let target = forward ? fields[fIdx + 1] : fields[fIdx - 1];

                if (!target) {
                    if (forward && saveBtn) {
                        focusField(saveBtn);
                        return;
                    }
                    const nextRow = allRows[forward ? idx + 1 : idx - 1] || allRows[forward ? 0 : allRows
                        .length - 1];
                    const nextFields = getFields(nextRow);
                    if (nextFields.length) target = forward ? nextFields[0] : nextFields[nextFields.length - 1];
                }
                if (target) focusField(target);
            };

            // =========================
            // Navigation & Save handling
            // =========================
            allRows.forEach(row => {
                const fields = getFields(row);
                const saveBtn = row.querySelector('.save-btn');

                fields.forEach(field => {
                    field.addEventListener('keydown', e => {
                        const forward = (e.key === 'Enter' && !e.shiftKey) || (e.key ===
                            'Tab' && !e.shiftKey);
                        const backward = (e.key === 'Enter' && e.shiftKey) || (e.key ===
                            'Tab' && e.shiftKey);
                        if (forward) {
                            e.preventDefault();
                            const last = fields.indexOf(field) === fields.length - 1;
                            last && saveBtn ? focusField(saveBtn) : moveFocus(field, true);
                        } else if (backward) {
                            e.preventDefault();
                            moveFocus(field, false);
                        }
                    });
                });

                if (saveBtn) {
                    saveBtn.addEventListener('keydown', e => {
                        const forward = (e.key === 'Enter' || e.key === 'Tab') && !e.shiftKey;
                        const backward = (e.key === 'Enter' || e.key === 'Tab') && e.shiftKey;
                        if (forward) {
                            e.preventDefault();
                            const nextRow = allRows[allRows.indexOf(row) + 1] || allRows[0];
                            const nf = getFields(nextRow);
                            nf.length && focusField(nf[0]);
                        }
                        if (backward) {
                            e.preventDefault();
                            const prevRow = allRows[allRows.indexOf(row) - 1] || allRows[allRows
                                .length - 1];
                            const pf = getFields(prevRow);
                            pf.length && focusField(pf[pf.length - 1]);
                        }
                    });
                }
            });

            // =========================
            // Custom shortcuts: Alt+S/U/V
            // =========================
            document.addEventListener('keydown', e => {
                if (!e.altKey) return;
                const key = e.key.toLowerCase();
                const row = document.activeElement.closest('tr');
                if (!row) return;

                if (key === 's') { // Save
                    const btn = row.querySelector('.save-btn');
                    if (btn) btn.click();
                    e.preventDefault();
                }
                if (key === 'u') { // Upload
                    const fileInput = row.querySelector('.resume-input');
                    if (fileInput) fileInput.click();
                    e.preventDefault();
                }
                if (key === 'v') { // View
                    const viewBtn = row.querySelector('.view-btn');
                    if (viewBtn && !viewBtn.classList.contains('d-none')) viewBtn.click();
                    e.preventDefault();
                }
            });
        });
    </script>

    <style>
        /* Highlight focused field briefly */
        .focus-highlight {
            animation: highlightFlash 0.5s ease-in-out;
        }

        @keyframes highlightFlash {
            0% {
                background-color: #fff3b0;
            }

            50% {
                background-color: #fff59d;
            }

            100% {
                background-color: transparent;
            }
        }

        /* Mouse-drag cursor */
        .scroll-sm.dragging {
            cursor: grabbing;
            cursor: -webkit-grabbing;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $('#seniorUpdateForm').on('submit', e => {
            e.preventDefault();
            $.ajax({
                url: e.target.action, // uses form's action attribute
                type: e.target.method, // uses form's method attribute (POST/GET)
                data: new FormData(e.target),
                contentType: false,
                processData: false,
                success: r => r.success && location.reload(),
                error: () => alert("Error while saving.")
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content');

            // ---- DATE FORMATTER (MM-DD-YYYY) ----
            const formatDateMDY = (dateStr) => {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                if (isNaN(d)) return '';

                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                const yyyy = d.getFullYear();

                return `${mm}/${dd}/${yyyy}`;
            };

            document.addEventListener('input', function(e) {

                if (!e.target.matches('.email-input')) return;

                const input = e.target;
                const email = input.value.trim();
                const hint = input.nextElementSibling;

                if (email.length < 5 || !email.includes('@')) {
                    hint.textContent = '';
                    input.classList.remove('is-invalid', 'is-valid');
                    return;
                }

                clearTimeout(input._emailCheckTimer);

                input._emailCheckTimer = setTimeout(() => {

                    fetch("<?php echo e(route('check.uniqueemail')); ?>", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                email
                            })
                        })
                        .then(res => res.json())
                        .then(data => {

                            if (data.exists && data.data) {

                                const row = input.closest('tr');

                                // ---------- INPUT FIELDS ----------
                                row.querySelector('[data-key="Name"]').value =
                                    data.data.Name ?? '';

                                row.querySelector('[data-key="Phone Number"]').value =
                                    data.data.Phone_Number ?? '';

                                row.querySelector('[data-key="Location"]').value =
                                    data.data.Location ?? '';

                                row.querySelector('[data-key="Graduation Date"]').value =
                                    formatDateMDY(data.data.Graduation_Date);

                                row.querySelector('[data-key="Amount"]').value =
                                    data.data.Amount ?
                                    `$${parseFloat(data.data.Amount).toFixed(2)}` :
                                    '';

                                row.querySelector('[data-key="Remark"]').value =
                                    data.data.Remark ?? '';

                                // ---------- DROPDOWNS (AUTO SELECT + TRIGGER CHANGE) ----------
                                const setSelect = (key, value) => {
                                    const select = row.querySelector(`[data-key="${key}"]`);
                                    if (!select || value === null) return;

                                    select.value = value;
                                    select.dispatchEvent(new Event('change', {
                                        bubbles: true
                                    }));
                                };

                                setSelect('Relocation', data.data.Relocation);
                                setSelect('Immigration', data.data.Immigration);
                                setSelect('Course', data.data.Course);
                                setSelect('Qualification', data.data.Qualification);
                                setSelect('1st Follow Up Remarks', data.data
                                    .First_Follow_Up_Remarks);
                                setSelect('Time Zone', data.data.Time_Zone);
                                setSelect('Exe Remarks', data.data.Exe_Remarks);

                                // ---------- VISUAL FEEDBACK ----------
                                input.classList.remove('is-invalid');
                                input.classList.add('is-valid');
                                hint.textContent = 'Existing record loaded.';
                                hint.style.color = 'blue';

                            } else {

                                input.classList.remove('is-invalid');
                                input.classList.add('is-valid');
                                hint.textContent = 'Email available.';
                                hint.style.color = 'green';
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            hint.textContent = '⚠️ Server error. Try again.';
                            hint.style.color = 'orange';
                        });

                }, 500);
            });
        });
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const topScrollWrapper = document.getElementById("top-scroll-wrapper");
            const bottomScrollWrapper = document.getElementById("bottom-scroll-wrapper");
            const mainTable = document.getElementById("main-table");
            const topScroll = document.getElementById("top-scroll");

            // Make sure the table exists before setting width
            if (mainTable) {
                topScroll.style.width = mainTable.scrollWidth + "px";
            }

            // Sync scrollbars
            topScrollWrapper.addEventListener("scroll", function() {
                bottomScrollWrapper.scrollLeft = topScrollWrapper.scrollLeft;
            });

            bottomScrollWrapper.addEventListener("scroll", function() {
                topScrollWrapper.scrollLeft = bottomScrollWrapper.scrollLeft;
            });
        });
    </script>

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

                const inputName = textareaName.replace('_hidden', '');
                const input = td.querySelector('input[name="' + inputName + '"]');
                if (!input) return;

                input.value = textarea.value.trim();
            }

            // 🔁 Real-time sync (extended only)
            document.querySelectorAll(
                'textarea.remark-autocomplete, textarea.transferremark-autocomplete, textarea.rejectedremark-autocomplete'
            ).forEach(function(textarea) {
                textarea.addEventListener('input', function() {
                    syncTextareaToInput(textarea);
                });
            });

            // 🛡️ Final sync before submit (extended only)
            form.addEventListener('submit', function() {
                document.querySelectorAll(
                    'textarea.remark-autocomplete, textarea.transferremark-autocomplete, textarea.rejectedremark-autocomplete'
                ).forEach(function(textarea) {
                    syncTextareaToInput(textarea);
                });
            });
        });
    </script>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/norloxsolutionscrm.com/wowcrm/resources/views/database/juniorrej.blade.php ENDPATH**/ ?>