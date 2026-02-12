<?php
    $userImage = Auth::user()->image
        ? asset('assets/images/user-grid/' . Auth::user()->image)
        : asset('assets/images/users/user1.png');
?>

<div class="navbar-header">
    <div class="row align-items-center justify-content-between">
        <div class="col-auto">
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">

                <button type="button" class="sidebar-toggle">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon text-2xl non-active"></iconify-icon>
                    <iconify-icon icon="iconoir:arrow-right" class="icon text-2xl active"></iconify-icon>
                </button>
                <button type="button" class="sidebar-mobile-toggle">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon"></iconify-icon>
                </button>

                <?php if (! (in_array(auth()->user()->role, [
                        'admin',
                        'career',
                        'trainer',
                        'support',
                        'accountant',
                        'operation',
                        'seniorassociate',
                        'resource',
                    ]))): ?>
                    <div
                        style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">


                        <div style="margin-right:10px;text-align:center;min-width:60px;">
                            <div style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                <iconify-icon icon="mdi:timer-outline" style="color:#dc3545;font-size:14px;"></iconify-icon>
                                <small
                                    style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                            </div>
                            <span id="countdown"
                                style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">09:00:00</span>
                        </div>


                        <div style="width:1px;background:#dee2e6;margin:0 4px;"></div>


                        <div style="margin-right:10px;text-align:center;min-width:60px;">
                            <div style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                <iconify-icon icon="mdi:clock-outline" style="color:#28a745;font-size:14px;"></iconify-icon>
                                <small
                                    style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Elapsed</small>
                            </div>
                            <span id="elapsed"
                                style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                        </div>

                        <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                            <button data-type="resumebreak"
                                style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                <iconify-icon icon="mdi:play" style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                            </button>

                            <button data-type="lunch"
                                style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                <iconify-icon icon="mdi:food" style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                            </button>

                            <button data-type="tea"
                                style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                <iconify-icon icon="mdi:coffee" style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                            </button>

                            <button data-type="break"
                                style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                <iconify-icon icon="mdi:pause" style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                            </button>
                        </div>

                        <div id="startButtonContainer"
                            style="display:none;align-items:center;gap:4px;flex-wrap:wrap;margin-left:4px;">
                            <button id="startButton"
                                style="width:65px;height:28px;border-radius:14px;background:#28a745;border:1px solid #1e7e34;display:flex;align-items:center;justify-content:center;font-size:12px;color:#fff;">
                                <iconify-icon icon="mdi:play" style="margin-right:2px;font-size:14px;"></iconify-icon>Start
                            </button>
                        </div>


                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-auto">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <button type="button" data-theme-toggle
                    class="w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center"></button>

                <div class="dropdown">
                    <button
                        class="has-indicator w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center"
                        type="button" data-bs-toggle="dropdown">
                        <iconify-icon icon="mage:email" class="text-primary-light text-xl"></iconify-icon>
                    </button>
                    <div class="dropdown-menu to-top dropdown-menu-lg p-0">
                        <div
                            class="m-16 py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <h6 class="text-lg text-primary-light fw-semibold mb-0">Message</h6>
                            </div>
                            <span
                                class="text-primary-600 fw-semibold text-lg w-40-px h-40-px rounded-circle bg-base d-flex justify-content-center align-items-center">05</span>
                        </div>

                        <div class="max-h-400-px overflow-y-auto scroll-sm pe-4">

                            <a href="javascript:void(0)"
                                class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between">
                                <div
                                    class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                                    <span class="w-40-px h-40-px rounded-circle flex-shrink-0 position-relative">
                                        <img src="<?php echo e(asset('assets/images/notification/profile-3.png')); ?>"
                                            alt="">
                                        <span
                                            class="w-8-px h-8-px bg-success-main rounded-circle position-absolute end-0 bottom-0"></span>
                                    </span>
                                    <div>
                                        <h6 class="text-md fw-semibold mb-4">Kathryn Murphy</h6>
                                        <p class="mb-0 text-sm text-secondary-light text-w-100-px">hey! there i’m...</p>
                                    </div>
                                </div>
                                <div class="d-flex flex-column align-items-end">
                                    <span class="text-sm text-secondary-light flex-shrink-0">12:30 PM</span>
                                    <span
                                        class="mt-4 text-xs text-base w-16-px h-16-px d-flex justify-content-center align-items-center bg-warning-main rounded-circle">8</span>
                                </div>
                            </a>

                        </div>
                        <div class="text-center py-12 px-16">
                            <a href="javascript:void(0)" class="text-primary-600 fw-semibold text-md">See All
                                Message</a>
                        </div>
                    </div>
                </div>

                <div class="dropdown" style="width:auto;">
                    <a href="<?php echo e(route('admin.notifications')); ?>"
                        style="text-decoration:none; color:inherit; display:block;">

                        <button id="notificationDropdownBtn"
                            class="position-relative has-indicator w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true"
                            style="
                transition:0.25s ease;
                box-shadow:0 6px 18px rgba(0,0,0,0.15);
                background:rgba(255,255,255,0.40);
                backdrop-filter:blur(16px) saturate(200%);
                -webkit-backdrop-filter:blur(16px) saturate(200%);
            ">
                            <iconify-icon icon="iconoir:bell" class="text-primary-light text-xl"></iconify-icon>

                            <!-- Unread badge -->
                            <span id="unread-badge"
                                class="badge position-absolute top-0 start-100 translate-middle rounded-pill bg-danger d-none"
                                style="
                    font-size:11px; min-width:22px; height:22px;
                    display:inline-flex; align-items:center; justify-content:center;
                    box-shadow:0 0 8px rgba(255,0,0,0.5);
                    background:rgba(255,0,0,0.85);
                    backdrop-filter:blur(8px);
                ">
                                0
                            </span>
                        </button>

                        <div class="dropdown-menu to-top dropdown-menu-lg p-0"
                            style="
                width:520px;
                max-width:92vw;
                background:rgba(255,255,255,0.12);
                backdrop-filter:blur(30px) saturate(240%);
                -webkit-backdrop-filter:blur(30px) saturate(240%);
                border-radius:24px;
                border:1px solid rgba(255,255,255,0.55);
                box-shadow:
                    0 20px 50px rgba(0,0,0,0.35),
                    inset 0 0 20px rgba(255,255,255,0.25);
                overflow:hidden;
                cursor:pointer;
                transform:translateY(8px);
                transition:0.35s ease;
             ">

                            <div class="d-flex justify-content-between align-items-center px-12 py-12 border-bottom"
                                style="
                    background:rgba(255,255,255,0.20);
                    backdrop-filter:blur(20px);
                    -webkit-backdrop-filter:blur(20px);
                    border-bottom:1px solid rgba(255,255,255,0.45);
                    cursor:pointer;
                    font-weight:600;
                    box-shadow:inset 0 -1px 8px rgba(255,255,255,0.25);
                 ">
                                <strong style="font-size:17px;">Notifications</strong>
                                <button id="markAllReadBtn" class="btn btn-link btn-sm text-muted"
                                    style="
                        font-size:13px; text-decoration:none;
                        transition:0.2s;
                        color:#6c757d !important;
                    ">
                                    Mark all as read
                                </button>
                            </div>

                            <div class="max-h-400-px overflow-y-auto scroll-sm pe-4"
                                style="
                    cursor:pointer;
                    backdrop-filter:blur(14px);
                    -webkit-backdrop-filter:blur(14px);
                 ">
                                <div id="latest-notification-box" class="p-12"
                                    style="
                        white-space:normal;
                        word-wrap:break-word;
                        line-height:1.55;
                        font-size:14px;
                        background:rgba(255,255,255,0.16);
                        border-radius:16px;
                        margin:10px;
                        padding:20px;
                        backdrop-filter:blur(12px);
                        -webkit-backdrop-filter:blur(12px);
                        cursor:pointer;
                        border:1px solid rgba(255,255,255,0.40);
                        box-shadow:
                            0 4px 16px rgba(0,0,0,0.12),
                            inset 0 0 12px rgba(255,255,255,0.25);
                     ">
                                    <p class="text-muted small mb-0">No notifications</p>
                                </div>
                            </div>

                            <!-- Bottom section -->
                            <div class="text-center py-14 px-16 hover-bg-neutral-100 cursor-pointer"
                                style="
                    background:rgba(255,255,255,0.14);
                    border-top:1px solid rgba(255,255,255,0.32);
                    backdrop-filter:blur(18px);
                    -webkit-backdrop-filter:blur(18px);
                    font-size:15px;
                    font-weight:600;
                    color:#3b5bfd;
                    transition:0.25s ease;
                    box-shadow:inset 0 4px 12px rgba(255,255,255,0.15);
                 ">
                                <span>See All Notifications</span>
                            </div>

                        </div>
                    </a>
                </div>






                <div class="dropdown">
                    <button class="d-flex justify-content-center align-items-center rounded-circle" type="button"
                        data-bs-toggle="dropdown">
                        <img src="<?php echo e(Auth::user()->image ? asset('storage/app/public/' . Auth::user()->image) : asset('assets/images/user-grid/user-grid-bg1.png')); ?>" alt="image"
                            class="w-40-px h-40-px object-fit-cover rounded-circle">
                    </button>
                    <div class="dropdown-menu to-top dropdown-menu-sm">
                        <div
                            class="py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <h6 class="text-lg text-primary-light fw-semibold mb-2"><?php echo e(Auth::user()->name); ?></h6>
                                <span class="text-secondary-light fw-medium text-sm"><?php echo e(Auth::user()->email); ?></span>
                            </div>
                            <button type="button" class="hover-text-danger">
                                <iconify-icon icon="radix-icons:cross-1" class="icon text-xl"></iconify-icon>
                            </button>
                        </div>
                        <ul class="to-top-list">
                            <li>
                                <a class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-primary d-flex align-items-center gap-3"
                                    href="">
                                    <iconify-icon icon="solar:user-linear" class="icon text-xl"></iconify-icon> My
                                    Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-primary d-flex align-items-center gap-3"
                                    href="">
                                    <iconify-icon icon="icon-park-outline:setting-two"
                                        class="icon text-xl"></iconify-icon> Setting
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-danger d-flex align-items-center gap-3"
                                    href="<?php echo e(route('logout')); ?>"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon> Log Out
                                </a>

                                
                                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                                    <?php echo csrf_field(); ?>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #statusOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: bold;
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        flex-direction: column;
        transition: opacity 0.3s ease;
    }

    #statusOverlay.show {
        opacity: 1;
        pointer-events: auto;
    }
