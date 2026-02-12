@extends('layout.layout')

@php
    $title = 'Timer -> IT Recruiter';
    $role = auth()->user()->role ?? '';
    if ($role === 'admin') {
        $subTitle = 'Super Admin';
    } elseif ($role === 'operation') {
        $subTitle = 'Operation Manager';
    } else {
        $subTitle = 'role';
    }
    $script = '<script>
        $(".delete-btn").on("click", function() {
            $(this).closest(".user-grid-card").addClass("d-none")
        });
    </script>';
@endphp

@section('content')
    <div class="card h-100 p-0 radius-12">
        <div
            class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <span class="text-md fw-medium text-secondary-light mb-0">Show</span>
                <select class="form-select form-select-sm w-auto ps-12 py-6 radius-12 h-40-px">
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                    <option>4</option>
                    <option>5</option>
                    <option>6</option>
                    <option>7</option>
                    <option>8</option>
                    <option>9</option>
                    <option>10</option>
                </select>
                <form class="navbar-search">
                    <input type="text" class="bg-base h-40-px w-auto" name="search" placeholder="Search">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
            <a href="javascript:void(0)"
                class="btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2" id="enableAll">
                <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                Enable All IT Recruiter
            </a>

            <a href="javascript:void(0)"
                class="btn btn-danger text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2" id="disableAll">
                <iconify-icon icon="ic:baseline-minus" class="icon text-xl line-height-1"></iconify-icon>
                Disable All IT Recruiter
            </a>
        </div>
        <div class="card-body p-24">
            <div class="row gy-4">
                @foreach ($timers as $timer)
                    <div class="col-xxl-3 col-md-6 user-grid-card">
                        <div class="position-relative border radius-16 overflow-hidden" style="padding-top: 30px;">
                            <img src="{{ $timer['image']
                                ? asset('assets/images/user-grid/user-grid.png')
                                : asset('assets/images/user-grid/user-grid-bg1.png') }}"
                                class="w-100 object-fit-cover" alt="">

                            <div class="ps-16 pb-16 pe-16 text-center mt--50">
                                <img src="{{ $timer['image'] ? asset('storage/app/public/' . $timer['image']) : asset('assets/images/user-grid/user-grid-bg1.png') }}"
                                    class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover"
                                    alt="">
                                <h6 class="text-lg mb-0 mt-4">{{ $timer['name'] }}</h6>
                                <span class="text-secondary-light mb-16">{{ $timer['email'] }}</span>

                                <!-- Timer Widget -->
                                <div class="timer-widget" data-user="{{ $timer['user_id'] }}"
                                    data-remaining="{{ $timer['remaining_seconds'] }}"
                                    data-elapsed="{{ $timer['elapsed_seconds'] }}" data-status="{{ $timer['status'] }}"
                                    style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:5px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                    <!-- Countdown -->
                                    <div style="margin-right:10px;text-align:center;min-width:60px;">
                                        <div
                                            style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                            <iconify-icon icon="mdi:timer-outline"
                                                style="color:#dc3545;font-size:14px;"></iconify-icon>
                                            <small
                                                style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                        </div>
                                        <span class="countdown"
                                            style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            {{ gmdate('H:i:s', $timer['remaining_seconds']) }}
                                        </span>
                                    </div>

                                    <!-- Divider -->
                                    <div style="width:1px;background:#dee2e6;margin:0 4px;"></div>

                                    <!-- Elapsed -->
                                    <div style="margin-right:10px;text-align:center;min-width:60px;">
                                        <div
                                            style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                            <iconify-icon icon="mdi:clock-outline"
                                                style="color:#28a745;font-size:14px;"></iconify-icon>
                                            <small
                                                style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Elapsed</small>
                                        </div>
                                        <span class="elapsed"
                                            style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            {{ gmdate('H:i:s', $timer['elapsed_seconds']) }}
                                        </span>
                                    </div>

                                    <!-- Control Buttons -->
                                    <div class="seniorcontrolButtons" id="seniorcontrolButtons_{{ $timer['user_id'] }}"
                                        style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                        <button data-type="resumebreak" data-user="{{ $timer['user_id'] }}"
                                            style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                            <iconify-icon icon="mdi:play"
                                                style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                        </button>
                                        <button data-type="lunch" data-user="{{ $timer['user_id'] }}"
                                            style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                            <iconify-icon icon="mdi:food"
                                                style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                        </button>
                                        <button data-type="tea" data-user="{{ $timer['user_id'] }}"
                                            style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                            <iconify-icon icon="mdi:coffee"
                                                style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                        </button>
                                        <button data-type="break" data-user="{{ $timer['user_id'] }}"
                                            style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                            <iconify-icon icon="mdi:pause"
                                                style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                        </button>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <!-- Login/Logout Button -->
                                @if ($timer['button_status'] == 0)
                                    <button data-type="login" data-user="{{ $timer['user_id'] }}"
                                        style="width:100%; height:40px; border-radius:14px; background:#0d6efd; border:1px solid #b0c6e6ff; color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; gap:6px; margin-top:16px; cursor:pointer;">
                                        <iconify-icon icon="mdi:play" style="font-size:16px;"></iconify-icon>
                                        Enable IT Recruiter
                                    </button>
                                @else
                                    <button data-type="logout" data-user="{{ $timer['user_id'] }}"
                                        style="width:100%; height:40px; border-radius:14px; background:#dc3545; border:1px solid #d8adb1ff; color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; gap:6px; margin-top:16px; cursor:pointer;">
                                        <iconify-icon icon="mdi:pause" style="font-size:16px;"></iconify-icon>
                                        Disable IT Recruiter
                                    </button>
                                @endif

                                <!-- Login/Logout Button -->
                                @if ($timer['status'] == 0)
                                    <!-- Login Button -->
                                    <button class="user-action-btn" data-type="login"
                                        data-user-id="{{ $timer['user_id'] }}"
                                        style="width:100%; height:40px; border-radius:14px; background:#0d6efd; border:1px solid #b0c6e6ff; color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; gap:6px; margin-top:16px; cursor:pointer;">
                                        <iconify-icon icon="mdi:play" style="font-size:16px;"></iconify-icon>
                                        Log In
                                    </button>
                                @else
                                    <!-- Logout Button -->
                                    <button class="user-action-btn" data-type="logout"
                                        data-user-id="{{ $timer['user_id'] }}"
                                        style="width:100%; height:40px; border-radius:14px; background:#dc3545; border:1px solid #d8adb1ff; color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; gap:6px; margin-top:16px; cursor:pointer;">
                                        <iconify-icon icon="mdi:stop" style="font-size:16px;"></iconify-icon>
                                        Log Out
                                    </button>
                                @endif



                                <div class="position-absolute top-0 end-0 me-16 mt-16"
                                    id="badgeContainer_{{ $timer['user_id'] }}">
                                    @if (!empty($timer['pause_type']))
                                        @if ($timer['pause_type'] == 'lunch')
                                            <span class="badge bg-danger text-white px-3 py-2 radius-8 shadow-sm"
                                                style="font-size:12px; min-width:100px; text-align:center;">Lunch
                                                Break</span>
                                        @elseif($timer['pause_type'] == 'tea')
                                            <span class="badge bg-success text-white px-3 py-2 radius-8 shadow-sm"
                                                style="font-size:12px; min-width:100px; text-align:center;">Tea
                                                Break</span>
                                        @elseif($timer['pause_type'] == 'break')
                                            <span class="badge bg-warning text-white px-3 py-2 radius-8 shadow-sm"
                                                style="font-size:12px; min-width:100px; text-align:center;">Short
                                                Break</span>
                                        @elseif($timer['pause_type'] == 'resume')
                                            <span class="badge bg-primary text-white px-3 py-2 radius-8 shadow-sm"
                                                style="font-size:12px; min-width:100px; text-align:center;">Resumed</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-3 py-2 radius-8 shadow-sm"
                                                style="font-size:12px; min-width:100px; text-align:center;">Unknown</span>
                                        @endif
                                    @endif
                                </div>



                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
                <span>Showing 1 to 10 of 12 entries</span>
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="javascript:void(0)">
                            <iconify-icon icon="ep:d-arrow-left" class=""></iconify-icon>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md bg-primary-600 text-white"
                            href="javascript:void(0)">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px"
                            href="javascript:void(0)">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="javascript:void(0)">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="javascript:void(0)">4</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="javascript:void(0)">5</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link bg-neutral-200 text-secondary-light fw-semibold radius-8 border-0 d-flex align-items-center justify-content-center h-32-px w-32-px text-md"
                            href="javascript:void(0)">
                            <iconify-icon icon="ep:d-arrow-right" class=""></iconify-icon>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>




    <div id="statusOverlay"></div>

    <script>
        // Format seconds to HH:MM:SS
        function formatTime(sec) {
            sec = Math.max(0, Math.floor(sec));
            const h = Math.floor(sec / 3600);
            const m = Math.floor((sec % 3600) / 60);
            const s = sec % 60;
            return `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
        }

        // Fetch all timers from backend every second
        function updateAllTimers() {
            fetch("{{ route('timer.alljuniors') }}")
                .then(res => res.json())
                .then(timers => {
                    if (!Array.isArray(timers)) {
                        console.warn("Invalid timer response:", timers);
                        return;
                    }

                    timers.forEach(data => {
                        const widget = document.querySelector(`.timer-widget[data-user='${data.user_id}']`);
                        if (!widget) return;

                        widget.dataset.remaining = data.remaining_seconds;
                        widget.dataset.elapsed = data.elapsed_seconds;
                        widget.dataset.status = data.status;

                        widget.querySelector('.countdown').innerText = formatTime(data.remaining_seconds);
                        widget.querySelector('.elapsed').innerText = formatTime(data.elapsed_seconds);

                        // Optional: notify when timer hits 0
                        if (data.remaining_seconds <= 0 && data.status === 'finished') {
                            alert(`User ${data.user_id} has finished their 9-hour session.`);
                        }
                    });
                })
                .catch(err => console.error("Timer fetch error:", err));
        }
        setInterval(updateAllTimers, 1000); // sync with DB every second
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Setup control buttons for all timer widgets
            function setupseniorControlButtons() {
                document.querySelectorAll('.seniorcontrolButtons button').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const type = btn.getAttribute('data-type');
                        const userId = btn.getAttribute('data-user');

                        console.log(`[Action] Button clicked: ${type} (User ID: ${userId})`);

                        fetch("{{ route('timer.update') }}", {
                                method: "POST",
                                headers: {
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                    "Content-Type": "application/json"
                                },
                                body: JSON.stringify({
                                    action: type,
                                    user_id: userId
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                console.log("[Action] Response:", data);

                                if (data.success === false && data.notice_status === 1) {
                                    showOverlay(data.message ||
                                        "Please wait for senior to enable.");
                                    return;
                                }

                                // Update individual timer UI for this user only
                                const timerWidget = document.querySelector(
                                    `.timer-widget[data-user='${userId}']`);
                                if (timerWidget) {
                                    timerWidget.dataset.status = data.status;
                                    timerWidget.dataset.remainingSeconds = data
                                        .remaining_seconds;
                                    timerWidget.dataset.elapsedSeconds = data.elapsed_seconds;
                                    updateUI(userId,
                                        data); // assume updateUI handles per-user updates

                                    // 🔹 Update card background based on status
                                    const card = timerWidget.closest('.user-grid-card');
                                    if (card) {
                                        if (data.status === 'paused') {
                                            card.style.backgroundColor = '#fff3cd'; // yellow
                                        } else if (data.status === 'running') {
                                            card.style.backgroundColor = '#d4edda'; // green
                                        } else {
                                            card.style.backgroundColor = ''; // default
                                        }
                                    }
                                }
                            })
                            .catch(err => console.error("[Action] Failed to send:", err));
                    });
                });

            }


            setupseniorControlButtons();

            setInterval(setupseniorControlButtons, 1000);
        });
    </script>




    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // Handle enable/disable click
            function toggleButtonStatus(button) {
                const userId = button.getAttribute("data-user");
                const type = button.getAttribute("data-type"); // login or logout
                const action = type === "login" ? "enable" : "disable";

                console.log(`Toggling user ${userId} to ${action}...`);

                fetch("{{ route('timer.toggleButtonStatus') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            action: action
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        console.log(`Response for user ${userId}:`, data);

                        if (!data.success) {
                            console.error(`Failed to toggle user ${userId}:`, data.message);
                            return;
                        }

                        // Update button instantly based on response
                        updateButton(button, data.button_status);
                    })
                    .catch(err => console.error(`Fetch error for user ${userId}:`, err));
            }

            // Update individual button appearance
            function updateButton(button, status) {
                if (status == 0) {
                    // Change to Enable
                    button.setAttribute("data-type", "login");
                    button.style.background = "#0d6efd";
                    button.style.border = "1px solid #b0c6e6ff";
                    button.innerHTML = `
                    <iconify-icon icon="mdi:play" style="font-size:16px;"></iconify-icon>
                    Enable IT Recruiter
                `;
                } else {
                    // Change to Disable
                    button.setAttribute("data-type", "logout");
                    button.style.background = "#dc3545";
                    button.style.border = "1px solid #d8adb1ff";
                    button.innerHTML = `
                    <iconify-icon icon="mdi:pause" style="font-size:16px;"></iconify-icon>
                    Disable IT Recruiter
                `;
                }
            }

            // Periodic button status check
            function checkButtonStatus() {
                fetch("{{ route('button.status') }}")
                    .then(response => response.json())
                    .then(data => {
                        if (Array.isArray(data)) {
                            data.forEach(user => {
                                const button = document.querySelector(
                                    `button[data-user='${user.user_id}']`);
                                if (button) {
                                    updateButton(button, user.button_status);
                                }
                            });
                        } else {
                            console.warn("Unexpected data format:", data);
                        }
                    })
                    .catch(err => console.error("Polling error:", err));
            }

            // Attach click listener to all buttons (delegated)
            document.body.addEventListener("click", function(e) {
                if (e.target.closest("button[data-user]")) {
                    const btn = e.target.closest("button[data-user]");
                    toggleButtonStatus(btn);
                }
            });

            // Initial check and polling
            checkButtonStatus();
            setInterval(checkButtonStatus, 1000);
        });
    </script>



    <script>
        // Bulk enable/disable all juniors
        function toggleAllStatus(action) {
            console.log(`Attempting to ${action} all juniors...`);
            fetch("{{ route('timer.toggleAllStatus') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        action: action
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Bulk toggle failed:', data);
                        return;
                    }

                    console.log('Bulk toggle response:', data);

                    // Update all user cards after bulk action
                    if (Array.isArray(data.updated)) {
                        data.updated.forEach(user => {
                            updateUserCard(user.user_id, user.button_status);
                        });
                    }

                    // Re-bind events
                    setupStatusButtons();
                })
                .catch(err => console.error('Bulk toggle fetch error:', err));
        }

        // Update UI for a given user card
        function updateUserCard(userId, buttonStatus) {
            const card = document.querySelector(`.user-grid-card[data-user-id='${userId}']`);
            if (!card) return;

            const btnContainer = card.querySelector('.ps-16');
            const dropdown = card.querySelector('.dropdown button');

            // Update dropdown style
            if (dropdown) {
                dropdown.className = buttonStatus ?
                    'bg-white-gradient-light w-32-px h-32-px radius-8 border d-flex justify-content-center align-items-center text-white' :
                    'bg-danger w-32-px h-32-px radius-8 border d-flex justify-content-center align-items-center text-white';
            }

            // Update action button
            if (btnContainer) {
                btnContainer.innerHTML = buttonStatus ?
                    `
                <a href="javascript:void(0)" class="btn btn-danger disable-junior text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2" data-user="${userId}">
                    <iconify-icon icon="ic:baseline-minus" class="icon text-xl line-height-1"></iconify-icon>
                    Disable IT Recruiter
                </a>` :
                    `
                <a href="javascript:void(0)" class="btn btn-primary enable-junior text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2" data-user="${userId}">
                    <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                    Enable IT Recruiter
                </a>`;
            }
        }

        // Bind enable/disable events
        function setupStatusButtons() {
            document.querySelectorAll('.enable-junior').forEach(btn => {
                btn.onclick = () => toggleButtonStatus(btn.dataset.user, 'enable');
            });
            document.querySelectorAll('.disable-junior').forEach(btn => {
                btn.onclick = () => toggleButtonStatus(btn.dataset.user, 'disable');
            });
        }

        // Auto update button status for all juniors
        function checkButtonStatus() {
            fetch("{{ route('button.status') }}")
                .then(response => response.json())
                .then(data => {
                    // Expecting format: [{user_id: 1, button_status: 1}, {user_id: 2, button_status: 0}, ...]
                    if (Array.isArray(data)) {
                        data.forEach(user => {
                            updateUserCard(user.user_id, user.button_status);
                        });
                        setupStatusButtons();
                    } else {
                        console.warn("Unexpected response:", data);
                    }
                })
                .catch(err => console.error("Polling error:", err));
        }

        // Bind top buttons
        const enableAllBtn = document.getElementById('enableAll');
        const disableAllBtn = document.getElementById('disableAll');

        if (enableAllBtn) enableAllBtn.onclick = () => toggleAllStatus('enable');
        if (disableAllBtn) disableAllBtn.onclick = () => toggleAllStatus('disable');

        // Run once immediately
        checkButtonStatus();

        // Poll every 1 second
        setInterval(checkButtonStatus, 1000);
    </script>

    <script>
        $(document).ready(function() {

            function updateButton(button, status) {
                console.log("Updating button for user:", button.data('user-id'), "Status:", status); // debug
                if (status == 0) {
                    button
                        .data('type', 'login')
                        .css({
                            'background': '#0d6efd',
                            'border': '1px solid #b0c6e6ff',
                            'color': '#fff'
                        })
                        .html('<iconify-icon icon="mdi:play" style="font-size:16px;"></iconify-icon> Log In');
                } else {
                    button
                        .data('type', 'logout')
                        .css({
                            'background': '#dc3545',
                            'border': '1px solid #d8adb1ff',
                            'color': '#fff'
                        })
                        .html('<iconify-icon icon="mdi:stop" style="font-size:16px;"></iconify-icon> Log Out');
                }
            }

            $('.user-action-btn').on('click', function() {
                var button = $(this);
                var userId = button.data('user-id');
                var actionType = button.data('type'); // login or logout
                var url = actionType === 'logout' ? "{{ route('ajax.logout') }}" :
                    "{{ route('ajax.login') }}";

                console.log("Button clicked for user:", userId, "Action:", actionType, "URL:",
                    url); // debug

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        user_id: userId,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        console.log("Sending AJAX request..."); // debug
                        button.prop('disabled', true).text(actionType === 'logout' ?
                            'Logging out...' : 'Logging in...');
                    },
                    success: function(res) {
                        console.log("AJAX response:", res); // debug
                        if (res.success) {
                            // Update button UI
                            updateButton(button, actionType === 'logout' ? 0 : 1);
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function(xhr) {
                        console.log("AJAX error:", xhr); // debug
                        alert('Something went wrong!');
                    },
                    complete: function() {
                        console.log("AJAX request completed"); // debug
                        button.prop('disabled', false);
                    }
                });
            });

            // Function to check status of all users and update buttons
            function checkButtonStatus() {
                $.ajax({
                    url: "{{ route('ajax.logincheckStatus') }}",
                    method: 'GET',
                    success: function(res) {
                        console.log("Status check response:", res); // debug
                        if (res.success) {
                            // res.data should be array of {user_id: status}
                            res.data.forEach(function(user) {
                                var button = $('.user-action-btn[data-user-id="' + user
                                    .user_id + '"]');
                                console.log("Updating button for user from status check:", user
                                    .user_id, "Status:", user.status); // debug
                                updateButton(button, user.status);
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log("Status check AJAX error:", xhr); // debug
                    }
                });
            }

            // Check status every 1 second
            setInterval(checkButtonStatus, 1000);

        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            function updatePauseBadges() {
                fetch("{{ route('timers.latestPauseTypes') }}")
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(item => {
                            const badge = document.querySelector(
                                `.pause-badge[data-user="${item.user_id}"]`);
                            if (!badge) return;

                            const type = item.pause_type || 'unknown';
                            badge.textContent = formatPauseText(type);
                            badge.className = `badge pause-badge ${getBadgeClass(type)}`;
                        });
                    })
                    .catch(err => console.error("[Pause Update] Error:", err));
            }

            function formatPauseText(type) {
                switch (type) {
                    case 'lunch':
                        return 'Lunch Break';
                    case 'tea':
                        return 'Tea Break';
                    case 'break':
                        return 'Short Break';
                    case 'resume':
                        return 'Resumed';
                    default:
                        return 'Unknown';
                }
            }

            function getBadgeClass(type) {
                switch (type) {
                    case 'lunch':
                        return 'bg-danger text-white';
                    case 'tea':
                        return 'bg-success text-white';
                    case 'break':
                        return 'bg-warning text-white';
                    case 'resume':
                        return 'bg-primary text-white';
                    default:
                        return 'bg-secondary text-white';
                }
            }

            // Run immediately and then every second
            updatePauseBadges();
            setInterval(updatePauseBadges, 1000);
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function updatePauseButtonsPerUser() {
                document.querySelectorAll('.seniorcontrolButtons').forEach(container => {
                    const userId = container.querySelector('button').getAttribute('data-user');

                    fetch('{{ route('timer.checkPauseButtonsSenior') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                user_id: userId
                            }) // send user ID to backend
                        })
                        .then(res => res.json())
                        .then(data => {
                            const resumeBtn = container.querySelector(
                                'button[data-type="resumebreak"]');
                            const pauseBtns = container.querySelectorAll(
                                'button[data-type="lunch"], button[data-type="tea"], button[data-type="break"]'
                            );

                            // ✅ Existing logic untouched
                            if (data.pause_type === 'lunch' || data.pause_type === 'tea' || data
                                .pause_type === 'break') {
                                pauseBtns.forEach(btn => btn.style.display = 'none');
                                if (resumeBtn) resumeBtn.style.display = 'flex';
                            } else {
                                pauseBtns.forEach(btn => btn.style.display = 'flex');
                                if (resumeBtn) resumeBtn.style.display = 'none';
                            }

                            // ✅ NEW: Update the badge simultaneously
                            const badgeContainer = document.querySelector(`#badgeContainer_${userId}`);
                            if (badgeContainer) {
                                if (data.pause_type === 'lunch') {
                                    badgeContainer.innerHTML =
                                        `<span class="badge bg-danger text-white px-3 py-2 radius-8 shadow-sm" style="font-size:12px;min-width:100px;text-align:center;">Lunch Break</span>`;
                                } else if (data.pause_type === 'tea') {
                                    badgeContainer.innerHTML =
                                        `<span class="badge bg-success text-white px-3 py-2 radius-8 shadow-sm" style="font-size:12px;min-width:100px;text-align:center;">Tea Break</span>`;
                                } else if (data.pause_type === 'break') {
                                    badgeContainer.innerHTML =
                                        `<span class="badge bg-warning text-white px-3 py-2 radius-8 shadow-sm" style="font-size:12px;min-width:100px;text-align:center;">Short Break</span>`;
                                } else if (data.pause_type === 'resume') {
                                    badgeContainer.innerHTML =
                                        `<span class="badge bg-primary text-white px-3 py-2 radius-8 shadow-sm" style="font-size:12px;min-width:100px;text-align:center;">Resumed</span>`;
                                } else {
                                    badgeContainer.innerHTML =
                                        `<span class="badge bg-danger text-white px-3 py-2 radius-8 shadow-sm" style="font-size:12px;min-width:100px;text-align:center;">Offline</span>`;
                                }
                            }
                        })
                        .catch(err => console.error('❌ Error fetching pause state for user', userId, err));
                });
            }

            // Initial check
            updatePauseButtonsPerUser();

            // Check every second
            setInterval(updatePauseButtonsPerUser, 1000);
        });
    </script>
@endsection
