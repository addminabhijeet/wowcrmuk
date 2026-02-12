@extends('layout.layout')

@php
    $title = 'Calendar Setting';
    $role = auth()->user()->role ?? '';
    if ($role === 'admin') {
        $subTitle = 'Super Admin';
    } elseif ($role === 'operation') {
        $subTitle = 'Operation Manager';
    } else {
        $subTitle = 'role';
    }
@endphp

@section('content')
    <div id="pdfContent">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="calendarPicker" class="fw-bold mb-1">
                    Select Month & Year
                </label>

                <input type="month" id="calendarPicker" class="form-control">
            </div>
        </div>

        <div class="row gy-4 mt-1">
            <div class="col-12">
                <div class="card h-100 border-0 shadow-sm radius-12">
                    <div class="card-body p-4">

                        <div class="row">
                            <!-- ================= FIRST TABLE : 1 - 16 ================= -->
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th class="fw-bold">Date</th>
                                                <th class="fw-bold text-center">Holiday</th>
                                            </tr>
                                        </thead>
                                        <tbody id="firstHalf"></tbody>
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
                                                <th class="fw-bold text-center">Holiday</th>
                                            </tr>
                                        </thead>
                                        <tbody id="secondHalf"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        let HOLIDAYS = @json($holidays);
    </script>

    <script>
        const TODAY_DATE = "{{ $today }}";
    </script>



    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <script>
        $('#calendarPicker').datepicker({
            format: "dd MM yyyy",
            autoclose: true,
            todayHighlight: true
        }).on('changeDate', function(e) {

            const year = e.date.getFullYear();
            const month = e.date.getMonth() + 1;

            loadHolidays(year, month, () => {
                generateDates(e.date);
            });

        });
    </script>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date(TODAY_DATE); // from controller
            const monthValue = TODAY_DATE.slice(0, 7);

            const picker = document.getElementById('calendarPicker');
            picker.value = monthValue;

            generateDates(today);

            picker.addEventListener('change', function() {
                const [year, month] = this.value.split('-');

                loadHolidays(year, month, () => {
                    generateDates(new Date(year, month - 1, 1));
                });
            });

        });
    </script>

    <script>
        function generateDates(selectedDate) {
            const month = selectedDate.getMonth() + 1;
            const year = selectedDate.getFullYear();
            const daysInMonth = new Date(year, month, 0).getDate();

            const firstHalf = document.getElementById('firstHalf');
            const secondHalf = document.getElementById('secondHalf');

            firstHalf.innerHTML = '';
            secondHalf.innerHTML = '';

            for (let day = 1; day <= daysInMonth; day++) {
                const fullDate = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const checked = HOLIDAYS[fullDate] == 1 ? 'checked' : '';

                const row = `
            <tr>
                <td>${String(day).padStart(2, '0')}</td>
                <td class="text-center">
                    <div class="form-check d-inline-block ms-2">
                        <input class="form-check-input holiday-checkbox"
                               type="checkbox"
                               data-date="${fullDate}"
                               ${checked}>
                    </div>
                </td>
            </tr>
        `;

                day <= 16 ?
                    firstHalf.insertAdjacentHTML('beforeend', row) :
                    secondHalf.insertAdjacentHTML('beforeend', row);
            }

            attachCheckboxEvents();
        }

        function attachCheckboxEvents() {
            document.querySelectorAll('.holiday-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {

                    fetch('/holiday/save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            date: this.dataset.date,
                            is_holiday: this.checked ? 1 : 0
                        })
                    });


                });
            });
        }
    </script>

    <script>
        function loadHolidays(year, month, callback) {
            fetch('/holiday/by-month', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({
                        year,
                        month
                    })
                })
                .then(res => res.json())
                .then(data => {
                    HOLIDAYS = data; // overwrite holidays safely
                    callback();
                });
        }
    </script>
@endsection
