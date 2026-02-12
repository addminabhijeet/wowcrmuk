<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\UserTimerPause;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Holiday;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function index()
    {

        return view('calendar.admin');
    }

    public function juniorUser(Request $request)
    {
        $view = $request->input('view', 'month'); // day, week, month
        $date = $request->input('date', now());

        $start = $end = Carbon::parse($date);

        switch ($view) {
            case 'day':
                $start = $start->startOfDay();
                $end = $end->endOfDay();
                break;
            case 'week':
                $start = $start->startOfWeek();
                $end = $end->endOfWeek();
                break;
            default: // month
                $start = $start->startOfMonth();
                $end = $end->endOfMonth();
                break;
        }

        return view('calendar.junior', compact('view', 'date'));
    }

    public function adminUser(Request $request)
    {
        $view = $request->input('view', 'month'); // day, week, month
        $date = $request->input('date', now());

        $start = $end = Carbon::parse($date);

        switch ($view) {
            case 'day':
                $start = $start->startOfDay();
                $end = $end->endOfDay();
                break;
            case 'week':
                $start = $start->startOfWeek();
                $end = $end->endOfWeek();
                break;
            default: // month
                $start = $start->startOfMonth();
                $end = $end->endOfMonth();
                break;
        }

        return view('calendar.admin', compact('view', 'date'));
    }

    // ✅ FullCalendar event JSON endpoint
    public function juniorEvents(Request $request)
    {
        $userId = Auth::id();

        // Fetch events sorted by latest first
        $events = UserTimerPause::where('user_id', $userId)
            ->orderBy('event_time', 'desc')  // <-- Latest events first
            ->get();

        // Define color mapping for clarity
        $labelColors = [
            'start'  => '#007bff',
            'resume' => '#28a745',
            'pause'  => '#ffc107',
            'stop'   => '#dc3545',
            'other'  => '#6c757d'
        ];

        $eventsData = $events->map(function ($event) use ($labelColors) {
            $type = strtolower($event->pause_type ?? 'other');

            return [
                'id'    => $event->id,
                'title' => ucfirst($type),
                'start' => $event->event_time,
                'allDay' => false,
                'extendedProps' => [
                    'status'            => $event->status ?? 'N/A',
                    'pause_type'        => $type,
                    'remaining_seconds' => $event->remaining_seconds ?? 0,
                    'label_color'       => $labelColors[$type] ?? $labelColors['other'],
                ]
            ];
        });

        return response()->json($eventsData);
    }

    public function allJuniorlist(Request $request)
    {
        // Fetch all users with role 'junior'
        $juniorUsers = User::where('role', 'junior')
            ->where('is_deleted', 0)
            ->get();


        // Pass users to the view
        return view('calendar.alljuniorlist', compact('juniorUsers'));
    }

    public function allTrainerlist(Request $request)
    {
        // Fetch all users with role 'trainer'
        $trainerUsers = User::where('role', 'trainer')
            ->where('is_deleted', 0)
            ->get();

        // Pass users to the view
        return view('calendar.alltrainerlist', compact('trainerUsers'));
    }

    public function allAccountantlist(Request $request)
    {
        // Fetch all users with role 'accountant'
        $accountantUsers = User::where('role', 'accountant')
            ->where('is_deleted', 0)
            ->get();

        // Pass users to the view
        return view('calendar.allaccountantlist', compact('accountantUsers'));
    }

    public function allAdminlist(Request $request)
    {
        // Fetch all users with role 'trainer'
        $adminUsers = User::where('role', 'admin')
            ->where('is_deleted', 0)
            ->get();

        // Pass users to the view
        return view('calendar.alladminlist', compact('adminUsers'));
    }

    public function allSeniorlist(Request $request)
    {
        // Fetch all users with role 'senior'
        $seniorUsers = User::where('role', 'senior')
            ->where('is_deleted', 0)
            ->get();

        // Pass users to the view
        return view('calendar.allseniorlist', compact('seniorUsers'));
    }

    public function alljuniorUser(Request $request, $user_id)
    {
        $view = $request->input('view', 'month'); // day, week, month
        $date = $request->input('date', now());

        $start = $end = Carbon::parse($date);

        switch ($view) {
            case 'day':
                $start = $start->startOfDay();
                $end = $end->endOfDay();
                break;
            case 'week':
                $start = $start->startOfWeek();
                $end = $end->endOfWeek();
                break;
            default: // month
                $start = $start->startOfMonth();
                $end = $end->endOfMonth();
                break;
        }

        // ✅ Fetch the junior user details
        $junior = User::where('id', $user_id)
            ->where('is_deleted', 0)
            ->first();


        if (!$junior) {
            abort(404, 'Junior not found');
        }

        $events = UserTimerPause::where('user_id', $user_id)
            ->whereBetween('event_time', [$start, $end])
            ->orderBy('event_time', 'asc')
            ->get();

        // ✅ Pass $junior to view
        return view('calendar.alljunior', compact('events', 'view', 'date', 'junior'));
    }

    public function allseniorUser(Request $request, $user_id)
    {
        $view = $request->input('view', 'month'); // day, week, month
        $date = $request->input('date', now());

        $start = $end = Carbon::parse($date);

        switch ($view) {
            case 'day':
                $start = $start->startOfDay();
                $end = $end->endOfDay();
                break;
            case 'week':
                $start = $start->startOfWeek();
                $end = $end->endOfWeek();
                break;
            default: // month
                $start = $start->startOfMonth();
                $end = $end->endOfMonth();
                break;
        }

        $user = User::where('is_deleted', 0)
            ->findOrFail($user_id);


        return view('calendar.allsenior', compact('view', 'date', 'user'));
    }

    public function allaccountantUser(Request $request, $user_id)
    {
        $view = $request->input('view', 'month'); // day, week, month
        $date = $request->input('date', now());

        $start = $end = Carbon::parse($date);

        switch ($view) {
            case 'day':
                $start = $start->startOfDay();
                $end = $end->endOfDay();
                break;
            case 'week':
                $start = $start->startOfWeek();
                $end = $end->endOfWeek();
                break;
            default: // month
                $start = $start->startOfMonth();
                $end = $end->endOfMonth();
                break;
        }

        // ✅ Fetch the accountant user details
        $accountant = User::where('id', $user_id)
            ->where('is_deleted', 0)
            ->first();


        if (!$accountant) {
            abort(404, 'Accountant not found');
        }

        $events = UserTimerPause::where('user_id', $user_id)
            ->whereBetween('event_time', [$start, $end])
            ->orderBy('event_time', 'asc')
            ->get();

        // ✅ Pass $accountant to view
        return view('calendar.allaccountant', compact('events', 'view', 'date', 'accountant'));
    }

    public function alltrainerUser(Request $request, $user_id)
    {
        $view = $request->input('view', 'month'); // day, week, month
        $date = $request->input('date', now());

        $start = $end = Carbon::parse($date);

        switch ($view) {
            case 'day':
                $start = $start->startOfDay();
                $end = $end->endOfDay();
                break;
            case 'week':
                $start = $start->startOfWeek();
                $end = $end->endOfWeek();
                break;
            default: // month
                $start = $start->startOfMonth();
                $end = $end->endOfMonth();
                break;
        }

        // ✅ Fetch the trainer user details
        $trainer = User::where('id', $user_id)
            ->where('is_deleted', 0)
            ->first();


        if (!$trainer) {
            abort(404, 'Trainer not found');
        }

        $events = UserTimerPause::where('user_id', $user_id)
            ->whereBetween('event_time', [$start, $end])
            ->orderBy('event_time', 'asc')
            ->get();

        // ✅ Pass $trainer to view
        return view('calendar.alltrainer', compact('events', 'view', 'date', 'trainer'));
    }

    public function getAllSeniorEvents(Request $request, $userId)
    {

        // Fetch events sorted by latest first
        $events = UserTimerPause::where('user_id', $userId)
            ->orderBy('event_time', 'desc')  // <-- Latest events first
            ->get();

        // Define color mapping for clarity
        $labelColors = [
            'start'  => '#007bff',
            'resume' => '#28a745',
            'pause'  => '#ffc107',
            'stop'   => '#dc3545',
            'other'  => '#6c757d'
        ];

        $eventsData = $events->map(function ($event) use ($labelColors) {
            $type = strtolower($event->pause_type ?? 'other');

            return [
                'id'    => $event->id,
                'title' => ucfirst($type),
                'start' => $event->event_time,
                'allDay' => false,
                'extendedProps' => [
                    'status'            => $event->status ?? 'N/A',
                    'pause_type'        => $type,
                    'remaining_seconds' => $event->remaining_seconds ?? 0,
                    'label_color'       => $labelColors[$type] ?? $labelColors['other'],
                ]
            ];
        });

        return response()->json($eventsData);
    }

    public function getAllAccountantEvents(Request $request, $userId)
    {
        // ✅ Fetch the junior user
        $junior = User::where('id', $userId)
            ->where('role', 'junior')
            ->where('is_deleted', 0)
            ->first();


        if (!$junior) {
            return response()->json(['error' => 'Junior user not found'], 404);
        }

        // ✅ Fetch all events for this junior (latest first)
        $events = UserTimerPause::where('user_id', $junior->id)
            ->orderBy('event_time', 'desc')
            ->get();

        // ✅ Define color mapping for clarity
        $labelColors = [
            'start'  => '#007bff',
            'resume' => '#28a745',
            'pause'  => '#ffc107',
            'stop'   => '#dc3545',
            'other'  => '#6c757d'
        ];

        // ✅ Format event data for FullCalendar
        $eventsData = $events->map(function ($event) use ($labelColors) {
            $type = strtolower($event->pause_type ?? 'other');

            return [
                'id'    => $event->id,
                'title' => ucfirst($type),
                'start' => $event->event_time,
                'allDay' => false,
                'extendedProps' => [
                    'status'            => $event->status ?? 'N/A',
                    'pause_type'        => $type,
                    'remaining_seconds' => $event->remaining_seconds ?? 0,
                    'label_color'       => $labelColors[$type] ?? $labelColors['other'],
                ]
            ];
        });

        return response()->json($eventsData);
    }

    public function getAllTrainerEvents(Request $request, $userId)
    {
        // ✅ Fetch the junior user
        $junior = User::where([
            ['id', '=', $userId],
            ['role', '=', 'junior'],
            ['is_deleted', '=', 0],
        ])->first();


        if (!$junior) {
            return response()->json(['error' => 'Junior user not found'], 404);
        }

        // ✅ Fetch all events for this junior (latest first)
        $events = UserTimerPause::where('user_id', $junior->id)
            ->orderBy('event_time', 'desc')
            ->get();

        // ✅ Define color mapping for clarity
        $labelColors = [
            'start'  => '#007bff',
            'resume' => '#28a745',
            'pause'  => '#ffc107',
            'stop'   => '#dc3545',
            'other'  => '#6c757d'
        ];

        // ✅ Format event data for FullCalendar
        $eventsData = $events->map(function ($event) use ($labelColors) {
            $type = strtolower($event->pause_type ?? 'other');

            return [
                'id'    => $event->id,
                'title' => ucfirst($type),
                'start' => $event->event_time,
                'allDay' => false,
                'extendedProps' => [
                    'status'            => $event->status ?? 'N/A',
                    'pause_type'        => $type,
                    'remaining_seconds' => $event->remaining_seconds ?? 0,
                    'label_color'       => $labelColors[$type] ?? $labelColors['other'],
                ]
            ];
        });

        return response()->json($eventsData);
    }

    public function getAllJuniorEvents(Request $request, $userId)
    {
        // ✅ Fetch the junior user
        $junior = User::where('id', $userId)
            ->where('role', 'junior')
            ->where('is_deleted', 0)
            ->first();


        if (!$junior) {
            return response()->json(['error' => 'Junior user not found'], 404);
        }

        // ✅ Fetch all events for this junior (latest first)
        $events = UserTimerPause::where('user_id', $junior->id)
            ->orderBy('event_time', 'desc')
            ->get();

        // ✅ Define color mapping for clarity
        $labelColors = [
            'start'  => '#007bff',
            'resume' => '#28a745',
            'pause'  => '#ffc107',
            'stop'   => '#dc3545',
            'other'  => '#6c757d'
        ];

        // ✅ Format event data for FullCalendar
        $eventsData = $events->map(function ($event) use ($labelColors) {
            $type = strtolower($event->pause_type ?? 'other');

            return [
                'id'    => $event->id,
                'title' => ucfirst($type),
                'start' => $event->event_time,
                'allDay' => false,
                'extendedProps' => [
                    'status'            => $event->status ?? 'N/A',
                    'pause_type'        => $type,
                    'remaining_seconds' => $event->remaining_seconds ?? 0,
                    'label_color'       => $labelColors[$type] ?? $labelColors['other'],
                ]
            ];
        });

        return response()->json($eventsData);
    }


    public function updateStatus(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:working,holiday,present,absent'
        ]);

        $attendance = Attendance::updateOrCreate(
            ['user_id' => Auth::id(), 'date' => $request->date],
            ['status' => $request->status]
        );

        return response()->json(['success' => true, 'status' => $attendance->status]);
    }

    public function seniorUser(Request $request)
    {
        $view = $request->input('view', 'month');
        $date = $request->input('date', now());

        $start = $end = Carbon::parse($date);

        switch ($view) {
            case 'day':
                $start = $start->startOfDay();
                $end = $end->endOfDay();
                break;
            case 'week':
                $start = $start->startOfWeek();
                $end = $end->endOfWeek();
                break;
            default: // month
                $start = $start->startOfMonth();
                $end = $end->endOfMonth();
                break;
        }

        return view('calendar.senior', compact('view', 'date'));
    }

    public function SeniorEvents(Request $request)
    {
        $userId = Auth::id();

        // Fetch events sorted by latest first
        $events = UserTimerPause::where('user_id', $userId)
            ->orderBy('event_time', 'desc')  // <-- Latest events first
            ->get();

        // Define color mapping for clarity
        $labelColors = [
            'start'  => '#007bff',
            'resume' => '#28a745',
            'pause'  => '#ffc107',
            'stop'   => '#dc3545',
            'other'  => '#6c757d'
        ];

        $eventsData = $events->map(function ($event) use ($labelColors) {
            $type = strtolower($event->pause_type ?? 'other');

            return [
                'id'    => $event->id,
                'title' => ucfirst($type),
                'start' => $event->event_time,
                'allDay' => false,
                'extendedProps' => [
                    'status'            => $event->status ?? 'N/A',
                    'pause_type'        => $type,
                    'remaining_seconds' => $event->remaining_seconds ?? 0,
                    'label_color'       => $labelColors[$type] ?? $labelColors['other'],
                ]
            ];
        });

        return response()->json($eventsData);
    }

    public function setting(Request $request)
    {
        $holidays = Holiday::pluck('is_holiday', 'holiday_date')->toArray();
        $today = now()->toDateString(); // YYYY-MM-DD

        return view('calendar.setting', compact('holidays', 'today'));
    }

    public function getHolidaysByMonth(Request $request)
    {
        $year  = $request->year;
        $month = $request->month;

        $holidays = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->pluck('is_holiday', 'holiday_date')
            ->toArray();

        return response()->json($holidays);
    }

    public function saveHoliday(Request $request)
    {
        Holiday::updateOrInsert(
            ['holiday_date' => $request->date],
            ['is_holiday' => (bool) $request->is_holiday]
        );

        return response()->json(['status' => 'success']);
    }
}
