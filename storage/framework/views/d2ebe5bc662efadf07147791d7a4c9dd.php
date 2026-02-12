<?php
$role = Auth::user()->role;
?>

<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>

    <div class="sidebar-logo d-flex justify-content-center align-items-center" style="text-align:center; padding: 20px 0;">
        <a href="#">
            <img src="<?php echo e(asset('assets/images/logo.png')); ?>" alt="site logo" class="light-logo" style="max-width: 120px; display: block;">
            <img src="<?php echo e(asset('assets/images/logo-light.png')); ?>" alt="site logo" class="dark-logo" style="max-width: 120px; display: none;">
            <img src="<?php echo e(asset('assets/images/logo-icon.png')); ?>" alt="site logo" class="logo-icon" style="max-width: 60px; display: none;">
        </a>
    </div>

    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">

            
            <?php if($role === 'junior'): ?>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('dashboard.junior')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>IT Recruiter Dashboard</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                    <span>Calendar</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('calendar.junior')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>IT Recruiter Cal.</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Database</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('google.sheet.junior')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Database Sheet</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('google.sheet.junior.candm')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Database Sheet C&M</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Report</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.junior')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Daily Report</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.juniormonthly')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Monthly Report</a></li>
                </ul>
            </li>


            <?php endif; ?>

            
            <?php if($role === 'senior'): ?>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('dashboard.senior')); ?>"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>Dashboard</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                    <span>Calendar</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('calendar.seniorUser')); ?>"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>Personal Calendar</a></li>
                </ul>

                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('calendar.allJuniorlist')); ?>"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>IT Recruiter Calendar</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Database</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('google.sheet.senior')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Database</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('google.sheet.seniorcandm')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Self Called & Mailed</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('google.sheet.seniorpaid')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Ready To Paid</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Report</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.senior')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Daily Personal Report</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.seniormonthly')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Monthly Personal Report</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.alljuniorlist')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Team Report</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Timer</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('timer.senior')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Timer Report</a></li>
                </ul>
            </li>
            <?php endif; ?>

            
            <?php if($role === 'accountant'): ?>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('dashboard.accountant')); ?>"><i class="ri-circle-fill circle-icon text-info-main w-auto"></i>Accountant Dashboard</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                    <span>Calendar</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('calendar.allaccountantUser', Auth::id())); ?>"><i class="ri-circle-fill circle-icon text-info-main w-auto"></i>Calendar</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Database</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('google.sheet.accountant')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Database</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('google.sheet.accountantpaid')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>All Paid</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Candidate</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('candidate.accountant')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Database</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Report</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.allaccountantdaily', Auth::id())); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Daily Personal Report</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.allaccountantmonthly', Auth::id())); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Monthly Personal Report</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.allseniorlist')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>IT Senior Recruiter Team Report</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.alljuniorlist')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>IT Recruiter Team Report</a></li>
                </ul>
            </li>
            <?php endif; ?>

            
            <?php if($role === 'admin'): ?>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('dashboard.admin')); ?>"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i>Dashboard</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="fluent:people-20-filled" class="menu-icon"></iconify-icon>
                    <span>Users</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('users.admin')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Admin</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('users.junior')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>IT Recruiter</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('users.senior')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>IT Senior Recruiter</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('users.accountant')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Support</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('users.trainer')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Trainer</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                    <span>Calendar</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('calendar.allJuniorlist')); ?>"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i>IT Recruiter Calendar</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('calendar.allSeniorlist')); ?>"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i>IT Senior Recruiter Calendar</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('calendar.allAccountantlist')); ?>"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i>Support Calendar</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('calendar.allTrainerlist')); ?>"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i>Trainer Calendar</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="fluent:database-20-regular" class="menu-icon"></iconify-icon>
                    <span>Database</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('google.sheet.index')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Database</a></li>
                </ul>
            </li>



            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="fluent:document-20-regular" class="menu-icon"></iconify-icon>
                    <span>Report</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.alljuniorlist')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>IT Recruiter Report</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.allseniorlist')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>IT Senior Recruiter Report</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.allaccountantlist')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Support Report</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('call.reports.alltrainerlist')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Trainer Report</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="fluent:timer-20-regular" class="menu-icon"></iconify-icon>
                    <span>Timer</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('timer.allsenior')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>IT Senior Recruiter Timer</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('timer.senior')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>IT Recruiter Timer</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('timer.admin')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Timer Setting</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="fluent:mail-20-regular" class="menu-icon"></iconify-icon>
                    <span>SMTP</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('smtp.editall')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>All User SMTP</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="fluent:target-20-regular" class="menu-icon"></iconify-icon>
                    <span>Target</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('target.all')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>All User Target</a></li>
                </ul>
            </li>

            <?php endif; ?>

            
            <?php if($role === 'trainer'): ?>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('dashboard.trainer')); ?>"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>Dashboard</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="hugeicons:invoice-03" class="menu-icon"></iconify-icon>
                    <span>Calendar</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href=""><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>Calendar</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Database</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('google.sheet.trainer')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Database</a></li>
                </ul>
                <ul class="sidebar-submenu">
                    <li><a href="<?php echo e(route('google.sheet.trainercompleted')); ?>"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>All Completed</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Report</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href=""><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Call Report</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Timer</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href=""><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Timer Report</a></li>
                </ul>
            </li>
            <?php endif; ?>

        </ul>
    </div>
</aside><?php /**PATH /home/u235777426/domains/admin.pdfreducer.com/public_html/resources/views/components/sidebar.blade.php ENDPATH**/ ?>