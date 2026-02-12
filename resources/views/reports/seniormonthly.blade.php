@extends('layout.layout')
@php
    $title = 'Call Report';
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
@endphp

@section('content')
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
                        <h3 class="mb-0 fw-bold" style="font-size: 36px;">${{ $targetGiven }}</h3>
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
                        <h3 class="mb-0 fw-bold" style="font-size: 36px;">${{ $targetAchieved }}</h3>
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
                        <h3 class="mb-0 fw-bold" style="font-size: 36px;">${{ $targetYetToAchieve }}</h3>
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
                        <h3 class="mb-0 fw-bold" style="font-size: 36px;">{{ $daysLeft }}</h3>
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
                        <h3 class="mb-0 fw-bold" style="font-size: 36px;">{{ $presentDays }}</h3>
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
                        <h3 class="mb-0 fw-bold" style="font-size: 36px;">{{ $absentDays }}</h3>
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
                        <h3 class="mb-0 fw-bold" style="font-size: 36px;">{{ $workingDays }}</h3>
                    </div>
                    <div class="d-flex justify-content-center align-items-center"
                        style="width: 70px; height: 70px; background-color: rgba(239,108,0,0.1); border-radius: 50%;">
                        <iconify-icon icon="mdi:briefcase-outline" style="font-size: 34px; color: #ef6c00;"></iconify-icon>
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
                            <h5 class="fw-bold mb-1">{{ $juniorUser->name }}</h5>
                        </div>
                        <form method="GET" action="{{ route('call.reports.seniormonthly') }}"
                            class="d-flex align-items-center gap-2">
                            <label for="selected_month" class="form-label mb-0 fw-semibold small">Select
                                Month:</label>
                            <input type="month" name="selected_month" id="selected_month"
                                value="{{ request('selected_month', date('Y-m')) }}" class="form-control form-control-sm"
                                onchange="this.form.submit()">
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
                                    <small class="text-muted d-block">Total Calls</small>
                                    <h4 class="fw-bold text-dark mb-0">{{ $MtotalCalls }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                <div class="icon mb-2 text-success fs-2">
                                    <i class="bi bi-bar-chart-fill"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Sr IT Recruiter<br>(Follow Up)</small>
                                    <h4 class="fw-bold text-dark mb-0">{{ $MfollowUpCalls }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                <div class="icon mb-2 text-warning fs-2">
                                    <i class="bi bi-envelope-paper-fill"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Sr IT Recruiter<br>(Called & Mailed)</small>
                                    <h4 class="fw-bold text-dark mb-0">{{ $McalledAndMailedCalls }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                <div class="icon mb-2 text-warning fs-2">
                                    <i class="bi bi-envelope-paper-fill"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Sr IT Recruiter<br>(Self follow up)</small>
                                    <h4 class="fw-bold text-dark mb-0">{{ $MselffollowupCalls }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                <div class="icon mb-2 text-warning fs-2">
                                    <i class="bi bi-envelope-paper-fill"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Sr IT Recruiter<br>(Transfered Follow Up)</small>
                                    <h4 class="fw-bold text-dark mb-0">{{ $MtransferedfollowUpCalls }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                <div class="icon mb-2 text-warning fs-2">
                                    <i class="bi bi-envelope-paper-fill"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Ready To Paid</small>
                                    <h4 class="fw-bold text-dark mb-0">{{ $MreadyToPaidCalls }}</h4>
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
                                                    class="badge bg-info">{{ $fDay1 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay1 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay1 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay1 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay1 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>02</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay2 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay2 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay2 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay2 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay2 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>03</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay3 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay3 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay3 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay3 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay3 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>04</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay4 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay4 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay4 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay4 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay4 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>05</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay5 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay5 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay5 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay5 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay5 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>06</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay6 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay6 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay6 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay6 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay6 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>07</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay7 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay7 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay7 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay7 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay7 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>08</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay8 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay8 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay8 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay8 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay8 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>09</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay9 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay9 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay9 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay9 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay9 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>10</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay10 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay10 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay10 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay10 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay10 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>11</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay11 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay11 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay11 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay11 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay11 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>12</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay12 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay12 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay12 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay12 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay12 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>13</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay13 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay13 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay13 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay13 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay13 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>14</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay14 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay14 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay14 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay14 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay14 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>15</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay15 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay15 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay15 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay15 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay15 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>16</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay16 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay16 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay16 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay16 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay16 }}</span></td>
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
                                                    class="badge bg-info">{{ $fDay17 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay17 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay17 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay17 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay17 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>18</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay18 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay18 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay18 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay18 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay18 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>19</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay19 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay19 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay19 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay19 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay19 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>20</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay20 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay20 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay20 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay20 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay20 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>21</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay21 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay21 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay21 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay21 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay21 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>22</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay22 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay22 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay22 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay22 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay22 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>23</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay23 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay23 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay23 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay23 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay23 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>24</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay24 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay24 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay24 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay24 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay24 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>25</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay25 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay25 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay25 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay25 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay25 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>26</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay26 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay26 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay26 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay26 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay26 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>27</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay27 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay27 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay27 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay27 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay27 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>28</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay28 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay28 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay28 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay28 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay28 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>29</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay29 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay29 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay29 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay29 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay29 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>30</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay30 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay30 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay30 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay30 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay30 }}</span></td>
                                        </tr>
                                        <tr>
                                            <td>31</td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $fDay31 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-info">{{ $cDay31 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-warning">{{ $sfDay31 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $tfDay31 }}</span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success">{{ $rDay31 }}</span></td>
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
        {{-- <div class="col-xxl-4 col-lg-6">
            <div class="card h-100 radius-8 border-0">
                <div class="card-body p-24">
                    <h6 class="fw-bold mb-1">Total Summary (From Joining)</h6>
                    <div class="mt-24">
                        <div class="d-flex align-items-center gap-1 justify-content-between mb-44">
                            <div class="me-4">
                                <span class="text-secondary-light fw-bold mb-12 text-xl">Total Calls</span>
                                <h5 class="fw-semibold mb-0">{{ $totalCalls }}</h5>
                            </div>
                            <div id="semiCircleGauge" class="me-3"></div>
                        </div>

                        <div class="d-flex align-items-center gap-1 justify-content-between mb-44">
                            <div>
                                <span class="text-secondary-light fw-bold mb-12 text-xl">Other Calls</span>
                                <h5 class="fw-semibold mb-0">{{ $otherCalls }}</h5>
                            </div>
                            <div id="areaChart"></div>
                        </div>

                        <div class="d-flex align-items-center gap-1 justify-content-between mb-44">
                            <div>
                                <span class="text-secondary-light fw-bold mb-12 text-xl">Called & Mailed Calls</span>
                                <h5 class="fw-semibold mb-0">{{ $calledAndMailedCalls }}</h5>
                            </div>
                            <div id="iconBarChartCmc"></div>
                        </div>

                        <div class="d-flex align-items-center gap-1 justify-content-between">
                            <div>
                                <span class="text-secondary-light fw-bold mb-12 text-xl">Ready To Pay Calls</span>
                                <h5 class="fw-semibold mb-0">{{ $readyToPaidCalls }}</h5>
                            </div>
                            <div id="iconBarChartR2p"></div>
                        </div>

                        <div class="d-flex align-items-center gap-1 justify-content-between">
                            <div>
                                <span class="text-secondary-light fw-bold mb-12 text-xl">Follow Up Calls</span>
                                <h5 class="fw-semibold mb-0">{{ $followUpCalls }}</h5>
                            </div>
                            <div id="iconBarChartR2p"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
@endsection