</style>

<div id="statusOverlay"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ===============================
        // Timer Variables
        // ===============================
        let backendSyncInterval;
        let remainingSeconds = Number("<?php echo e($remaining_seconds ?? 0); ?>");
        let elapsedSeconds = Number("<?php echo e($elapsed_seconds ?? 0); ?>");
        let status = "<?php echo e($status ?? 'running'); ?>";

        let overlayTimeout;
        let isInactive = false;

        // ===============================
        // Helper Functions
        // ===============================
        function formatTime(sec) {
            sec = Math.floor(sec);
            const h = Math.floor(sec / 3600);
            const m = Math.floor((sec % 3600) / 60);
            const s = sec % 60;
            return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }

        function updateUI() {
            const countdownElem = document.getElementById('countdown');
            const elapsedElem = document.getElementById('elapsed');
            if (countdownElem) countdownElem.innerText = formatTime(remainingSeconds);
            if (elapsedElem) elapsedElem.innerText = formatTime(elapsedSeconds);
        }

        function showOverlay(message) {
            const overlay = document.getElementById('statusOverlay');
            if (!overlay) return;
            overlay.innerText = message;
            overlay.classList.add('show');
            clearTimeout(overlayTimeout);
            overlayTimeout = setTimeout(() => {
                overlay.classList.remove('show');
            }, 3000);
        }

        document.addEventListener('visibilitychange', function() {
            isInactive = document.hidden;
        });

        // ===============================
        // Backend Sync
        // ===============================

        function syncWithBackend() {
            fetch("<?php echo e(route('timer.update')); ?>", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        action: 'tick',
                        is_inactive: isInactive
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        console.warn("[Sync] No success response");
                        return;
                    }

                    if (data.notice_status === 1 && data.message) {
                        showOverlay(data.message);
                    }

                    remainingSeconds = data.remaining_seconds;
                    elapsedSeconds = data.elapsed_seconds;
                    status = data.status;
                    updateUI();

                    if (data.logout) {
                        console.warn("[Sync] Work session ended. Logging out...");
                        clearInterval(backendSyncInterval);
                        const userRole = "<?php echo e(auth()->user()->role); ?>";
                        if (userRole !== 'accountant') {
                            alert("Your 9-hour work session has ended.");
                        }
                    }
                })
                .catch(err => console.error("[Sync] Timer sync failed:", err));
        }

        // ===============================
        // Control Buttons (Pause/Resume/etc)
        // ===============================
        const controlButtonsContainer = document.getElementById('controlButtons');
        if (controlButtonsContainer) {
            controlButtonsContainer.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', () => {
                    const type = btn.getAttribute('data-type');
                    fetch("<?php echo e(route('timer.update')); ?>", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                action: type
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success === false && data.notice_status === 1) {
                                showOverlay(data.message ||
                                    "Please wait for senior to enable.");
                                return;
                            }

                            remainingSeconds = data.remaining_seconds;
                            elapsedSeconds = data.elapsed_seconds;
                            status = data.status;
                            updateUI();
                        })
                        .catch(err => console.error("[Action] Failed to send:", err));
                });
            });
        }

        // ===============================
        // Initialize Timer
        // ===============================
        updateUI();
        backendSyncInterval = setInterval(syncWithBackend, 1000);

    });
