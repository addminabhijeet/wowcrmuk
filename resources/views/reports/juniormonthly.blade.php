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
    <!-- Include html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>



    <!-- Wrap the main content in a container for PDF export -->
    <div id="pdfContent">

        <div class="row row-cols-xxl-4 row-cols-md-4 row-cols-sm-2 row-cols-1 g-4">
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
            <div class="col-xxl-8 col-lg-6">
                <div class="card h-100 border-0 shadow-sm radius-12">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $juniorUser->name }}</h5>
                            </div>
                            <form method="GET" action="{{ route('call.reports.juniormonthly') }}"
                                class="d-flex align-items-center gap-2">
                                <label for="selected_month" class="form-label mb-0 fw-semibold small">Select
                                    Month:</label>
                                <input type="month" name="selected_month" id="selected_month"
                                    value="{{ request('selected_month', date('Y-m')) }}"
                                    class="form-control form-control-sm" onchange="this.form.submit()">
                            </form>
                        </div>
                        <div class="row g-3 mb-4">

                            <div class="col-md-2 col-sm-4 col-6">
                                <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                    <div class="icon mb-2 text-primary fs-2">
                                        <i class="bi bi-telephone-fill"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold d-block">Total Calls</small>
                                        <h4 class="fw-bold text-dark mb-0">{{ $MtotalCalls }}</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-4 col-6">
                                <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                    <div class="icon mb-2 text-success fs-2">
                                        <i class="bi bi-bar-chart-fill"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold d-block">Other Calls</small>
                                        <h4 class="fw-bold text-dark mb-0">{{ $MotherCalls }}</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-4 col-6">
                                <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                    <div class="icon mb-2 text-warning fs-2">
                                        <i class="bi bi-envelope-paper-fill"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold d-block">Called & Mailed</small>
                                        <h4 class="fw-bold text-dark mb-0">{{ $McalledAndMailedCalls }}</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-4 col-6">
                                <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                    <div class="icon mb-2 text-warning fs-2">
                                        <i class="bi bi-envelope-paper-fill"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold d-block">Per Day Calls</small>
                                        <h4 class="fw-bold text-dark mb-0">{{ $MAvgTotalCalls }}</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-4 col-6">
                                <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                    <div class="icon mb-2 text-warning fs-2">
                                        <i class="bi bi-envelope-paper-fill"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold d-block">Total Transfers</small>
                                        <h4 class="fw-bold text-dark mb-0">{{ $Mtotaltransfers }}</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-4 col-6">
                                <div class="card border-0 shadow-sm radius-12 text-center p-3 h-100">
                                    <div class="icon mb-2 text-warning fs-2">
                                        <i class="bi bi-envelope-paper-fill"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold d-block">Per Day Transfers</small>
                                        <h4 class="fw-bold text-dark mb-0">{{ $MAvgtotaltransfers }}</h4>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <div class="row">

                            <!-- ================= FIRST TABLE : 1 - 16 ================= -->
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th class="fw-bold">Date</th>
                                                <th class="fw-bold text-center">Called & Mailed</th>
                                                <th class="fw-bold text-center">Other Call</th>
                                                <th class="fw-bold text-center">Transfers</th> <!-- New Column -->
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <tr>
                                                <td>01</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay1 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay1 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay1 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>02</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay2 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay2 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay2 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>03</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay3 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay3 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay3 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>04</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay4 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay4 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay4 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>05</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay5 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay5 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay5 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>06</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay6 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay6 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay6 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>07</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay7 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay7 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay7 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>08</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay8 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay8 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay8 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>09</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay9 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay9 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay9 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>10</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay10 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay10 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay10 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>11</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay11 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay11 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay11 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>12</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay12 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay12 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay12 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>13</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay13 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay13 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay13 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>14</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay14 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay14 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay14 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>15</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay15 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay15 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay15 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>16</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay16 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay16 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay16 }}</span></td>
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
                                                <th class="fw-bold text-center">Called & Mailed</th>
                                                <th class="fw-bold text-center">Other Call</th>
                                                <th class="fw-bold text-center">Transfers</th> <!-- New Column -->
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <tr>
                                                <td>17</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay17 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay17 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay17 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>18</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay18 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay18 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay18 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>19</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay19 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay19 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay19 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>20</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay20 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay20 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay20 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>21</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay21 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay21 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay21 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>22</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay22 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay22 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay22 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>23</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay23 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay23 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay23 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>24</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay24 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay24 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay24 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>25</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay25 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay25 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay25 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>26</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay26 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay26 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay26 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>27</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay27 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay27 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay27 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>28</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay28 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay28 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay28 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>29</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay29 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay29 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay29 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>30</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay30 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay30 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay30 }}</span></td>
                                            </tr>

                                            <tr>
                                                <td>31</td>
                                                <td class="text-center"><span
                                                        class="badge bg-info">{{ $tDay31 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-warning">{{ $oDay31 }}</span></td>
                                                <td class="text-center"><span
                                                        class="badge bg-success">{{ $trDay31 }}</span></td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>


                        </div>

                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-lg-6">
                <div class="card h-100 radius-8 border-0">
                    <div class="card-body p-24">
                        <h6 class="fw-bold mb-1">Total Summary (From Joining)</h6>
                        <div class="mt-24">
                            <div class="d-flex align-items-center gap-1 justify-content-between mb-44">
                                <div class="me-4">
                                    <span class="text-secondary-light fw-bold mb-12 text-xl">Total Calls</span>
                                    <h5 class="fw-semibold mb-0">{{ $MtotalCalls }}</h5>
                                </div>
                                <div id="semiCircleGauge" class="me-3"></div>
                            </div>

                            <div class="d-flex align-items-center gap-1 justify-content-between mb-44">
                                <div>
                                    <span class="text-secondary-light fw-bold mb-12 text-xl">Other Calls</span>
                                    <h5 class="fw-semibold mb-0">{{ $MotherCalls }}</h5>
                                </div>
                                <div id="areaChart"></div>
                            </div>
                            <div class="d-flex align-items-center gap-1 justify-content-between">
                                <div>
                                    <span class="text-secondary-light fw-bold mb-12 text-xl">Called & Mailed Calls</span>
                                    <h5 class="fw-semibold mb-0">{{ $McalledAndMailedCalls }}</h5>
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
@endsection
