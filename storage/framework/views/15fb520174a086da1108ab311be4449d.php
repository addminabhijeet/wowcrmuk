<?php
$title='Call Report';
$subTitle = 'Called and Mailed Report';
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
                categories: [`Jan ${currentYear}`, `Feb ${currentYear}`, `Mar ${currentYear}`, `Apr ${currentYear}`, `May ${currentYear}`, `Jun ${currentYear}`, `Jul ${currentYear}`, `Aug ${currentYear}`, `Sep ${currentYear}`, `Oct ${currentYear}`, `Nov ${currentYear}`, `Dec ${currentYear}`],
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

<!-- Include html2pdf.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<!-- Download PDF Button -->
<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-danger btn-sm" id="downloadPdfBtn">
        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download PDF
    </button>
</div>

<!-- Wrap the main content in a container for PDF export -->
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
                        <p class="mb-1 fw-semibold" style="font-size: 15px; opacity: 0.8;">Total Target Given</p>
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
                        <p class="mb-1 fw-semibold" style="font-size: 15px; opacity: 0.8;">Total Target Achieved</p>
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
                        <p class="mb-1 fw-semibold" style="font-size: 15px; opacity: 0.8;">Target Yet to Achieve</p>
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
                        <p class="mb-1 fw-semibold" style="font-size: 15px; opacity: 0.8;">Days Left</p>
                        <h3 class="mb-0 fw-bold" style="font-size: 36px;"><?php echo e($daysLeft); ?></h3>
                    </div>
                    <div class="d-flex justify-content-center align-items-center"
                        style="width: 70px; height: 70px; background-color: rgba(46,125,50,0.1); border-radius: 50%;">
                        <iconify-icon icon="mdi:calendar-clock" style="font-size: 34px; color: #2e7d32;"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 border-0 shadow-sm"
                style="background: linear-gradient(135deg, #e8f5e9, #c8e6c9); border-radius: 20px; color: #2e7d32; transition: all 0.3s ease; cursor: pointer;"
                onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <p class="mb-1 fw-semibold" style="font-size: 15px; opacity: 0.8;">Total Present Days</p>
                        <h3 class="mb-0 fw-bold" style="font-size: 36px;"><?php echo e($presentDays); ?></h3>
                    </div>
                    <div class="d-flex justify-content-center align-items-center"
                        style="width: 70px; height: 70px; background-color: rgba(46,125,50,0.1); border-radius: 50%;">
                        <iconify-icon icon="mdi:account-check-outline" style="font-size: 34px; color: #2e7d32;"></iconify-icon>
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
                        <p class="mb-1 fw-semibold" style="font-size: 15px; opacity: 0.8;">Total Absent Days</p>
                        <h3 class="mb-0 fw-bold" style="font-size: 36px;"><?php echo e($absentDays); ?></h3>
                    </div>
                    <div class="d-flex justify-content-center align-items-center"
                        style="width: 70px; height: 70px; background-color: rgba(198,40,40,0.1); border-radius: 50%;">
                        <iconify-icon icon="mdi:account-cancel-outline" style="font-size: 34px; color: #c62828;"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <!-- Working Days -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm"
                style="background: linear-gradient(135deg, #fff8e1, #ffecb3); border-radius: 20px; color: #ef6c00; transition: all 0.3s ease; cursor: pointer;"
                onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <p class="mb-1 fw-semibold" style="font-size: 15px; opacity: 0.8;">Total Working Days</p>
                        <h3 class="mb-0 fw-bold" style="font-size: 36px;"><?php echo e($workingDays); ?></h3>
                    </div>
                    <div class="d-flex justify-content-center align-items-center"
                        style="width: 70px; height: 70px; background-color: rgba(239,108,0,0.1); border-radius: 50%;">
                        <iconify-icon icon="mdi:briefcase-outline" style="font-size: 34px; color: #ef6c00;"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <!-- Non Working Days -->
        <div class="col">
            <div class="card h-100 border-0 shadow-sm"
                style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); border-radius: 20px; color: #1565c0; transition: all 0.3s ease; cursor: pointer;"
                onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)';">
                <div class="card-body d-flex justify-content-between align-items-center p-4">
                    <div>
                        <p class="mb-1 fw-semibold" style="font-size: 15px; opacity: 0.8;">Non Working Days</p>
                        <h3 class="mb-0 fw-bold" style="font-size: 36px;"><?php echo e($nonWorkingDays); ?></h3>
                    </div>
                    <div class="d-flex justify-content-center align-items-center"
                        style="width: 70px; height: 70px; background-color: rgba(21,101,192,0.1); border-radius: 50%;">
                        <iconify-icon icon="mdi:calendar-blank-outline" style="font-size: 34px; color: #1565c0;"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <div class="row gy-4 mt-1">

        <div class="col-xxl-8 col-lg-6">
            <div class="card h-100 border-0 shadow-sm radius-12">
                <div class="card-body p-4">
                    <!-- Header -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                        <div>
                            <h5 class="fw-bold mb-1">Monthly Call Report</h5>
                        </div>
                        <form method="GET" action="<?php echo e(route('call.reports.juniormonthly')); ?>" class="d-flex align-items-center gap-2">
                            <label for="selected_month" class="form-label mb-0 fw-semibold small">Select Month:</label>
                            <input type="month"
                                name="selected_month"
                                id="selected_month"
                                value="<?php echo e(request('selected_month', date('Y-m'))); ?>"
                                class="form-control form-control-sm"
                                onchange="this.form.submit()">
                        </form>
                    </div>

                    <!-- Stats Section -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                <div class="icon mb-2 text-primary fs-2">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Total Calls (TC)</small>
                                    <h4 class="fw-bold text-dark mb-0"><?php echo e($MtotalCalls); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                <div class="icon mb-2 text-success fs-2">
                                    <i class="bi bi-bar-chart-fill"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Other Calls (OC)</small>
                                    <h4 class="fw-bold text-dark mb-0"><?php echo e($MotherCalls); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                <div class="icon mb-2 text-warning fs-2">
                                    <i class="bi bi-envelope-paper-fill"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Called & Mailed (C&MC)</small>
                                    <h4 class="fw-bold text-dark mb-0"><?php echo e($McalledAndMailedCalls); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th class="fw-semibold">Time Range</th>
                                    <th class="fw-semibold text-center">Called & Mailed Count</th>
                                    <th class="fw-semibold text-center">Other Call Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="white-space: nowrap;">10.00AM-11.00AM</td>
                                    <td class="text-center"><span class="badge bg-info"><?php echo e($t10to11am); ?></span></td>
                                    <td class="text-center"><span class="badge bg-warning"><?php echo e($o10to11am); ?></span></td>
                                </tr>
                                <tr>
                                    <td style="white-space: nowrap;">11.00AM-12.00PM</td>
                                    <td class="text-center"><span class="badge bg-info"><?php echo e($t11to12pm); ?></span></td>
                                    <td class="text-center"><span class="badge bg-warning"><?php echo e($o11to12pm); ?></span></td>
                                </tr>
                                <tr>
                                    <td style="white-space: nowrap;">12.00PM-01.00PM</td>
                                    <td class="text-center"><span class="badge bg-info"><?php echo e($t12to1pm); ?></span></td>
                                    <td class="text-center"><span class="badge bg-warning"><?php echo e($o12to1pm); ?></span></td>
                                </tr>
                                <tr>
                                    <td style="white-space: nowrap;">01.00PM-02.00PM</td>
                                    <td class="text-center"><span class="badge bg-info"><?php echo e($t1to2pm); ?></span></td>
                                    <td class="text-center"><span class="badge bg-warning"><?php echo e($o1to2pm); ?></span></td>
                                </tr>
                                <tr>
                                    <td style="white-space: nowrap;">02.00PM-03.00PM</td>
                                    <td class="text-center"><span class="badge bg-info"><?php echo e($t2to3pm); ?></span></td>
                                    <td class="text-center"><span class="badge bg-warning"><?php echo e($o2to3pm); ?></span></td>
                                </tr>
                                <tr>
                                    <td style="white-space: nowrap;">03.00PM-04.00PM</td>
                                    <td class="text-center"><span class="badge bg-info"><?php echo e($t3to4pm); ?></span></td>
                                    <td class="text-center"><span class="badge bg-warning"><?php echo e($o3to4pm); ?></span></td>
                                </tr>
                                <tr>
                                    <td style="white-space: nowrap;">04.00PM-05.00PM</td>
                                    <td class="text-center"><span class="badge bg-info"><?php echo e($t4to5pm); ?></span></td>
                                    <td class="text-center"><span class="badge bg-warning"><?php echo e($o4to5pm); ?></span></td>
                                </tr>
                                <tr>
                                    <td style="white-space: nowrap;">05.00PM-06.00PM</td>
                                    <td class="text-center"><span class="badge bg-info"><?php echo e($t5to6pm); ?></span></td>
                                    <td class="text-center"><span class="badge bg-warning"><?php echo e($o5to6pm); ?></span></td>
                                </tr>
                                <tr>
                                    <td style="white-space: nowrap;">06.00PM-07.00PM</td>
                                    <td class="text-center"><span class="badge bg-info"><?php echo e($t6to7pm); ?></span></td>
                                    <td class="text-center"><span class="badge bg-warning"><?php echo e($o6to7pm); ?></span></td>
                                </tr>
                                <tr>
                                    <td style="white-space: nowrap;">07.00PM-08.00PM</td>
                                    <td class="text-center"><span class="badge bg-info"><?php echo e($t7to8pm); ?></span></td>
                                    <td class="text-center"><span class="badge bg-warning"><?php echo e($o7to8pm); ?></span></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-lg-6">
            <div class="card h-100 radius-8 border-0">
                <div class="card-body p-24">
                    <h6 class="mb-2 fw-bold text-lg">Statistic</h6>

                    <div class="mt-24">
                        <div class="d-flex align-items-center gap-1 justify-content-between mb-44">
                            <div>
                                <span class="text-secondary-light fw-normal mb-12 text-xl">Total Calls (TC)</span>
                                <h5 class="fw-semibold mb-0"><?php echo e($MtotalCalls); ?></h5>
                            </div>
                            <div class="position-relative">
                                <div id="semiCircleGauge"></div>
                                <span class="w-36-px h-36-px rounded-circle bg-neutral-100 d-flex justify-content-center align-items-center position-absolute start-50 translate-middle bottom-0">
                                    <iconify-icon icon="mdi:emoji" class="text-primary-600 text-md mb-0"></iconify-icon>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-1 justify-content-between mb-44">
                            <div>
                                <span class="text-secondary-light fw-normal mb-12 text-xl">Other Calls (OC)</span>
                                <h5 class="fw-semibold mb-0"><?php echo e($MotherCalls); ?></h5>
                            </div>
                            <div id="areaChart"></div>
                        </div>

                        <div class="d-flex align-items-center gap-1 justify-content-between">
                            <div>
                                <span class="text-secondary-light fw-normal mb-12 text-xl">Called & Mailed Calls (C&MC)</span>
                                <h5 class="fw-semibold mb-0"><?php echo e($McalledAndMailedCalls); ?></h5>
                            </div>
                            <div id="dailyIconBarChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div> <!-- End of #pdfContent -->