</script>




<script>
    document.addEventListener("DOMContentLoaded", function() {
        const controlButtons = document.getElementById('controlButtons');

        if (!controlButtons) {
            console.error("[Init Error] Elements #controlButtons or  are missing.");
            return;
        }

        function checkButtonStatus() {
            fetch("<?php echo e(route('button.status')); ?>")
                .then(response => {
                    if (!response.ok) throw new Error("Network response was not ok");
                    return response.json();
                })
                .then(data => {
                    const status = parseInt(data.button_status ?? 0);

                    if (status === 1) {
                        // Show control buttons, hide start button
                        controlButtons.style.display = 'flex';

                        console.log("[Button Status] Control buttons visible (status=1)");
                    } else {
                        // Hide control buttons, show start button
                        controlButtons.style.display = 'none';

                        console.log("[Button Status] Control buttons hidden (status=0)");
                    }
                })
                .catch(err => console.error("[Status Check] Error fetching button status:", err));
        }

        // Initial check + periodic refresh
        checkButtonStatus();
        setInterval(checkButtonStatus, 1000);
    });
</script>





<script>
    document.addEventListener('DOMContentLoaded', function() {

        const startButton = document.getElementById('startButton');


        // Check if today's timer already exists
        fetch('<?php echo e(route('timer.start')); ?>', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    check: true
                })
            })
            .then(res => res.json())
            .then(data => {})
            .catch(err => console.error('❌ Error checking timer existence:', err));

        // On Start button click
        startButton.addEventListener('click', function() {


            fetch('<?php echo e(route('timer.start')); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({}) // No check parameter here
                })
                .then(res => res.json())
                .then(data => {


                    if (data.success || data.exists || data.timer) {
                        alert('⏱ Timer is running for today!');
                    } else {
                        console.error('⚠️ Unexpected response while starting timer:', data);
                        alert('⚠️ Something went wrong starting the timer.');
                    }
                })
                .catch(err => {
                    console.error('❌ Error starting timer:', err);
                });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startButtonContainer = document.getElementById('startButtonContainer');

        // Function to check timer status
        function checkTimerStatus() {
            fetch('<?php echo e(route('timer.starthide')); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        check: true
                    }) // Only check, don't start
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.exists) {
                        // Show start button only if timer doesn't exist
                        startButtonContainer.style.display = 'flex';
                    } else {
                        // Timer exists, hide start button
                        startButtonContainer.style.display = 'none';
                    }
                })
                .catch(err => {
                    console.error('❌ Error checking timer:', err);
                    if (err instanceof TypeError) {
                        console.error('TypeError - likely a network or CORS issue');
                    }
                });
        }

        // Initial check
        checkTimerStatus();

        // Re-check every 1 second
        setInterval(checkTimerStatus, 1000);
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        function updatePauseButtons() {
            fetch('<?php echo e(route('timer.checkPauseButtons')); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(res => res.json())
                .then(data => {
                    const buttonsToHide = document.querySelectorAll(
                        '#controlButtons button[data-type="lunch"], ' +
                        '#controlButtons button[data-type="tea"], ' +
                        '#controlButtons button[data-type="break"]'
                    );

                    const resumeBtn = document.querySelector(
                        '#controlButtons button[data-type="resumebreak"]');

                    if (data.pause_type === 'lunch' || data.pause_type === 'tea' || data.pause_type ===
                        'break') {
                        // Hide lunch/tea/break buttons
                        buttonsToHide.forEach(btn => {
                            if (btn) btn.style.display = 'none';
                        });

                        // Show resume button
                        if (resumeBtn) resumeBtn.style.display = 'flex';
                    } else {
                        // Show all pause buttons
                        buttonsToHide.forEach(btn => {
                            if (btn) btn.style.display = 'flex';
                        });

                        // Hide resume button
                        if (resumeBtn) resumeBtn.style.display = 'none';
                    }
                })
                .catch(err => console.error('❌ Error checking pause buttons:', err));
        }

        // Initial check
        updatePauseButtons();

        // Check every 2 seconds
        setInterval(updatePauseButtons, 1000);
    });
