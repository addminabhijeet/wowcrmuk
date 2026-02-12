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

        var chart = new ApexCharts(document.querySelector("#dailyIconBar"), options);
        chart.render();
    </script>';
@endphp

@section('content')
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
                                <h5 class="fw-bold mb-1">{{ $juniorUser->name }}</h5>
                            </div>
                            <form method="GET" action="{{ route('call.reports.senior') }}"
                                class="d-flex align-items-center gap-2">
                                <label for="selected_date" class="form-label mb-0 fw-semibold small">Select Date:</label>
                                <input type="date" name="selected_date" id="selected_date"
                                    value="{{ request('selected_date', date('Y-m-d')) }}"
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
                                        <small class="text-muted d-block">Total Calls</small>
                                        <h4 class="fw-bold text-dark mb-0">{{ $StotalCalls }}</h4>
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
                                        <h4 class="fw-bold text-dark mb-0">{{ $SfollowUpCalls }}</h4>
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
                                        <h4 class="fw-bold text-dark mb-0">{{ $ScalledAndMailedCalls }}</h4>
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
                                        <h4 class="fw-bold text-dark mb-0">{{ $SselffollowupCalls }}</h4>
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
                                        <h4 class="fw-bold text-dark mb-0">{{ $StransferedfollowUpCalls }}</h4>
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
                                        <h4 class="fw-bold text-dark mb-0">{{ $SreadyToPaidCalls }}</h4>
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
                                        <th class="fw-semibold text-center">Sr IT Recruiter<br>(Follow Up)</th>
                                        <th class="fw-semibold text-center">Sr IT Recruiter<br>(Called & Mailed)</th>
                                        <th class="fw-semibold text-center">Sr IT Recruiter<br>(Self follow up)</th>
                                        <th class="fw-semibold text-center">Sr IT Recruiter<br>(Transfered Follow Up)</th>
                                        <th class="fw-semibold text-center">Ready To Paid</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="white-space: nowrap;">08.00AM-09.00AM</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $f8to9am }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info">{{ $c8to9am }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $sf8to9am }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $tf8to9am }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r8to9am }}</span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td style="white-space: nowrap;">09.00AM-10.00AM</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $f9to10am }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info">{{ $c9to10am }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $sf9to10am }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $tf9to10am }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r9to10am }}</span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td style="white-space: nowrap;">10.00AM-11.00AM</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $f10to11am }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info">{{ $c10to11am }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $sf10to11am }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $tf10to11am }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r10to11am }}</span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td style="white-space: nowrap;">11.00AM-12.00PM</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $f11to12pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info">{{ $c11to12pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $sf11to12pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $tf11to12pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r11to12pm }}</span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td style="white-space: nowrap;">12.00PM-01.00PM</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $f12to1pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info">{{ $c12to1pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $sf12to1pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $tf12to1pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r12to1pm }}</span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td style="white-space: nowrap;">01.00PM-02.00PM</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $f1to2pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info">{{ $c1to2pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $sf1to2pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $tf1to2pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r1to2pm }}</span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td style="white-space: nowrap;">02.00PM-03.00PM</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $f2to3pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info">{{ $c2to3pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $sf2to3pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $tf2to3pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r2to3pm }}</span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td style="white-space: nowrap;">03.00PM-04.00PM</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $f3to4pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info">{{ $c3to4pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $sf3to4pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $tf3to4pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r3to4pm }}</span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td style="white-space: nowrap;">04.00PM-05.00PM</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $f4to5pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info">{{ $c4to5pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $sf4to5pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $tf4to5pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r4to5pm }}</span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td style="white-space: nowrap;">05.00PM-06.00PM</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $f5to6pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info">{{ $c5to6pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $sf5to6pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $tf5to6pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r5to6pm }}</span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td style="white-space: nowrap;">06.00PM-07.00PM</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $f6to7pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info">{{ $c6to7pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $sf6to7pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $tf6to7pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r6to7pm }}</span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td style="white-space: nowrap;">07.00PM-08.00PM</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $f7to8pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-info">{{ $c7to8pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $sf7to8pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $tf7to8pm }}</span>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r7to8pm }}</span>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>


                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="col-xxl-4 col-lg-6">
                <div class="card h-100 radius-8 border-0">
                    <div class="card-body p-24">
                        <h6 class="mb-2 fw-bold text-lg">Total Summary (From Joining)</h6>

                        <div class="mt-4">

                            <div
                                class="d-flex align-items-center justify-content-between mb-4 p-3 border rounded-3 shadow-sm bg-white">
                                <div>
                                    <span class="text-secondary fw-normal d-block mb-1">Total Calls (TC)</span>
                                    <h5 class="fw-semibold mb-0">{{ $totalCalls }}</h5>
                                </div>
                                <div class="position-relative">
                                    <div id="semiCircleGauge"></div>
                                    <span
                                        class="rounded-circle bg-light d-flex justify-content-center align-items-center position-absolute start-50 translate-middle bottom-0 p-2">
                                        <iconify-icon icon="mdi:emoji" class="text-primary fs-5"></iconify-icon>
                                    </span>
                                </div>
                            </div>


                            <div
                                class="d-flex align-items-center justify-content-between mb-4 p-3 border rounded-3 shadow-sm bg-white">
                                <div>
                                    <span class="text-secondary fw-normal d-block mb-1">Other Calls (OC)</span>
                                    <h5 class="fw-semibold mb-0">{{ $otherCalls }}</h5>
                                </div>
                                <div id="areaChart" class="p-2"></div>
                            </div>


                            <div
                                class="d-flex align-items-center justify-content-between mb-4 p-3 border rounded-3 shadow-sm bg-white">
                                <div>
                                    <span class="text-secondary fw-normal d-block mb-1">Called & Mailed Calls (C&MC)</span>
                                    <h5 class="fw-semibold mb-0">{{ $calledAndMailedCalls }}</h5>
                                </div>
                                <div id="iconBarChartCmc" class="p-2"></div>
                            </div>


                            <div
                                class="d-flex align-items-center justify-content-between p-3 border rounded-3 shadow-sm bg-white">
                                <div>
                                    <span class="text-secondary fw-normal d-block mb-1">Ready To Pay Calls (R2P)</span>
                                    <h5 class="fw-semibold mb-0">{{ $readyToPaidCalls }}</h5>
                                </div>
                                <div id="iconBarChartR2p" class="p-2"></div>
                            </div>


                            <div
                                class="d-flex align-items-center justify-content-between p-3 border rounded-3 shadow-sm bg-white">
                                <div>
                                    <span class="text-secondary fw-normal d-block mb-1">Follow Up Calls</span>
                                    <h5 class="fw-semibold mb-0">{{ $followUpCalls }}</h5>
                                </div>
                                <div id="iconBarChartR2p" class="p-2"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div> --}}

        </div>
    </div>
@endsection
