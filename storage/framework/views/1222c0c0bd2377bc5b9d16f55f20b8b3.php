<?php
    $title = 'Report -> IT Senior Recruiter';
    $role = auth()->user()->role ?? '';
    if ($role === 'admin') {
        $subTitle = 'Super Admin';
    } elseif ($role === 'operation') {
        $subTitle = 'Operation Manager';
    } else {
        $subTitle = 'role';
    }
    $script = '<script>
        var options = {
            series: [{
                name: "SELL",
                data: [{
                    x: "8 pm",
                    y: Number("{{ $t8to9pm ?? 0 }}"),
                }, {
                    x: "9 pm",
                    y: Number("{{ $t9to10pm ?? 0 }}"),
                }, {
                    x: "10 pm",
                    y: Number("{{ $t10to11pm ?? 0 }}"),
                }, {
                    x: "11 pm",
                    y: Number("{{ $t11to12pm ?? 0 }}"),
                }, {
                    x: "12 pm",
                    y: Number("{{ $t12to1am ?? 0 }}"),
                }, {
                    x: "1 am",
                    y: Number("{{ $t1to2am ?? 0 }}"),
                }, {
                    x: "2 am",
                    y: Number("{{ $t2to3am ?? 0 }}"),
                }, {
                    x: "3 am",
                    y: Number("{{ $t3to4am ?? 0 }}"),
                }, {
                    x: "4 am",
                    y: Number("{{ $t4to5am ?? 0 }}"),
                }, {
                    x: "5 am",
                    y: Number("{{ $t5to6am ?? 0 }}"),
                }]
            }],
            chart: {
                type: "bar",
                height: 310,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: false,
                    columnWidth: "23%",
                    endingShape: "rounded",
                }
            },
            dataLabels: {
                enabled: false
            },
            fill: {
                type: "gradient",
                colors: ["#487FFF"],
                gradient: {
                    shade: "light",
                    type: "vertical",
                    shadeIntensity: 0.5,
                    gradientToColors: ["#487FFF"],
                    inverseColors: false,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 100],
                },
            },
            grid: {
                show: true,
                borderColor: "#D1D5DB",
                strokeDashArray: 4,
                position: "back",
            },
            xaxis: {
                type: "category",
                categories: ["8 pm", "9 pm", "10 pm", "11 pm", "12 pm", "1 am", "2 am", "3 am", "4 am", "5 am"]
            },
            yaxis: {
                labels: {
                    formatter: function(value) {
                        return (value / 1).toFixed(0) + "CM";
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return value / 1 + "CM";
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#barChart"), options);
        chart.render();



        var options = {
            series: [75],
            chart: {
                height: 165,
                width: 120,
                type: "radialBar",
                sparkline: {
                    enabled: false
                },
                toolbar: {
                    show: false
                },
                padding: {
                    left: -32,
                    right: -32,
                    top: -32,
                    bottom: -32
                },
                margin: {
                    left: -32,
                    right: -32,
                    top: -32,
                    bottom: -32
                }
            },
            plotOptions: {
                radialBar: {
                    offsetY: -24,
                    offsetX: -14,
                    startAngle: -90,
                    endAngle: 90,
                    track: {
                        background: "#E3E6E9",

                        dropShadow: {
                            enabled: false,
                            top: 2,
                            left: 0,
                            color: "#999",
                            opacity: 1,
                            blur: 2
                        }
                    },
                    dataLabels: {
                        show: false,
                        name: {
                            show: false
                        },
                        value: {
                            offsetY: -2,
                            fontSize: "22px"
                        }
                    }
                }
            },
            fill: {
                type: "gradient",
                colors: ["#9DBAFF"],
                gradient: {
                    shade: "dark",
                    type: "horizontal",
                    shadeIntensity: 0.5,
                    gradientToColors: ["#487FFF"],
                    inverseColors: true,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 100]
                }
            },
            stroke: {
                lineCap: "round",
            },
            labels: ["Percent"],
        };

        var chart = new ApexCharts(document.querySelector("#semiCircleGauge"), options);
        chart.render();



        function createChart(chartId, chartColor) {

            let currentYear = new Date().getFullYear();

            var options = {
                series: [{
                    name: "series1",
                    data: [0, 10, 8, 25, 15, 26, 13, 35, 15, 39, 16, 46, 42],
                }, ],
                chart: {
                    type: "area",
                    width: 164,
                    height: 72,

                    sparkline: {
                        enabled: true
                    },

                    toolbar: {
                        show: false
                    },
                    padding: {
                        left: 0,
                        right: 0,
                        top: 0,
                        bottom: 0
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: "smooth",
                    width: 2,
                    colors: [chartColor],
                    lineCap: "round"
                },
                grid: {
                    show: true,
                    borderColor: "transparent",
                    strokeDashArray: 0,
                    position: "back",
                    xaxis: {
                        lines: {
                            show: false
                        }
                    },
                    yaxis: {
                        lines: {
                            show: false
                        }
                    },
                    row: {
                        colors: undefined,
                        opacity: 0.5
                    },
                    column: {
                        colors: undefined,
                        opacity: 0.5
                    },
                    padding: {
                        top: -3,
                        right: 0,
                        bottom: 0,
                        left: 0
                    },
                },
                fill: {
                    type: "gradient",
                    colors: [chartColor],
                    gradient: {
                        shade: "light",
                        type: "vertical",
                        shadeIntensity: 0.5,
                        gradientToColors: [`${chartColor}00`],
                        inverseColors: false,
                        opacityFrom: .8,
                        opacityTo: 0.3,
                        stops: [0, 100],
                    },
                },

                markers: {
                    colors: [chartColor],
                    strokeWidth: 2,
                    size: 0,
                    hover: {
                        size: 8
                    }
                },
                xaxis: {
                    labels: {
                        show: false
                    },
                    categories: [`Jan ${currentYear}`, `Feb ${currentYear}`, `Mar ${currentYear}`, `Apr ${currentYear}`,
                        `May ${currentYear}`, `Jun ${currentYear}`, `Jul ${currentYear}`, `Aug ${currentYear}`,
                        `Sep ${currentYear}`, `Oct ${currentYear}`, `Nov ${currentYear}`, `Dec ${currentYear}`
                    ],
                    tooltip: {
                        enabled: false,
                    },
                },
                yaxis: {
                    labels: {
                        show: false
                    }
                },
                tooltip: {
                    x: {
                        format: "dd/MM/yy HH:mm"
                    },
                },
            };

            var chart = new ApexCharts(document.querySelector(`#${chartId}`), options);
            chart.render();
        }


        createChart("areaChart", "#FF9F29");



        var options = {
            series: [{
                name: "Sales",
                data: [{
                    x: "Mon",
                    y: 20,
                }, {
                    x: "Tue",
                    y: 40,
                }, {
                    x: "Wed",
                    y: 20,
                }, {
                    x: "Thur",
                    y: 30,
                }, {
                    x: "Fri",
                    y: 40,
                }, {
                    x: "Sat",
                    y: 35,
                }]
            }],
            chart: {
                type: "bar",
                width: 164,
                height: 80,
                sparkline: {
                    enabled: true
                },
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    horizontal: false,
                    columnWidth: 14,
                }
            },
            dataLabels: {
                enabled: false
            },
            states: {
                hover: {
                    filter: {
                        type: "none"
                    }
                }
            },
            fill: {
                type: "gradient",
                colors: ["#E3E6E9"],
                gradient: {
                    shade: "light",
                    type: "vertical",
                    shadeIntensity: 0.5,
                    gradientToColors: ["#E3E6E9"],
                    inverseColors: false,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 100],
                },
            },
            grid: {
                show: false,
                borderColor: "#D1D5DB",
                strokeDashArray: 1,
                position: "back",
            },
            xaxis: {
                labels: {
                    show: false
                },
                type: "category",
                categories: ["Mon", "Tue", "Wed", "Thur", "Fri", "Sat"]
            },
            yaxis: {
                labels: {
                    show: false,
                    formatter: function(value) {
                        return (value / 1000).toFixed(0) + "k";
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return value / 1000 + "k";
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#dailyIconBarChart"), options);
        chart.render();
    </script>';
?>

<?php $__env->startSection('content'); ?>
    <style>
        /* --- Optimized for Black & White Printing with Bold Text --- */
        @media print {

            /* Global text visibility and contrast */
            * {
                color: #000 !important;
                box-shadow: none !important;
                text-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body {
                background: #fff !important;
            }

            /* Make all text bolder for visibility */
            h1,
            h2,
            h3,
            h4,
            h5,
            h6,
            p,
            label,
            span,
            small,
            th,
            td {
                color: #000 !important;
                font-weight: 700 !important;
            }

            /* Cards: remove gradients and keep structure clear */
            .card {
                background: #fff !important;
                border: 2px solid #000 !important;
                color: #000 !important;
            }

            .card-body {
                background: #fff !important;
                color: #000 !important;
            }

            /* Table borders and cells */
            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }

            table,
            th,
            td {
                border: 2px solid #000 !important;
                color: #000 !important;
                font-weight: 700 !important;
                background: #fff !important;
            }

            thead {
                background: #e0e0e0 !important;
            }

            /* Badge visibility: solid borders & bold text */
            .badge {
                background: #ddd !important;
                color: #000 !important;
                font-weight: 800 !important;
                border: 2px solid #000 !important;
                padding: 4px 8px !important;
            }

            /* Remove all color backgrounds and gradients */
            [style*="background: linear-gradient"],
            [style*="background-color"] {
                background: #fff !important;
            }

            /* Icons in pure black */
            iconify-icon,
            i {
                color: #000 !important;
                filter: grayscale(100%) contrast(200%) !important;
            }

            /* Headings and labels more prominent */
            h3,
            h4,
            h5 {
                font-size: 1.3em !important;
                font-weight: 800 !important;
            }

            /* Form inputs for month selection */
            input,
            select,
            label {
                color: #000 !important;
                border: 1px solid #000 !important;
                font-weight: 700 !important;
            }

            /* Graph placeholders stay visible */
            #semiCircleGauge,
            #areaChart,
            #dailyIconBarChart {
                border: 2px solid #000 !important;
                background: #fff !important;
                min-height: 80px;
            }

            /* Prevent hover/transform effects during print */
            [onmouseover],
            [onmouseout] {
                transform: none !important;
                box-shadow: none !important;
            }

            /* Optional: Improve spacing for print clarity */
            .card-body,
            .row,
            .col {
                padding: 10px !important;
                margin: 0 !important;
            }

            /* Page setup */
            @page {
                size: A4 portrait;
                margin: 15mm;
            }
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-danger btn-sm" id="downloadPdfBtn">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download PDF
        </button>
    </div>
    <div id="pdfContent">

        <div class="row row-cols-xxl-4 row-cols-md-4 row-cols-sm-2 row-cols-1 g-4">

            <!-- Total Target Given -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm"
                    style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); border-radius: 20px; color: #0d47a1; transition: all 0.3s ease; cursor: pointer;"
                    onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.1)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <p class="mb-1 fw-bold" style="font-size: 15px; opacity: 0.8;">Total Target Given</p>
                            <h3 class="mb-0 fw-bold" style="font-size: 36px;">$<?php echo e($targetGiven); ?></h3>
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
                            <p class="mb-1 fw-bold" style="font-size: 15px; opacity: 0.8;">Total Target Achieved</p>
                            <h3 class="mb-0 fw-bold" style="font-size: 36px;">$<?php echo e($targetAchieved); ?></h3>
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
                            <p class="mb-1 fw-bold" style="font-size: 15px; opacity: 0.8;">Target Yet to Achieve</p>
                            <h3 class="mb-0 fw-bold" style="font-size: 36px;">$<?php echo e($targetYetToAchieve); ?></h3>
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
                            <p class="mb-1 fw-bold" style="font-size: 15px; opacity: 0.8;">Days Left</p>
                            <h3 class="mb-0 fw-bold" style="font-size: 36px;"><?php echo e($daysLeft); ?></h3>
                        </div>
                        <div class="d-flex justify-content-center align-items-center"
                            style="width: 70px; height: 70px; background-color: rgba(46,125,50,0.1); border-radius: 50%;">
                            <iconify-icon icon="mdi:calendar-clock" style="font-size: 34px; color: #2e7d32;"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Present Days -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm"
                    style="background: linear-gradient(135deg, #e8f5e9, #c8e6c9); border-radius: 20px; color: #2e7d32; transition: all 0.3s ease; cursor: pointer;"
                    onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <p class="mb-1 fw-bold" style="font-size: 15px; opacity: 0.8;">Total Present Days</p>
                            <h3 class="mb-0 fw-bold" style="font-size: 36px;"><?php echo e($presentDays); ?></h3>
                        </div>
                        <div class="d-flex justify-content-center align-items-center"
                            style="width: 70px; height: 70px; background-color: rgba(46,125,50,0.1); border-radius: 50%;">
                            <iconify-icon icon="mdi:account-check-outline"
                                style="font-size: 34px; color: #2e7d32;"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Absent Days -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm"
                    style="background: linear-gradient(135deg, #ffebee, #ffcdd2); border-radius: 20px; color: #c62828; transition: all 0.3s ease; cursor: pointer;"
                    onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <p class="mb-1 fw-bold" style="font-size: 15px; opacity: 0.8;">Total Absent Days</p>
                            <h3 class="mb-0 fw-bold" style="font-size: 36px;"><?php echo e($absentDays); ?></h3>
                        </div>
                        <div class="d-flex justify-content-center align-items-center"
                            style="width: 70px; height: 70px; background-color: rgba(198,40,40,0.1); border-radius: 50%;">
                            <iconify-icon icon="mdi:account-cancel-outline"
                                style="font-size: 34px; color: #c62828;"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Working Days -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm"
                    style="background: linear-gradient(135deg, #fff8e1, #ffecb3); border-radius: 20px; color: #ef6c00; transition: all 0.3s ease; cursor: pointer;"
                    onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <p class="mb-1 fw-bold" style="font-size: 15px; opacity: 0.8;">Total Working Days</p>
                            <h3 class="mb-0 fw-bold" style="font-size: 36px;"><?php echo e($workingDays); ?></h3>
                        </div>
                        <div class="d-flex justify-content-center align-items-center"
                            style="width: 70px; height: 70px; background-color: rgba(239,108,0,0.1); border-radius: 50%;">
                            <iconify-icon icon="mdi:briefcase-outline"
                                style="font-size: 34px; color: #ef6c00;"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <div class="row gy-4 mt-1">

            <!-- ================= Left Section ================= -->
            <div class="col-xxl-8 col-lg-6 w-100">
                <div class="card h-100 border-0 shadow-sm radius-12">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                            <div>
                                <h5 class="fw-bold mb-1"><?php echo e($juniorUser->name); ?></h5>
                            </div>
                            <form method="GET"
                                action="<?php echo e(route('call.reports.allseniormonthly', ['userId' => request()->route('userId')])); ?>"
                                class="d-flex align-items-center gap-2">
                                <label for="selected_month" class="form-label mb-0 fw-semibold small">Select
                                    Month:</label>
                                <input type="month" name="selected_month" id="selected_month"
                                    value="<?php echo e(request('selected_month', date('Y-m'))); ?>"
                                    class="form-control form-control-sm" onchange="this.form.submit()">
                            </form>
                        </div>

                        <!-- Stats Section -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-2">
                                <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                    <div class="icon mb-2 text-primary fs-2">
                                        <i class="bi bi-telephone-fill"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold d-block">Total Calls</small>
                                        <h4 class="fw-bold text-dark mb-0"><?php echo e($MtotalCalls); ?></h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                    <div class="icon mb-2 text-success fs-2">
                                        <i class="bi bi-bar-chart-fill"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold d-block">Sr IT Recruiter<br>(Follow Up)</small>
                                        <h4 class="fw-bold text-dark mb-0"><?php echo e($MfollowUpCalls); ?></h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                    <div class="icon mb-2 text-warning fs-2">
                                        <i class="bi bi-envelope-paper-fill"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold d-block">Sr IT Recruiter<br>(Called & Mailed)</small>
                                        <h4 class="fw-bold text-dark mb-0"><?php echo e($McalledAndMailedCalls); ?></h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                    <div class="icon mb-2 text-info fs-2">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold d-block">Sr IT Recruiter<br>(Self follow up)</small>
                                        <h4 class="fw-bold text-dark mb-0"><?php echo e($MselffollowupCalls); ?></h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                    <div class="icon mb-2 text-info fs-2">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold d-block">Sr IT Recruiter<br>(Transfered Follow Up)</small>
                                        <h4 class="fw-bold text-dark mb-0"><?php echo e($MtransferedfollowUpCalls); ?></h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                    <div class="icon mb-2 text-warning fs-2">
                                        <i class="bi bi-envelope-paper-fill"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold d-block">Ready To Paid</small>
                                        <h4 class="fw-bold text-dark mb-0"><?php echo e($MreadyToPaidCalls); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Table Section -->
                        <div class="row">

                            <!-- ================= FIRST TABLE : 1 - 16 ================= -->
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th class="fw-bold">Date</th>
                                                <th class="fw-bold text-center">Sr IT Recruiter<br>(Follow Up)</th>
                                                <th class="fw-bold text-center">Sr IT Recruiter<br>(Called & Mailed)</th>
                                                <th class="fw-bold text-center">Sr IT Recruiter<br>(Self follow up)</th>
                                                <th class="fw-bold text-center">Sr IT Recruiter<br>(Transfered Follow Up)</th>
                                                <th class="fw-bold text-center">Ready To Paid</th>
                                                <!-- Replaces Transfers -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>01</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay1); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay1); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay1); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay1); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay1); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>02</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay2); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay2); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay2); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay2); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay2); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>03</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay3); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay3); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay3); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay3); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay3); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>04</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay4); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay4); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay4); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay4); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay4); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>05</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay5); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay5); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay5); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay5); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay5); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>06</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay6); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay6); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay6); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay6); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay6); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>07</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay7); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay7); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay7); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay7); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay7); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>08</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay8); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay8); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay8); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay8); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay8); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>09</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay9); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay9); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay9); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay9); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay9); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>10</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay10); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay10); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay10); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay10); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay10); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>11</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay11); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay11); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay11); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay11); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay11); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>12</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay12); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay12); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay12); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay12); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay12); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>13</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay13); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay13); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay13); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay13); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay13); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>14</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay14); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay14); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay14); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay14); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay14); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>15</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay15); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay15); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay15); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay15); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay15); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>16</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay16); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay16); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay16); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay16); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay16); ?></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- ================= SECOND TABLE : 17 - 31 ================= -->
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th class="fw-bold">Date</th>
                                                <th class="fw-bold text-center">Sr IT Recruiter<br>(Follow Up)</th>
                                                <th class="fw-bold text-center">Sr IT Recruiter<br>(Called & Mailed)</th>
                                                <th class="fw-bold text-center">Sr IT Recruiter<br>(Self follow up)</th>
                                                <th class="fw-bold text-center">Sr IT Recruiter<br>(Transfered Follow Up)</th>
                                                <th class="fw-bold text-center">Ready To Paid</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>17</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay17); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay17); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay17); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay17); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay17); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>18</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay18); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay18); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay18); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay18); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay18); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>19</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay19); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay19); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay19); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay19); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay19); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>20</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay20); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay20); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay20); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay20); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay20); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>21</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay21); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay21); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay21); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay21); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay21); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>22</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay22); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay22); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay22); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay22); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay22); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>23</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay23); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay23); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay23); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay23); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay23); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>24</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay24); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay24); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay24); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay24); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay24); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>25</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay25); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay25); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay25); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay25); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay25); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>26</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay26); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay26); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay26); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay26); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay26); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>27</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay27); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay27); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay27); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay27); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay27); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>28</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay28); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay28); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay28); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay28); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay28); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>29</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay29); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay29); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay29); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay29); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay29); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>30</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay30); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay30); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay30); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay30); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay30); ?></span></td>
                                            </tr>
                                            <tr>
                                                <td>31</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($fDay31); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-info"><?php echo e($cDay31); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning"><?php echo e($sfDay31); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($tfDay31); ?></span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success"><?php echo e($rDay31); ?></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= Right Summary ================= -->
            
        </div>

    </div>
    <script>
        document.getElementById("downloadPdfBtn").addEventListener("click", async function() {
            const element = document.getElementById("pdfContent");

            // ✅ Clone element to apply isolated print styles
            const clonedElement = element.cloneNode(true);

            // ✅ Add black & white print style dynamically (for PDF only)
            const printStyle = document.createElement("style");
            printStyle.textContent = `
        * {
            color: #000 !important;
            box-shadow: none !important;
            text-shadow: none !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            filter: contrast(250%) brightness(0%) !important;
        }
        body { background: #fff !important; margin: 0 !important; }
        h1, h2, h3, h4, h5, h6, p, label, span, small, th, td {
            color: #000 !important;
            font-weight: 800 !important;
            filter: contrast(250%) brightness(0%) !important;
        }
        .icon-wrapper {
            background: #fff !important;
            border: 2px solid #000 !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        iconify-icon,
        i,
        .icon-wrapper iconify-icon {
            color: #000 !important;
            filter: grayscale(100%) contrast(200%) brightness(0%) !important;
        }
        [style*="background: linear-gradient"],
        [style*="background-color"] {
            background: #fff !important;
        }
        .card {
            background: #fff !important;
            border: 2px solid #000 !important;
            color: #000 !important;
            box-shadow: none !important;
            transition: none !important;
            filter: contrast(250%) brightness(0%) !important;
        }
        [onmouseover], [onmouseout] {
            transform: none !important;
            box-shadow: none !important;
        }
        table, th, td {
            border: 2px solid #000 !important;
            color: #000 !important;
            font-weight: 800 !important;
            background: #fff !important;
            -webkit-text-stroke: 0.3px #000 !important;
            filter: contrast(250%) brightness(0%) !important;
        }
        .badge {
            background: #ddd !important;
            color: #000 !important;
            font-weight: 900 !important;
            border: 2px solid #000 !important;
            padding: 4px 8px !important;
            filter: contrast(250%) brightness(0%) !important;
        }
        i, iconify-icon {
            color: #000 !important;
            filter: contrast(250%) brightness(0%) !important;
        }
        input, select, label {
            color: #000 !important;
            font-weight: 800 !important;
            -webkit-text-stroke: 0.2px #000 !important;
            filter: contrast(250%) brightness(0%) !important;
        }
        #semiCircleGauge, #areaChart, #dailyIconBarChart {
            background: #fff !important;
            min-height: 80px !important;
            filter: contrast(250%) brightness(0%) !important;
        }
        .card-body, .row, .col {
            padding: 10px !important;
            margin: 0 !important;
            filter: contrast(250%) brightness(0%) !important;
        }
        @page {
            size: A4 portrait;
            margin: 0;
        }
    `;
            clonedElement.prepend(printStyle);

            // ✅ Wait for a short time to ensure all assets/styles load
            await new Promise(resolve => setTimeout(resolve, 5));

            // ✅ Proper A4 PDF dimensions in pixels
            const a4WidthPx = 1175;
            const a4HeightPx = Math.round(a4WidthPx * 1.4142);

            // ✅ PDF generation options (optimized for full A4 coverage)
            const opt = {
                margin: [0, 0, 0, 0],
                filename: 'monthly-report.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 3,
                    useCORS: true,
                    scrollY: 0,
                    backgroundColor: "#ffffff",
                    logging: false,
                    letterRendering: true,
                },
                jsPDF: {
                    unit: 'px',
                    format: [a4WidthPx, a4HeightPx],
                    orientation: 'portrait',
                },
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy']
                }
            };

            // Convert Iconify icons to inline SVG images for html2canvas visibility
            clonedElement.querySelectorAll("iconify-icon").forEach(icon => {
                const svg = document.createElement("img");
                const iconName = icon.getAttribute("icon");
                svg.src = `https://api.iconify.design/${iconName}.svg?color=%23000`;
                svg.width = 34;
                svg.height = 34;
                svg.style.filter = "contrast(250%) brightness(0%)";
                icon.replaceWith(svg);
            });


            // ✅ Generate the full-page PDF
            await html2pdf().set(opt).from(clonedElement).save();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/norloxsolutionscrm.com/wowcrm/resources/views/reports/allseniormonthly.blade.php ENDPATH**/ ?>