</script>

<script>
    (function() {

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        let lastNotificationId = localStorage.getItem("last_notification_id") || null;
        let lastNotificationTime = localStorage.getItem("last_notification_time") || null;

        let dropdownTimer = null;
        const pollInterval = 10000;

        const latestBox = document.querySelector('#latest-notification-box');
        const dropdownBtn = document.querySelector('#notificationDropdownBtn');
        const unreadBadge = document.querySelector('#unread-badge');
        const markAllBtn = document.querySelector('#markAllReadBtn');


        function setBadge(count) {
            if (!unreadBadge) return;

            if (count > 0) {
                unreadBadge.textContent = count > 99 ? '99+' : count;
                unreadBadge.classList.remove('d-none');
            } else {
                unreadBadge.classList.add('d-none');
            }
        }


        function fetchLatestNotification() {

            fetch("<?php echo e(route('admin.latest.notification')); ?>", {
                    credentials: "same-origin"
                })
                .then(res => res.json())
                .then(response => {

                    if (typeof response.unread_count !== "undefined") {
                        setBadge(response.unread_count);
                    }

                    if (!response.status || !response.html) return;

                    const latestId = response.id;
                    const latestTime = response.created_at; // timestamp

                    // 🚀 ONLY show dropdown if BOTH are new:
                    // 1. ID changed
                    // 2. timestamp is newer
                    if (response.unread_count > 0 &&
                        (lastNotificationId != latestId || lastNotificationTime != latestTime)) {


                        // Save ID + timestamp
                        localStorage.setItem("last_notification_id", latestId);
                        localStorage.setItem("last_notification_time", latestTime);

                        // Update HTML
                        if (latestBox) {
                            latestBox.innerHTML = response.html;

                            latestBox.firstElementChild?.classList.add("flash-new");
                            setTimeout(() => latestBox.firstElementChild?.classList.remove("flash-new"), 1400);
                        }

                        // Show dropdown ONLY for TRUE new notification
                        const dropdown = new bootstrap.Dropdown(dropdownBtn);
                        dropdown.show();

                        if (dropdownTimer) clearTimeout(dropdownTimer);
                        dropdownTimer = setTimeout(() => dropdown.hide(), 8000);

                        return;
                    }

                    // If NOT a new notification → DO NOT open dropdown
                    // Just ensure HTML is loaded once if empty
                    if (!latestBox.innerHTML.trim()) {
                        latestBox.innerHTML = response.html;
                    }

                })
                .catch(err => console.error("Fetch latest notification error:", err));
        }


        function markAllRead() {
            fetch("<?php echo e(route('admin.notifications.markallread')); ?>", {
                    method: "PUT", // Match the route
                    credentials: "same-origin",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken || ""
                    },
                    body: JSON.stringify({})
                })
                .then(res => res.json())
                .then(json => {
                    if (json.status) {
                        setBadge(0);

                        // Save latest ID so popup NEVER reopens
                        if (json.latest_id) {
                            localStorage.setItem("last_notification_id", json.latest_id);
                        }
                        if (json.latest_time) {
                            localStorage.setItem("last_notification_time", Number(json.latest_time));
                        }
                    }
                })
                .catch(err => console.error('Error marking notifications as read:', err));
        }



        // Mark on dropdown open
        if (dropdownBtn) {
            dropdownBtn.addEventListener("show.bs.dropdown", () => {
                setTimeout(() => markAllRead(), 8000);
            });
        }

        if (markAllBtn) {
            markAllBtn.addEventListener("click", (e) => {
                e.preventDefault();
                markAllRead();
            });
        }

        fetchLatestNotification();
        setInterval(fetchLatestNotification, pollInterval);

    })();
</script>



<style>
    @keyframes flashNew {
        0% {
            background: rgba(0, 123, 255, .1);
            transform: translateY(-4px);
        }

        100% {
            background: transparent;
            transform: translateY(0);
        }
    }

    .flash-new {
        animation: flashNew 0.9s ease;
    }
</style>
<?php /**PATH /var/www/norloxsolutionscrm.com/wowcrm/resources/views/components/navbar.blade.php ENDPATH**/ ?>