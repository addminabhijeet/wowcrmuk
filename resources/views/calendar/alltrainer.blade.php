@extends('layout.layout')

@php
    $title = 'Users -> Trainer';
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
    <div class="row gy-4">
        <div class="col-12">
            <div class="card h-100 p-0">
                <div class="card-body p-24">

                    <!-- Legend -->
                    <div class="calendar-legend d-flex align-items-center mb-3">
                        <div class="legend-item d-flex align-items-center me-3">
                            <div class="legend-color"
                                style="background-color: rgba(220,50,50,0.3); width: 20px; height: 20px; border-radius: 4px; margin-right: 6px;">
                            </div>
                            <span>Less than 8h Work</span>
                        </div>
                        <div class="legend-item d-flex align-items-center">
                            <div class="legend-color"
                                style="background-color: rgba(0,123,255,0.08); width: 20px; height: 20px; border-radius: 4px; margin-right: 6px;">
                            </div>
                            <span>Completed ≥8h</span>
                        </div>
                    </div>

                    <!-- Calendar -->
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header py-16 px-24 border-0">
                    <h1 class="modal-title fs-5" id="eventModalLabel">
                        Events on <span id="modalDate"></span>
                    </h1>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-24" id="modalBody"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const modalEl = document.getElementById('eventModal');
            const modal = new bootstrap.Modal(modalEl);
            const modalBody = document.getElementById('modalBody');
            const modalDate = document.getElementById('modalDate');

            function formatTime(seconds) {
                seconds = Math.floor(seconds);
                const hrs = Math.floor(seconds / 3600);
                const mins = Math.floor((seconds % 3600) / 60);
                const secs = seconds % 60;
                const hh = String(hrs).padStart(2, '0');
                const mm = String(mins).padStart(2, '0');
                const ss = String(secs).padStart(2, '0');
                return `${hh}:${mm}:${ss}`;
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: "{{ route('calendar.allTrainerEvents', ['userId' => $trainer->id]) }}",
                displayEventTime: false,
                displayEventEnd: false,
                eventContent: function() {
                    return {
                        domNodes: []
                    };
                },
                eventDidMount: function(info) {
                    info.el.remove();
                    const cell = info.el.closest('.fc-daygrid-day');
                    if (cell) cell.classList.add('has-event');
                },
                datesSet: function() {
                    highlightUnderworkedDays(calendar);
                },
                dateClick: function(info) {
                    modalDate.textContent = info.dateStr;
                    modalBody.innerHTML = '';

                    const eventsOnDate = calendar.getEvents().filter(e => e.startStr.slice(0, 10) ===
                        info.dateStr);

                    // Sort earliest first
                    eventsOnDate.sort((a, b) => new Date(a.start) - new Date(b.start));

                    if (eventsOnDate.length > 0) {
                        let totalBreakSec = 0;
                        let totalWorkSec = 0;
                        let lastPauseTime = null;
                        let tableRows = '';

                        const chronologicalEvents = [...eventsOnDate];

                        // Find the first 'Start' event
                        const startEvent = chronologicalEvents.find(ev => ev.title.toLowerCase() ===
                            'start');
                        const startTime = startEvent && startEvent.start ?
                            new Date(startEvent.start).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            }) :
                            'null';
                        // Find the last 'Logout' event
                        const logoutEvent = [...chronologicalEvents].reverse().find(ev => ev.title
                            .toLowerCase() === 'logout');
                        const endTime = logoutEvent && logoutEvent.start ?
                            new Date(logoutEvent.start).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            }) :
                            'null';

                        // --- Active work calculation: only after Start, include Login + Logout ---
                        let activeWorkSec = 0;
                        let startSeen = false;

                        for (let i = 0; i < chronologicalEvents.length; i++) {
                            const event = chronologicalEvents[i];
                            const title = (event.title || '').toLowerCase();

                            if (title === 'start') {
                                startSeen = true; // Start tracking from here
                            }

                            if (!startSeen) continue; // Ignore everything before 'Start'

                            // Active events: login, logout, start, resume, running
                            if (['login', 'logout', 'start', 'resume', 'running'].includes(title)) {
                                let durationSec = 0;
                                const eTime = new Date(event.start);

                                if (i < chronologicalEvents.length - 1) {
                                    const nextTime = new Date(chronologicalEvents[i + 1].start);
                                    durationSec = Math.max(0, (nextTime - eTime) / 1000);
                                } else if (event.end) {
                                    durationSec = Math.max(0, (new Date(event.end) - eTime) / 1000);
                                }

                                activeWorkSec += durationSec;
                            }
                        }

                        // Build table rows (original code)
                        for (let i = 0; i < chronologicalEvents.length; i++) {
                            const event = chronologicalEvents[i];
                            const eTime = new Date(event.start);
                            const type = (event.extendedProps.pause_type || '').toLowerCase();
                            let breakTime = 0,
                                workTime = 0;

                            if (type === 'inactive') lastPauseTime = eTime;
                            else if ((type === 'resume' || type === 'running') && lastPauseTime) {
                                breakTime = (eTime - lastPauseTime) / 1000;
                                totalBreakSec += breakTime;
                                lastPauseTime = null;
                            }

                            if (i > 0) {
                                let prevTime = new Date(chronologicalEvents[i - 1].start);
                                workTime = (eTime - prevTime) / 1000;
                                if (workTime < 0) workTime = 0;
                                totalWorkSec += workTime;
                            }

                            let durationSec = 0;
                            if (i < chronologicalEvents.length - 1) {
                                const nextTime = new Date(chronologicalEvents[i + 1].start);
                                durationSec = Math.max(0, (nextTime - eTime) / 1000);
                            } else if (event.end) {
                                durationSec = Math.max(0, (new Date(event.end) - eTime) / 1000);
                            }

                            tableRows += `
<tr>
    <td>${event.title}</td>
    <td>${eTime.toLocaleTimeString([], { hour:'2-digit', minute:'2-digit', second:'2-digit' })}${i < chronologicalEvents.length - 1 ? ' - ' + new Date(chronologicalEvents[i + 1].start).toLocaleTimeString([], { hour:'2-digit', minute:'2-digit', second:'2-digit' }) : ''}</td>
    <td>${formatTime(durationSec)}</td>
</tr>`;
                        }

                        const targetSec = 8 * 3600;
                        const elapsedSec = activeWorkSec; // only active work
                        const remainingSec = Math.max(targetSec - activeWorkSec, 0);
                        const extraSec = Math.max(activeWorkSec - targetSec, 0); // extra time
                        const completed = activeWorkSec >= targetSec ? "✅ Yes" : "❌ No";

                        tableRows += `
<tr class="fw-bold text-success">
    <td colspan="2" class="text-end">Total</td>
    <td>${formatTime(elapsedSec)}</td>
</tr>
<tr class="fw-bold text-primary">
    <td colspan="2" class="text-end">Elapsed / Remaining</td>
    <td colspan="2">${formatTime(elapsedSec)} / ${formatTime(remainingSec)}</td>
</tr>
<tr class="fw-bold text-warning">
    <td colspan="2" class="text-end">Extra Time</td>
    <td>${formatTime(extraSec)}</td>
</tr>`;

                        modalBody.innerHTML = `
<div class="summary border-bottom pb-3 mb-3">
    <h5 class="fw-semibold text-success">Summary</h5>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <strong>8 Hours Completed:</strong>
            <span class="badge ${elapsedSec >= targetSec ? 'bg-success' : 'bg-danger'} fs-6">${completed}</span>
        </div>
        <div>
            <strong>Start Time:</strong>
            <span class="badge fs-6 bg-danger">${startTime}</span>
        </div>
        <div>
            <strong>End Time:</strong>
            <span class="badge fs-6 bg-danger">${endTime}</span>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-sm table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Event</th>
                <th>Time</th>
                <th>Duration</th>
            </tr>
        </thead>
        <tbody>${tableRows}</tbody>
    </table>
</div>

<div class="totals mt-3">
    <div class="d-flex justify-content-between fw-bold text-success">
        <span>Total Work Time:</span>
        <span>${formatTime(elapsedSec)}</span>
    </div>
    <div class="d-flex justify-content-between fw-bold text-primary">
        <span>Elapsed / Remaining:</span>
        <span>${formatTime(elapsedSec)} / ${formatTime(remainingSec)}</span>
    </div>
    <div class="d-flex justify-content-between fw-bold text-warning">
        <span>Extra Time:</span>
        <span>${formatTime(extraSec)}</span>
    </div>
</div>
`;

                        // --- Merge consecutive duplicates (original code)
                        const tbody = modalBody.querySelector('tbody');
                        const allRows = Array.from(tbody.querySelectorAll('tr'));
                        let mergedRows = [];

                        for (let i = 0; i < allRows.length; i++) {
                            const curr = allRows[i];
                            if (!curr || curr.classList.contains('fw-bold')) continue;

                            const currEvent = curr.cells[0]?.textContent.trim();
                            let currTime = curr.cells[1]?.textContent.trim();
                            let currDuration = parseTimeToSeconds(curr.cells[2]?.textContent.trim());

                            let firstTime = currTime.split(' - ')[0];
                            let lastTime = currTime.split(' - ').pop();

                            let j = i + 1;
                            while (j < allRows.length) {
                                const next = allRows[j];
                                if (!next || next.classList.contains('fw-bold')) break;

                                const nextEvent = next.cells[0]?.textContent.trim();
                                const nextDuration = parseTimeToSeconds(next.cells[2]?.textContent
                                .trim());
                                const nextTime = next.cells[1]?.textContent.trim().split(' - ').pop();

                                if (nextEvent === currEvent) {
                                    lastTime = nextTime;
                                    currDuration += nextDuration;
                                    j++;
                                } else break;
                            }

                            const mergedRow = document.createElement('tr');
                            mergedRow.innerHTML = `
<td>${currEvent}</td>
<td>${firstTime} - ${lastTime}</td>
<td>${formatTime(currDuration)}</td>`;
                            mergedRows.push(mergedRow);
                            i = j - 1;
                        }

                        tbody.innerHTML = '';
                        mergedRows.forEach(r => tbody.appendChild(r));

                        modal.show();

                    } else {
                        modalBody.innerHTML =
                            '<p class="text-center text-muted">No events on this date.</p>';
                    }

                    function parseTimeToSeconds(timeStr) {
                        if (!timeStr) return 0;
                        const parts = timeStr.split(':').map(Number);
                        return parts[0] * 3600 + parts[1] * 60 + parts[2];
                    }
                }


            });

            calendar.render()

            function highlightUnderworkedDays(calendar) {
                const allEvents = calendar.getEvents();
                const grouped = {};
                allEvents.forEach(ev => {
                    const dateKey = new Date(ev.start).toISOString().split('T')[0];
                    if (!grouped[dateKey]) grouped[dateKey] = [];
                    grouped[dateKey].push(ev);
                });

                Object.keys(grouped).forEach(dateStr => {
                    const dayEvents = grouped[dateStr];
                    let totalBreakSec = 0,
                        lastPauseTime = null;

                    dayEvents.forEach(ev => {
                        const eTime = new Date(ev.start);
                        const pauseType = (ev.extendedProps.pause_type || '').toLowerCase();
                        if (pauseType === 'inactive') lastPauseTime = eTime;
                        else if (pauseType === 'resume' && lastPauseTime) {
                            totalBreakSec += (eTime - lastPauseTime) / 1000;
                            lastPauseTime = null;
                        }
                    });

                    const startTime = new Date(dayEvents[0].start);
                    const endTime = new Date(dayEvents[dayEvents.length - 1].start);
                    const totalDaySec = (endTime - startTime) / 1000;
                    const totalWorkSec = totalDaySec - totalBreakSec;

                    const cell = calendarEl.querySelector(`.fc-daygrid-day[data-date='${dateStr}']`);
                    if (cell) {
                        if (totalWorkSec < 8 * 3600) cell.style.backgroundColor = 'rgba(220,50,50,0.3)';
                        else cell.style.backgroundColor = 'rgba(0,123,255,0.08)';
                    }
                });
            }


            function generateDailyPDF(events, dateStr) {
                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF();

                doc.setFontSize(16);
                doc.text(`Daily Report: ${dateStr}`, 10, 20);

                let y = 30;
                events.forEach(ev => {
                    const time = new Date(ev.start).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    doc.setFontSize(12);
                    doc.text(`${time} - ${ev.title} (${ev.extendedProps.status})`, 10, y);
                    y += 10;
                });

                doc.save(`Daily_Report_${dateStr}.pdf`);
            }

            function generateMonthlyPDF(events) {
                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF();

                doc.setFontSize(16);
                doc.text(`Monthly Report`, 10, 20);

                let y = 30;
                events.forEach(ev => {
                    const date = new Date(ev.start).toLocaleDateString();
                    const time = new Date(ev.start).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    doc.setFontSize(12);
                    doc.text(`${date} ${time} - ${ev.title} (${ev.extendedProps.status})`, 10, y);
                    y += 10;
                    if (y > 280) {
                        doc.addPage();
                        y = 20;
                    }
                });

                doc.save(`Monthly_Report.pdf`);
            }

        });
    </script>

    <style>
        .calendar-legend {
            font-size: 14px;
        }

        .fc-event,
        .fc-daygrid-event,
        .fc-event-dot,
        .fc-event-main,
        .fc-daygrid-day-events,
        .fc-daygrid-event-harness,
        .fc-daygrid-event-harness-abs {
            display: none !important;
        }

        .fc-daygrid-day-frame {
            min-height: 60px;
            padding: 4px;
            display: block !important;
        }

        .fc-day-today {
            background-color: rgba(0, 123, 255, 0.1) !important;
        }

        .fc-theme-standard td,
        .fc-theme-standard th {
            border: 1px solid #e5e5e5 !important;
        }

        .fc-daygrid-day.has-event {
            transition: background-color 0.2s ease;
        }

        .fc-daygrid-day.has-event:hover {
            background-color: rgba(0, 123, 255, 0.15);
        }

        .fc-daygrid-day:hover {
            cursor: pointer;
            background-color: rgba(0, 0, 0, 0.02);
        }

        .fc .fc-button {
            padding: 0.2em 0.65em !important;
        }
    </style>
@endsection
