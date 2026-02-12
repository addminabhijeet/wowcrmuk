@extends('layout.layout')
@php
    $title = 'Users List';
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

        </div>
        <div class="card-body p-24">
            <div class="row gy-4">
                <div class="col-xxl-3 col-md-6 user-grid-card   ">
                    <div class="position-relative border radius-16 overflow-hidden">
                        <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                            class="w-100 object-fit-cover">

                        <div class="dropdown position-absolute top-0 end-0 me-16 mt-16">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                                <iconify-icon icon="entypo:dots-three-vertical" class="icon "></iconify-icon>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-10"
                                        href="#">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button type="button"
                                        class="delete-btn dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="ps-16 pb-16 pe-16 text-center mt--50">
                            <img src="{{ asset('assets/images/user-grid/user-grid-img1.png') }}" alt=""
                                class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover">
                            <h6 class="text-lg mb-0 mt-4">Jacob Jones</h6>
                            <span class="text-secondary-light mb-16">ifrandom@gmail.com</span>

                            <div
                                style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                <!-- Countdown -->
                                <div style="margin-right:10px;text-align:center;min-width:60px;">
                                    <div
                                        style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                        <iconify-icon icon="mdi:timer-outline"
                                            style="color:#dc3545;font-size:14px;"></iconify-icon>
                                        <small
                                            style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                    </div>
                                    <span id="countdown"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">08:00:00</span>
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
                                    <span id="elapsed"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                                </div>

                                <!-- Control Buttons -->
                                <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                    <button data-type="resume"
                                        style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                        <iconify-icon icon="mdi:play"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                    </button>

                                    <button data-type="lunch"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                        <iconify-icon icon="mdi:food"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                    </button>

                                    <button data-type="tea"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                        <iconify-icon icon="mdi:coffee"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                    </button>

                                    <button data-type="break"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                        <iconify-icon icon="mdi:pause"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                    </button>
                                </div>
                            </div>
                            <a href=""
                                class="bg-primary-50 text-primary-600 bg-hover-primary-600 hover-text-white p-10 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center justify-content-center mt-16 fw-medium gap-2 w-100">
                                View Profile
                                <iconify-icon icon="solar:alt-arrow-right-linear"
                                    class="icon text-xl line-height-1"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6 user-grid-card   ">
                    <div class="position-relative border radius-16 overflow-hidden">
                        <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                            class="w-100 object-fit-cover">

                        <div class="dropdown position-absolute top-0 end-0 me-16 mt-16">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                                <iconify-icon icon="entypo:dots-three-vertical" class="icon "></iconify-icon>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-10"
                                        href="#">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button type="button"
                                        class="delete-btn dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="ps-16 pb-16 pe-16 text-center mt--50">
                            <img src="{{ asset('assets/images/user-grid/user-grid-img1.png') }}" alt=""
                                class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover">
                            <h6 class="text-lg mb-0 mt-4">Jacob Jones</h6>
                            <span class="text-secondary-light mb-16">ifrandom@gmail.com</span>

                            <div
                                style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                <!-- Countdown -->
                                <div style="margin-right:10px;text-align:center;min-width:60px;">
                                    <div
                                        style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                        <iconify-icon icon="mdi:timer-outline"
                                            style="color:#dc3545;font-size:14px;"></iconify-icon>
                                        <small
                                            style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                    </div>
                                    <span id="countdown"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">08:00:00</span>
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
                                    <span id="elapsed"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                                </div>

                                <!-- Control Buttons -->
                                <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                    <button data-type="resume"
                                        style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                        <iconify-icon icon="mdi:play"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                    </button>

                                    <button data-type="lunch"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                        <iconify-icon icon="mdi:food"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                    </button>

                                    <button data-type="tea"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                        <iconify-icon icon="mdi:coffee"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                    </button>

                                    <button data-type="break"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                        <iconify-icon icon="mdi:pause"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                    </button>
                                </div>
                            </div>
                            <a href=""
                                class="bg-primary-50 text-primary-600 bg-hover-primary-600 hover-text-white p-10 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center justify-content-center mt-16 fw-medium gap-2 w-100">
                                View Profile
                                <iconify-icon icon="solar:alt-arrow-right-linear"
                                    class="icon text-xl line-height-1"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6 user-grid-card   ">
                    <div class="position-relative border radius-16 overflow-hidden">
                        <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                            class="w-100 object-fit-cover">

                        <div class="dropdown position-absolute top-0 end-0 me-16 mt-16">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                                <iconify-icon icon="entypo:dots-three-vertical" class="icon "></iconify-icon>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-10"
                                        href="#">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button type="button"
                                        class="delete-btn dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="ps-16 pb-16 pe-16 text-center mt--50">
                            <img src="{{ asset('assets/images/user-grid/user-grid-img1.png') }}" alt=""
                                class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover">
                            <h6 class="text-lg mb-0 mt-4">Jacob Jones</h6>
                            <span class="text-secondary-light mb-16">ifrandom@gmail.com</span>

                            <div
                                style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                <!-- Countdown -->
                                <div style="margin-right:10px;text-align:center;min-width:60px;">
                                    <div
                                        style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                        <iconify-icon icon="mdi:timer-outline"
                                            style="color:#dc3545;font-size:14px;"></iconify-icon>
                                        <small
                                            style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                    </div>
                                    <span id="countdown"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">08:00:00</span>
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
                                    <span id="elapsed"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                                </div>

                                <!-- Control Buttons -->
                                <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                    <button data-type="resume"
                                        style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                        <iconify-icon icon="mdi:play"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                    </button>

                                    <button data-type="lunch"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                        <iconify-icon icon="mdi:food"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                    </button>

                                    <button data-type="tea"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                        <iconify-icon icon="mdi:coffee"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                    </button>

                                    <button data-type="break"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                        <iconify-icon icon="mdi:pause"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                    </button>
                                </div>
                            </div>
                            <a href=""
                                class="bg-primary-50 text-primary-600 bg-hover-primary-600 hover-text-white p-10 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center justify-content-center mt-16 fw-medium gap-2 w-100">
                                View Profile
                                <iconify-icon icon="solar:alt-arrow-right-linear"
                                    class="icon text-xl line-height-1"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6 user-grid-card   ">
                    <div class="position-relative border radius-16 overflow-hidden">
                        <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                            class="w-100 object-fit-cover">

                        <div class="dropdown position-absolute top-0 end-0 me-16 mt-16">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                                <iconify-icon icon="entypo:dots-three-vertical" class="icon "></iconify-icon>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-10"
                                        href="#">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button type="button"
                                        class="delete-btn dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="ps-16 pb-16 pe-16 text-center mt--50">
                            <img src="{{ asset('assets/images/user-grid/user-grid-img1.png') }}" alt=""
                                class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover">
                            <h6 class="text-lg mb-0 mt-4">Jacob Jones</h6>
                            <span class="text-secondary-light mb-16">ifrandom@gmail.com</span>

                            <div
                                style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                <!-- Countdown -->
                                <div style="margin-right:10px;text-align:center;min-width:60px;">
                                    <div
                                        style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                        <iconify-icon icon="mdi:timer-outline"
                                            style="color:#dc3545;font-size:14px;"></iconify-icon>
                                        <small
                                            style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                    </div>
                                    <span id="countdown"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">08:00:00</span>
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
                                    <span id="elapsed"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                                </div>

                                <!-- Control Buttons -->
                                <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                    <button data-type="resume"
                                        style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                        <iconify-icon icon="mdi:play"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                    </button>

                                    <button data-type="lunch"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                        <iconify-icon icon="mdi:food"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                    </button>

                                    <button data-type="tea"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                        <iconify-icon icon="mdi:coffee"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                    </button>

                                    <button data-type="break"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                        <iconify-icon icon="mdi:pause"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                    </button>
                                </div>
                            </div>
                            <a href=""
                                class="bg-primary-50 text-primary-600 bg-hover-primary-600 hover-text-white p-10 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center justify-content-center mt-16 fw-medium gap-2 w-100">
                                View Profile
                                <iconify-icon icon="solar:alt-arrow-right-linear"
                                    class="icon text-xl line-height-1"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6 user-grid-card   ">
                    <div class="position-relative border radius-16 overflow-hidden">
                        <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                            class="w-100 object-fit-cover">

                        <div class="dropdown position-absolute top-0 end-0 me-16 mt-16">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                                <iconify-icon icon="entypo:dots-three-vertical" class="icon "></iconify-icon>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-10"
                                        href="#">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button type="button"
                                        class="delete-btn dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="ps-16 pb-16 pe-16 text-center mt--50">
                            <img src="{{ asset('assets/images/user-grid/user-grid-img1.png') }}" alt=""
                                class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover">
                            <h6 class="text-lg mb-0 mt-4">Jacob Jones</h6>
                            <span class="text-secondary-light mb-16">ifrandom@gmail.com</span>

                            <div
                                style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                <!-- Countdown -->
                                <div style="margin-right:10px;text-align:center;min-width:60px;">
                                    <div
                                        style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                        <iconify-icon icon="mdi:timer-outline"
                                            style="color:#dc3545;font-size:14px;"></iconify-icon>
                                        <small
                                            style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                    </div>
                                    <span id="countdown"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">08:00:00</span>
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
                                    <span id="elapsed"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                                </div>

                                <!-- Control Buttons -->
                                <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                    <button data-type="resume"
                                        style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                        <iconify-icon icon="mdi:play"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                    </button>

                                    <button data-type="lunch"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                        <iconify-icon icon="mdi:food"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                    </button>

                                    <button data-type="tea"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                        <iconify-icon icon="mdi:coffee"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                    </button>

                                    <button data-type="break"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                        <iconify-icon icon="mdi:pause"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                    </button>
                                </div>
                            </div>
                            <a href=""
                                class="bg-primary-50 text-primary-600 bg-hover-primary-600 hover-text-white p-10 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center justify-content-center mt-16 fw-medium gap-2 w-100">
                                View Profile
                                <iconify-icon icon="solar:alt-arrow-right-linear"
                                    class="icon text-xl line-height-1"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6 user-grid-card   ">
                    <div class="position-relative border radius-16 overflow-hidden">
                        <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                            class="w-100 object-fit-cover">

                        <div class="dropdown position-absolute top-0 end-0 me-16 mt-16">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                                <iconify-icon icon="entypo:dots-three-vertical" class="icon "></iconify-icon>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-10"
                                        href="#">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button type="button"
                                        class="delete-btn dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="ps-16 pb-16 pe-16 text-center mt--50">
                            <img src="{{ asset('assets/images/user-grid/user-grid-img1.png') }}" alt=""
                                class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover">
                            <h6 class="text-lg mb-0 mt-4">Jacob Jones</h6>
                            <span class="text-secondary-light mb-16">ifrandom@gmail.com</span>

                            <div
                                style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                <!-- Countdown -->
                                <div style="margin-right:10px;text-align:center;min-width:60px;">
                                    <div
                                        style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                        <iconify-icon icon="mdi:timer-outline"
                                            style="color:#dc3545;font-size:14px;"></iconify-icon>
                                        <small
                                            style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                    </div>
                                    <span id="countdown"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">08:00:00</span>
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
                                    <span id="elapsed"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                                </div>

                                <!-- Control Buttons -->
                                <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                    <button data-type="resume"
                                        style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                        <iconify-icon icon="mdi:play"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                    </button>

                                    <button data-type="lunch"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                        <iconify-icon icon="mdi:food"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                    </button>

                                    <button data-type="tea"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                        <iconify-icon icon="mdi:coffee"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                    </button>

                                    <button data-type="break"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                        <iconify-icon icon="mdi:pause"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                    </button>
                                </div>
                            </div>
                            <a href=""
                                class="bg-primary-50 text-primary-600 bg-hover-primary-600 hover-text-white p-10 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center justify-content-center mt-16 fw-medium gap-2 w-100">
                                View Profile
                                <iconify-icon icon="solar:alt-arrow-right-linear"
                                    class="icon text-xl line-height-1"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6 user-grid-card   ">
                    <div class="position-relative border radius-16 overflow-hidden">
                        <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                            class="w-100 object-fit-cover">

                        <div class="dropdown position-absolute top-0 end-0 me-16 mt-16">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                                <iconify-icon icon="entypo:dots-three-vertical" class="icon "></iconify-icon>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-10"
                                        href="#">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button type="button"
                                        class="delete-btn dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="ps-16 pb-16 pe-16 text-center mt--50">
                            <img src="{{ asset('assets/images/user-grid/user-grid-img1.png') }}" alt=""
                                class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover">
                            <h6 class="text-lg mb-0 mt-4">Jacob Jones</h6>
                            <span class="text-secondary-light mb-16">ifrandom@gmail.com</span>

                            <div
                                style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                <!-- Countdown -->
                                <div style="margin-right:10px;text-align:center;min-width:60px;">
                                    <div
                                        style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                        <iconify-icon icon="mdi:timer-outline"
                                            style="color:#dc3545;font-size:14px;"></iconify-icon>
                                        <small
                                            style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                    </div>
                                    <span id="countdown"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">08:00:00</span>
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
                                    <span id="elapsed"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                                </div>

                                <!-- Control Buttons -->
                                <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                    <button data-type="resume"
                                        style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                        <iconify-icon icon="mdi:play"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                    </button>

                                    <button data-type="lunch"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                        <iconify-icon icon="mdi:food"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                    </button>

                                    <button data-type="tea"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                        <iconify-icon icon="mdi:coffee"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                    </button>

                                    <button data-type="break"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                        <iconify-icon icon="mdi:pause"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                    </button>
                                </div>
                            </div>
                            <a href=""
                                class="bg-primary-50 text-primary-600 bg-hover-primary-600 hover-text-white p-10 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center justify-content-center mt-16 fw-medium gap-2 w-100">
                                View Profile
                                <iconify-icon icon="solar:alt-arrow-right-linear"
                                    class="icon text-xl line-height-1"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6 user-grid-card   ">
                    <div class="position-relative border radius-16 overflow-hidden">
                        <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                            class="w-100 object-fit-cover">

                        <div class="dropdown position-absolute top-0 end-0 me-16 mt-16">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                                <iconify-icon icon="entypo:dots-three-vertical" class="icon "></iconify-icon>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-10"
                                        href="#">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button type="button"
                                        class="delete-btn dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="ps-16 pb-16 pe-16 text-center mt--50">
                            <img src="{{ asset('assets/images/user-grid/user-grid-img1.png') }}" alt=""
                                class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover">
                            <h6 class="text-lg mb-0 mt-4">Jacob Jones</h6>
                            <span class="text-secondary-light mb-16">ifrandom@gmail.com</span>

                            <div
                                style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                <!-- Countdown -->
                                <div style="margin-right:10px;text-align:center;min-width:60px;">
                                    <div
                                        style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                        <iconify-icon icon="mdi:timer-outline"
                                            style="color:#dc3545;font-size:14px;"></iconify-icon>
                                        <small
                                            style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                    </div>
                                    <span id="countdown"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">08:00:00</span>
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
                                    <span id="elapsed"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                                </div>

                                <!-- Control Buttons -->
                                <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                    <button data-type="resume"
                                        style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                        <iconify-icon icon="mdi:play"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                    </button>

                                    <button data-type="lunch"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                        <iconify-icon icon="mdi:food"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                    </button>

                                    <button data-type="tea"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                        <iconify-icon icon="mdi:coffee"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                    </button>

                                    <button data-type="break"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                        <iconify-icon icon="mdi:pause"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                    </button>
                                </div>
                            </div>
                            <a href=""
                                class="bg-primary-50 text-primary-600 bg-hover-primary-600 hover-text-white p-10 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center justify-content-center mt-16 fw-medium gap-2 w-100">
                                View Profile
                                <iconify-icon icon="solar:alt-arrow-right-linear"
                                    class="icon text-xl line-height-1"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6 user-grid-card   ">
                    <div class="position-relative border radius-16 overflow-hidden">
                        <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                            class="w-100 object-fit-cover">

                        <div class="dropdown position-absolute top-0 end-0 me-16 mt-16">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                                <iconify-icon icon="entypo:dots-three-vertical" class="icon "></iconify-icon>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-10"
                                        href="#">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button type="button"
                                        class="delete-btn dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="ps-16 pb-16 pe-16 text-center mt--50">
                            <img src="{{ asset('assets/images/user-grid/user-grid-img1.png') }}" alt=""
                                class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover">
                            <h6 class="text-lg mb-0 mt-4">Jacob Jones</h6>
                            <span class="text-secondary-light mb-16">ifrandom@gmail.com</span>

                            <div
                                style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                <!-- Countdown -->
                                <div style="margin-right:10px;text-align:center;min-width:60px;">
                                    <div
                                        style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                        <iconify-icon icon="mdi:timer-outline"
                                            style="color:#dc3545;font-size:14px;"></iconify-icon>
                                        <small
                                            style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                    </div>
                                    <span id="countdown"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">08:00:00</span>
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
                                    <span id="elapsed"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                                </div>

                                <!-- Control Buttons -->
                                <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                    <button data-type="resume"
                                        style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                        <iconify-icon icon="mdi:play"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                    </button>

                                    <button data-type="lunch"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                        <iconify-icon icon="mdi:food"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                    </button>

                                    <button data-type="tea"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                        <iconify-icon icon="mdi:coffee"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                    </button>

                                    <button data-type="break"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                        <iconify-icon icon="mdi:pause"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                    </button>
                                </div>
                            </div>
                            <a href=""
                                class="bg-primary-50 text-primary-600 bg-hover-primary-600 hover-text-white p-10 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center justify-content-center mt-16 fw-medium gap-2 w-100">
                                View Profile
                                <iconify-icon icon="solar:alt-arrow-right-linear"
                                    class="icon text-xl line-height-1"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6 user-grid-card   ">
                    <div class="position-relative border radius-16 overflow-hidden">
                        <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                            class="w-100 object-fit-cover">

                        <div class="dropdown position-absolute top-0 end-0 me-16 mt-16">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                                <iconify-icon icon="entypo:dots-three-vertical" class="icon "></iconify-icon>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-10"
                                        href="#">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button type="button"
                                        class="delete-btn dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="ps-16 pb-16 pe-16 text-center mt--50">
                            <img src="{{ asset('assets/images/user-grid/user-grid-img1.png') }}" alt=""
                                class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover">
                            <h6 class="text-lg mb-0 mt-4">Jacob Jones</h6>
                            <span class="text-secondary-light mb-16">ifrandom@gmail.com</span>

                            <div
                                style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                <!-- Countdown -->
                                <div style="margin-right:10px;text-align:center;min-width:60px;">
                                    <div
                                        style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                        <iconify-icon icon="mdi:timer-outline"
                                            style="color:#dc3545;font-size:14px;"></iconify-icon>
                                        <small
                                            style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                    </div>
                                    <span id="countdown"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">08:00:00</span>
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
                                    <span id="elapsed"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                                </div>

                                <!-- Control Buttons -->
                                <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                    <button data-type="resume"
                                        style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                        <iconify-icon icon="mdi:play"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                    </button>

                                    <button data-type="lunch"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                        <iconify-icon icon="mdi:food"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                    </button>

                                    <button data-type="tea"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                        <iconify-icon icon="mdi:coffee"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                    </button>

                                    <button data-type="break"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                        <iconify-icon icon="mdi:pause"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                    </button>
                                </div>
                            </div>
                            <a href=""
                                class="bg-primary-50 text-primary-600 bg-hover-primary-600 hover-text-white p-10 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center justify-content-center mt-16 fw-medium gap-2 w-100">
                                View Profile
                                <iconify-icon icon="solar:alt-arrow-right-linear"
                                    class="icon text-xl line-height-1"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6 user-grid-card   ">
                    <div class="position-relative border radius-16 overflow-hidden">
                        <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                            class="w-100 object-fit-cover">

                        <div class="dropdown position-absolute top-0 end-0 me-16 mt-16">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                                <iconify-icon icon="entypo:dots-three-vertical" class="icon "></iconify-icon>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-10"
                                        href="#">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button type="button"
                                        class="delete-btn dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="ps-16 pb-16 pe-16 text-center mt--50">
                            <img src="{{ asset('assets/images/user-grid/user-grid-img1.png') }}" alt=""
                                class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover">
                            <h6 class="text-lg mb-0 mt-4">Jacob Jones</h6>
                            <span class="text-secondary-light mb-16">ifrandom@gmail.com</span>

                            <div
                                style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                <!-- Countdown -->
                                <div style="margin-right:10px;text-align:center;min-width:60px;">
                                    <div
                                        style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                        <iconify-icon icon="mdi:timer-outline"
                                            style="color:#dc3545;font-size:14px;"></iconify-icon>
                                        <small
                                            style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                    </div>
                                    <span id="countdown"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">08:00:00</span>
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
                                    <span id="elapsed"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                                </div>

                                <!-- Control Buttons -->
                                <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                    <button data-type="resume"
                                        style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                        <iconify-icon icon="mdi:play"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                    </button>

                                    <button data-type="lunch"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                        <iconify-icon icon="mdi:food"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                    </button>

                                    <button data-type="tea"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                        <iconify-icon icon="mdi:coffee"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                    </button>

                                    <button data-type="break"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                        <iconify-icon icon="mdi:pause"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                    </button>
                                </div>
                            </div>
                            <a href=""
                                class="bg-primary-50 text-primary-600 bg-hover-primary-600 hover-text-white p-10 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center justify-content-center mt-16 fw-medium gap-2 w-100">
                                View Profile
                                <iconify-icon icon="solar:alt-arrow-right-linear"
                                    class="icon text-xl line-height-1"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6 user-grid-card   ">
                    <div class="position-relative border radius-16 overflow-hidden">
                        <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                            class="w-100 object-fit-cover">

                        <div class="dropdown position-absolute top-0 end-0 me-16 mt-16">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                                <iconify-icon icon="entypo:dots-three-vertical" class="icon "></iconify-icon>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-10"
                                        href="#">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <button type="button"
                                        class="delete-btn dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10">
                                        Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="ps-16 pb-16 pe-16 text-center mt--50">
                            <img src="{{ asset('assets/images/user-grid/user-grid-img1.png') }}" alt=""
                                class="border br-white border-width-2-px w-100-px h-100-px rounded-circle object-fit-cover">
                            <h6 class="text-lg mb-0 mt-4">Jacob Jones</h6>
                            <span class="text-secondary-light mb-16">ifrandom@gmail.com</span>

                            <div
                                style="display:flex;align-items:center;background:#fff;border:1px solid #ddd;border-radius:50px;padding:5px 8px;box-shadow:0 1px 3px rgba(0,0,0,0.08);flex-wrap:wrap;min-width:180px;">

                                <!-- Countdown -->
                                <div style="margin-right:10px;text-align:center;min-width:60px;">
                                    <div
                                        style="display:flex;align-items:center;justify-content:center;gap:2px;flex-wrap:wrap;">
                                        <iconify-icon icon="mdi:timer-outline"
                                            style="color:#dc3545;font-size:14px;"></iconify-icon>
                                        <small
                                            style="color:#6c757d;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Countdown</small>
                                    </div>
                                    <span id="countdown"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">08:00:00</span>
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
                                    <span id="elapsed"
                                        style="font-weight:bold;color:#212529;font-size:14px;display:block;margin-top:-2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">00:00:00</span>
                                </div>

                                <!-- Control Buttons -->
                                <div id="controlButtons" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                    <button data-type="resume"
                                        style="width:65px;height:28px;border-radius:14px;background:#d4edda;border:1px solid #28a745;display:flex;align-items:center;justify-content:center;font-size:12px;color:#28a745;">
                                        <iconify-icon icon="mdi:play"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Resume
                                    </button>

                                    <button data-type="lunch"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#ffc107;">
                                        <iconify-icon icon="mdi:food"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Lunch
                                    </button>

                                    <button data-type="tea"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#8b4513;">
                                        <iconify-icon icon="mdi:coffee"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Tea
                                    </button>

                                    <button data-type="break"
                                        style="width:65px;height:28px;border-radius:14px;background:#f8f9fa;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:12px;color:#007bff;">
                                        <iconify-icon icon="mdi:pause"
                                            style="margin-right:2px;font-size:14px;"></iconify-icon>Break
                                    </button>
                                </div>
                            </div>
                            <a href=""
                                class="bg-primary-50 text-primary-600 bg-hover-primary-600 hover-text-white p-10 text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center justify-content-center mt-16 fw-medium gap-2 w-100">
                                View Profile
                                <iconify-icon icon="solar:alt-arrow-right-linear"
                                    class="icon text-xl line-height-1"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>
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
@endsection