<!-- Script to handle PDF download -->
<script>
    document.getElementById("downloadPdfBtn").addEventListener("click", function() {
        const element = document.getElementById("pdfContent");

        // Get the natural height and width of the content
        const elementWidth = element.scrollWidth;
        const elementHeight = element.scrollHeight;

        // A4 paper size in pixels at 96 DPI
        const a4WidthPx = 1240; // A4 width at 150 DPI
        const a4HeightPx = 1754; // A4 height at 150 DPI


        // Calculate scale to fit entire content on a single page
        const scaleX = a4WidthPx / elementWidth;
        const scaleY = a4HeightPx / elementHeight;
        const scale = Math.min(scaleX, scaleY, 1); // prevent upscaling

        const opt = {
            margin: 0,
            filename: 'monthly-report.pdf',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 2, // high resolution
                useCORS: true, // allow external icons/images
                scrollY: 0, // ignore window scroll
                backgroundColor: "#ffffff",
                width: elementWidth,
                height: elementHeight,
            },
            jsPDF: {
                unit: 'px',
                format: [a4WidthPx, a4HeightPx],
                orientation: 'portrait'
            }
        };

        html2pdf()
            .set(opt)
            .from(element)
            .toPdf()
            .get('pdf')
            .then(function(pdf) {
                // Scale and center content on a single page
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                const offsetX = (pageWidth - elementWidth * scale) / 2;
                const offsetY = (pageHeight - elementHeight * scale) / 2;
                pdf.internal.scaleFactor = 1 / scale;
                pdf.internal.write(10, offsetY, '');
            })
            .save();
    });
</script>




<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u235777426/domains/admin.pdfreducer.com/public_html/resources/views/reports/juniormonthly.blade.php ENDPATH**/ ?>