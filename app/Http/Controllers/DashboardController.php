<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\UserTimerLog;
use App\Models\UserTimerPause;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Models\TimerSetting;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use App\Models\SmtpSetting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\GoogleSheetData;
use App\Models\Holiday;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class DashboardController extends Controller
{
    /**
     * Get Timer Settings (with fallback defaults if DB is empty)
     */
    private function getTimerSettings()
    {
        $setting = TimerSetting::first();
        return [
            'work_day_seconds' => $setting ? $setting->work_day_seconds : (9 * 3600), // 9 hours default
            'daily_base_time'  => $setting ? $setting->daily_base_time : '07:00:00',  // 8 PM default
        ];
    }


    public function index()
    {
        // Only users who are not deleted
        $users = User::where('is_deleted', 0)->get();

        // Only new (not deleted) users created in the last 30 days
        $newUsers = User::where('is_deleted', 0)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->get();

        return view('dashboard.admin', compact('users', 'newUsers'));
    }

    public function adminnotification()
    {
        $users = User::where('is_deleted', 0)->get();

        $admin = Auth::user();

        $notifications = Notification::with('candidate')
            ->where('notifiable_role', 'admin')
            ->where('notifiable_id', $admin->id)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return view('notice.veiwdetails', compact('users', 'notifications'));
    }

    public function latestNotification()
    {
        $admin = Auth::user();

        // unread count should stay as is
        $unreadCount = Notification::where('notifiable_id', $admin->id)
            ->where('notifiable_role', 'admin')
            ->whereNull('read_at')
            ->count();

        // NEW: Fetch latest UNREAD notification only.
        $notification = Notification::with(['user', 'candidate'])
            ->where('notifiable_id', $admin->id)
            ->where('notifiable_role', 'admin')
            ->whereNull('read_at')                // ← added condition
            ->latest('id')
            ->first();

        if (!$notification) {
            return response()->json([
                'status' => false,
                'unread_count' => $unreadCount
            ]);
        }

        // Prepare the HTML
        $html = view('notice.partials.single-notification', [
            'msg' => $notification->data,
            'userName' => $notification->user->name ?? 'Unknown User',
            'userEmail' => $notification->user->email ?? '',
            'candidateName' => $notification->candidate->Name ?? null,
            'candidateEmail' => $notification->candidate->Email_Address ?? null,
            'candidatePhone' => $notification->candidate->Phone_Number ?? null,
            'candidateCourse' => $notification->candidate->Course ?? null,
        ])->render();

        return response()->json([
            'status' => true,
            'html' => $html,
            'id' => $notification->id,
            'created_at' => $notification->created_at->timestamp,
            'unread_count' => $unreadCount
        ]);
    }





    public function markAllRead(Request $request)
    {
        $admin = Auth::user();

        // Mark all unread notifications as read
        Notification::where('notifiable_id', $admin->id)
            ->where('notifiable_role', 'admin')
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now()
            ]);

        // Get the latest notification to store its ID/time
        $latest = Notification::where('notifiable_id', $admin->id)
            ->where('notifiable_role', 'admin')
            ->latest('updated_at') // or 'created_at'
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Marked all as read',
            'unread_count' => 0,
            'latest_id' => $latest->id ?? null,
            'latest_time' => $latest?->created_at?->timestamp
        ]);
    }

    public function junior()
    {
        // Fetch timer settings
        $settings = TimerSetting::first();
        if (!$settings) {
            return response()->json(['error' => 'Timer settings not configured'], 500);
        }
        $workDaySeconds = $settings->work_day_seconds;

        $user  = Auth::user();
        $createdByKey = "{$user->id}|junior";
        $timer = UserTimerLog::where('user_id', $user->id)->latest()->first();

        $remaining_seconds = $workDaySeconds;
        $elapsed_seconds   = 0;
        $status            = 'running';
        $button_status     = 1; // default to show if no timer exists

        if ($timer) {
            $remaining_seconds = $timer->remaining_seconds;
            $elapsed_seconds   = $workDaySeconds - $remaining_seconds;
            $status            = $timer->status;
            $button_status     = $timer->button_status ?? 1;
        }
        $juniorUser = $user;

        $selectedDate  = date('Y-m-d');
        $selectedMonth = date('Y-m', strtotime($selectedDate));
        [$year, $month] = explode('-', $selectedMonth);

        $holidayDates = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where('is_holiday', 1)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Handle multiple targets and target_dates (e.g., "14|15|17" and "2025-09|2025-10|2025-11")
        $targetValues = array_map('trim', explode('|', $juniorUser->target ?? ''));
        $targetDates  = array_map('trim', explode('|', $juniorUser->target_date ?? ''));

        // Find index of matching month (e.g., "2025-10")
        $targetIndex = null;
        foreach ($targetDates as $index => $date) {
            // Accept both "YYYY-MM" and full date "YYYY-MM-DD"
            $monthPart = preg_match('/^\d{4}-\d{2}$/', $date)
                ? $date
                : Carbon::parse($date)->format('Y-m');

            if ($monthPart === $selectedMonth) {
                $targetIndex = $index;
                break;
            }
        }

        // Use the matching month's target, else fallback to first or 0
        $targetGiven = isset($targetValues[$targetIndex])
            ? (int) $targetValues[$targetIndex]
            : ((int) ($targetValues[0] ?? 0));

        // Calculate Days Left (based on matched target_date entry)
        $matchedDate = $targetDates[$targetIndex] ?? null;

        if ($matchedDate) {
            // Handle "YYYY-MM" (month only) or full date
            if (preg_match('/^\d{4}-\d{2}$/', $matchedDate)) {
                $carbonDate = Carbon::parse($matchedDate . '-01')->endOfMonth();
            } else {
                $carbonDate = Carbon::parse($matchedDate);
            }

            $diff     = now()->floatDiffInDays($carbonDate, false);
            $daysLeft = max(0, ceil($diff));
        } else {
            $daysLeft = 0;
        }

        $targetAchieved = GoogleSheetData::whereRaw("created_by REGEXP '{$juniorUser->id}\\\\|junior'")
            ->whereRaw("created_by REGEXP '\\\\|senior'")
            ->whereRaw("created_by REGEXP '\\\\|accountant'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', (int) $month)
            ->sum('Amount');


        $targetYetToAchieve = max(0, $targetGiven - $targetAchieved);

        // --- Calculate Present / Absent / Working / Non-working days ---
        $events = UserTimerPause::where('user_id', $juniorUser->id)
            ->whereYear('event_time', $year)
            ->whereMonth('event_time', $month)
            ->orderBy('event_time', 'asc')
            ->get();

        // Group events by date
        $groupedEvents = $events->groupBy(function ($event) {
            return Carbon::parse($event->event_time)->format('Y-m-d');
        });

        // Determine all days in the selected month
        $startOfMonth = Carbon::create($year, $month, 1);
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();
        $daysInMonth  = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $presentDays     = 0;
        $halfDays        = 0;
        $absentDays      = 0;
        $workingDays     = 0;
        $nonWorkingDays  = 0;

        $today = now()->startOfDay(); // 🔒 block today until end of day

        // Loop through each day
        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

            // Skip today completely
            if ($day->equalTo($today)) {
                continue;
            }

            $dailyEvents = $groupedEvents->get($dateStr, collect());

            // Consider only Saturday/Sunday or holidays as non-working
            if ($day->isWeekend() || in_array($dateStr, $holidayDates)) {
                $nonWorkingDays++;
                continue;
            }

            // Working day
            $workingDays++;

            if ($dailyEvents->isEmpty()) {
                $absentDays++;
                continue;
            }

            // Auto-present rule
            if ($dailyEvents->contains(fn($e) => strtolower($e->pause_type) === 'start')) {
                $presentDays++;
                continue;
            }

            $sorted = $dailyEvents->sortBy('event_time')->values();

            $startSeen       = false;
            $activeWorkSec   = 0;
            $totalBreakSec   = 0;
            $lastPauseTime   = null;

            for ($i = 0; $i < $sorted->count(); $i++) {
                $event      = $sorted[$i];
                $title      = strtolower($event->status ?? '');
                $pauseType  = strtolower($event->pause_type ?? '');
                $eventName  = $title ?: $pauseType;
                $eventTime  = Carbon::parse($event->event_time);

                if ($eventName === 'start') {
                    $startSeen = true;
                }

                if (!$startSeen) continue;

                if ($pauseType === 'inactive') {
                    $lastPauseTime = $eventTime;
                } elseif (in_array($pauseType, ['resume', 'running']) && $lastPauseTime) {
                    $totalBreakSec += $eventTime->diffInSeconds($lastPauseTime);
                    $lastPauseTime = null;
                }

                if ($i < $sorted->count() - 1) {
                    $nextEventTime = Carbon::parse($sorted[$i + 1]->event_time);
                    $durationSec  = max(0, $nextEventTime->diffInSeconds($eventTime));

                    if (in_array($eventName, ['login', 'logout', 'start', 'resume', 'running'])) {
                        $activeWorkSec += $durationSec;
                    }
                }
            }

            // --- Apply threshold with Half-Day logic ---
            if ($activeWorkSec >= (8 * 3600)) {
                $presentDays++;
            } elseif ($activeWorkSec >= (4 * 3600)) {
                $halfDays++;
            } else {
                $absentDays++;
            }
        }

        // --- Remove future working days from absentDays ---
        $futureWorkingDays = 0;

        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

            if (
                $day->greaterThan($today) &&
                !$day->isWeekend() &&
                !in_array($dateStr, $holidayDates)
            ) {
                $futureWorkingDays++;
            }
        }

        $absentDays = max(0, $absentDays - $futureWorkingDays);

        return view('dashboard.junior', compact(
            'remaining_seconds',
            'elapsed_seconds',
            'status',
            'button_status',
            'targetGiven',
            'targetAchieved',
            'targetYetToAchieve',
            'daysLeft',
            'presentDays',
            'absentDays',
            'workingDays',
            'nonWorkingDays'
        ));
    }

    public function getButtonStatus()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Fetch the latest timer record by start_time (not ID)
        $latestLog = UserTimerLog::where('user_id', $user->id)
            ->orderBy('start_time', 'desc')
            ->first();

        // Default to 0 if no record exists
        $buttonStatus = $latestLog ? (int) $latestLog->button_status : 0;

        return response()->json([
            'user_id' => $user->id,
            'button_status' => $buttonStatus
        ]);
    }

    public function getLatestPauseTypes()
    {
        $users = User::where('role', 'junior')
            ->where('is_deleted', 0)
            ->get();

        $data = $users->map(function ($user) {
            $latestLog = UserTimerLog::where('user_id', $user->id)
                ->orderBy('start_time', 'desc')
                ->first();

            return [
                'user_id' => $user->id,
                'pause_type' => $latestLog ? $latestLog->pause_type : null,
            ];
        });

        return response()->json($data);
    }





    public function startTimer(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }

            $settings = TimerSetting::first();
            if (!$settings) {
                return response()->json(['error' => 'Timer settings missing'], 500);
            }

            $workDaySeconds = $settings->work_day_seconds;
            $today = now()->startOfDay();

            // Check if timer already exists for today
            $existingTimer = UserTimerLog::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->first();

            // Handle "check only" requests
            if ($request->input('check')) {
                return response()->json([
                    'exists' => $existingTimer ? true : false
                ]);
            }

            // If timer exists, respond accordingly
            if ($existingTimer) {
                return response()->json([
                    'exists' => true,
                    'timer' => $existingTimer
                ]);
            }

            // Create a new timer
            $timerData = [
                'user_id'           => $user->id,
                'login_id'          => $user->id,
                'start_time'        => now(),
                'last_decrement'    => now(),
                'remaining_seconds' => $workDaySeconds,
                'status'            => 'running',
                'pause_type'        => 'resume',
            ];

            if (in_array($user->role, ['senior', 'associate'])) {
                $timerData['button_status'] = '1';
            }


            $timer = UserTimerLog::create($timerData);


            UserTimerPause::create([
                'user_timer_log_id' => $timer->id,
                'user_id'           => $user->id,
                'status'            => 'running',
                'pause_type'        => 'start',
                'remaining_seconds' => $workDaySeconds,
                'event_time'        => now(),
            ]);

            return response()->json([
                'success' => true,
                'timer' => $timer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function startTimerHide(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }

            $today = now()->startOfDay();

            // Check if timer exists for today
            $existingTimer = UserTimerLog::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->first();

            return response()->json([
                'exists' => $existingTimer ? true : false,
                'timer'  => $existingTimer ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function checkPauseButtons(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }

            // Get the latest timer log for today
            $latestLog = UserTimerLog::where('user_id', $user->id)
                ->whereDate('created_at', now()->startOfDay())
                ->latest('created_at')
                ->first();

            return response()->json([
                'pause_type' => $latestLog->pause_type ?? null,
                'status'     => $latestLog->status ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function checkPauseButtonsSenior(Request $request)
    {
        try {
            $userId = $request->input('user_id');

            // Optional: fallback to logged-in user
            if (!$userId) {
                $user = Auth::user();
                $userId = $user->id ?? null;
            }

            if (!$userId) {
                return response()->json(['error' => 'User not found'], 401);
            }

            // Get the latest timer log for today
            $latestLog = UserTimerLog::where('user_id', $userId)
                ->whereDate('created_at', now()->startOfDay())
                ->latest('created_at')
                ->first();

            return response()->json([
                'pause_type' => $latestLog->pause_type ?? null,
                'status'     => $latestLog->status ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ], 500);
        }
    }




    public function senior()
    {
        // Fetch timer settings
        $settings = TimerSetting::first();
        if (!$settings) {
            return response()->json(['error' => 'Timer settings not configured'], 500);
        }
        $workDaySeconds = $settings->work_day_seconds;

        $user  = Auth::user();
        $createdByKey = "{$user->id}|senior";
        $timer = UserTimerLog::where('user_id', $user->id)->latest()->first();

        $remaining_seconds = $workDaySeconds;
        $elapsed_seconds   = 0;
        $status            = 'running';
        $button_status     = 1; // default to show if no timer exists

        if ($timer) {
            $remaining_seconds = $timer->remaining_seconds;
            $elapsed_seconds   = $workDaySeconds - $remaining_seconds;
            $status            = $timer->status;
            $button_status     = $timer->button_status ?? 1;
        }
        $juniorUser = $user;

        $selectedDate  = date('Y-m-d');
        $selectedMonth = date('Y-m', strtotime($selectedDate));
        [$year, $month] = explode('-', $selectedMonth);

        $holidayDates = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where('is_holiday', 1)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Handle multiple targets and target_dates (e.g., "14|15|17" and "2025-09|2025-10|2025-11")
        $targetValues = array_map('trim', explode('|', $juniorUser->target ?? ''));
        $targetDates  = array_map('trim', explode('|', $juniorUser->target_date ?? ''));

        // Find index of matching month (e.g., "2025-10")
        $targetIndex = null;
        foreach ($targetDates as $index => $date) {
            // Accept both "YYYY-MM" and full date "YYYY-MM-DD"
            $monthPart = preg_match('/^\d{4}-\d{2}$/', $date)
                ? $date
                : Carbon::parse($date)->format('Y-m');

            if ($monthPart === $selectedMonth) {
                $targetIndex = $index;
                break;
            }
        }

        // Use the matching month's target, else fallback to first or 0
        $targetGiven = isset($targetValues[$targetIndex])
            ? (int) $targetValues[$targetIndex]
            : ((int) ($targetValues[0] ?? 0));

        // Calculate Days Left (based on matched target_date entry)
        $matchedDate = $targetDates[$targetIndex] ?? null;

        if ($matchedDate) {
            // Handle "YYYY-MM" (month only) or full date
            if (preg_match('/^\d{4}-\d{2}$/', $matchedDate)) {
                $carbonDate = Carbon::parse($matchedDate . '-01')->endOfMonth();
            } else {
                $carbonDate = Carbon::parse($matchedDate);
            }

            $diff     = now()->floatDiffInDays($carbonDate, false);
            $daysLeft = max(0, ceil($diff));
        } else {
            $daysLeft = 0;
        }

        $targetAchieved = GoogleSheetData::whereRaw("created_by REGEXP '{$juniorUser->id}\\\\|junior'")
            ->whereRaw("created_by REGEXP '\\\\|senior'")
            ->whereRaw("created_by REGEXP '\\\\|accountant'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', (int) $month)
            ->sum('Amount');


        $targetYetToAchieve = max(0, $targetGiven - $targetAchieved);

        // --- Calculate Present / Absent / Working / Non-working days ---
        $events = UserTimerPause::where('user_id', $juniorUser->id)
            ->whereYear('event_time', $year)
            ->whereMonth('event_time', $month)
            ->orderBy('event_time', 'asc')
            ->get();

        // Group events by date
        $groupedEvents = $events->groupBy(function ($event) {
            return Carbon::parse($event->event_time)->format('Y-m-d');
        });

        // Determine all days in the selected month
        $startOfMonth = Carbon::create($year, $month, 1);
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();
        $daysInMonth  = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $presentDays     = 0;
        $halfDays        = 0;
        $absentDays      = 0;
        $workingDays     = 0;
        $nonWorkingDays  = 0;

        $today = now()->startOfDay(); // 🔒 block today until end of day

        // Loop through each day
        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

            // Skip today completely
            if ($day->equalTo($today)) {
                continue;
            }

            $dailyEvents = $groupedEvents->get($dateStr, collect());

            // Consider only Saturday/Sunday or holidays as non-working
            if ($day->isWeekend() || in_array($dateStr, $holidayDates)) {
                $nonWorkingDays++;
                continue;
            }

            // Working day
            $workingDays++;

            if ($dailyEvents->isEmpty()) {
                $absentDays++;
                continue;
            }

            // Auto-present rule
            if ($dailyEvents->contains(fn($e) => strtolower($e->pause_type) === 'start')) {
                $presentDays++;
                continue;
            }

            $sorted = $dailyEvents->sortBy('event_time')->values();

            $startSeen       = false;
            $activeWorkSec   = 0;
            $totalBreakSec   = 0;
            $lastPauseTime   = null;

            for ($i = 0; $i < $sorted->count(); $i++) {
                $event      = $sorted[$i];
                $title      = strtolower($event->status ?? '');
                $pauseType  = strtolower($event->pause_type ?? '');
                $eventName  = $title ?: $pauseType;
                $eventTime  = Carbon::parse($event->event_time);

                if ($eventName === 'start') {
                    $startSeen = true;
                }

                if (!$startSeen) continue;

                if ($pauseType === 'inactive') {
                    $lastPauseTime = $eventTime;
                } elseif (in_array($pauseType, ['resume', 'running']) && $lastPauseTime) {
                    $totalBreakSec += $eventTime->diffInSeconds($lastPauseTime);
                    $lastPauseTime = null;
                }

                if ($i < $sorted->count() - 1) {
                    $nextEventTime = Carbon::parse($sorted[$i + 1]->event_time);
                    $durationSec  = max(0, $nextEventTime->diffInSeconds($eventTime));

                    if (in_array($eventName, ['login', 'logout', 'start', 'resume', 'running'])) {
                        $activeWorkSec += $durationSec;
                    }
                }
            }

            // --- Apply threshold with Half-Day logic ---
            if ($activeWorkSec >= (8 * 3600)) {
                $presentDays++;
            } elseif ($activeWorkSec >= (4 * 3600)) {
                $halfDays++;
            } else {
                $absentDays++;
            }
        }

        // --- Remove future working days from absentDays ---
        $futureWorkingDays = 0;

        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

            if (
                $day->greaterThan($today) &&
                !$day->isWeekend() &&
                !in_array($dateStr, $holidayDates)
            ) {
                $futureWorkingDays++;
            }
        }

        $absentDays = max(0, $absentDays - $futureWorkingDays);

        return view('dashboard.senior', compact(
            'remaining_seconds',
            'elapsed_seconds',
            'status',
            'button_status',
            'targetGiven',
            'targetAchieved',
            'targetYetToAchieve',
            'daysLeft',
            'presentDays',
            'absentDays',
            'workingDays',
            'nonWorkingDays'
        ));
    }

    public function trainer()
    {
        // Fetch timer settings dynamically
        $settings = TimerSetting::first();
        if (!$settings) {
            return response()->json(['error' => 'Timer settings not configured'], 500);
        }
        $workDaySeconds = $settings->work_day_seconds;

        $user  = Auth::user();
        $timer = UserTimerLog::where('user_id', $user->id)->latest()->first();

        $remaining_seconds = $workDaySeconds;
        $elapsed_seconds   = 0;
        $status            = 'running';
        $button_status     = 1; // default to show if no timer exists

        if ($timer) {
            $remaining_seconds = $timer->remaining_seconds;
            $elapsed_seconds   = $workDaySeconds - $remaining_seconds;
            $status            = $timer->status;
            $button_status     = $timer->button_status ?? 1;
        }

        return view('dashboard.trainer', compact(
            'remaining_seconds',
            'elapsed_seconds',
            'status',
            'button_status'
        ));
    }

    public function accountant()
    {
        // Fetch timer settings dynamically
        $settings = TimerSetting::first();
        if (!$settings) {
            return response()->json(['error' => 'Timer settings not configured'], 500);
        }
        $workDaySeconds = $settings->work_day_seconds;

        $user  = Auth::user();
        $timer = UserTimerLog::where('user_id', $user->id)->latest()->first();

        $remaining_seconds = $workDaySeconds;
        $elapsed_seconds   = 0;
        $status            = 'running';
        $button_status     = 1; // default to show if no timer exists

        if ($timer) {
            $remaining_seconds = $timer->remaining_seconds;
            $elapsed_seconds   = $workDaySeconds - $remaining_seconds;
            $status            = $timer->status;
            $button_status     = $timer->button_status ?? 1;
        }

        return view('dashboard.accountant', compact(
            'remaining_seconds',
            'elapsed_seconds',
            'status',
            'button_status'
        ));
    }

    public function associate()
    {
        // Fetch timer settings dynamically
        $settings = TimerSetting::first();
        if (!$settings) {
            return response()->json(['error' => 'Timer settings not configured'], 500);
        }
        $workDaySeconds = $settings->work_day_seconds;

        $user  = Auth::user();
        $timer = UserTimerLog::where('user_id', $user->id)->latest()->first();

        $remaining_seconds = $workDaySeconds;
        $elapsed_seconds   = 0;
        $status            = 'running';
        $button_status     = 1; // default to show if no timer exists

        if ($timer) {
            $remaining_seconds = $timer->remaining_seconds;
            $elapsed_seconds   = $workDaySeconds - $remaining_seconds;
            $status            = $timer->status;
            $button_status     = $timer->button_status ?? 1;
        }

        return view('dashboard.associate', compact(
            'remaining_seconds',
            'elapsed_seconds',
            'status',
            'button_status'
        ));
    }

    public function career()
    {
        // Fetch timer settings dynamically
        $settings = TimerSetting::first();
        if (!$settings) {
            return response()->json(['error' => 'Timer settings not configured'], 500);
        }
        $workDaySeconds = $settings->work_day_seconds;

        $user  = Auth::user();
        $timer = UserTimerLog::where('user_id', $user->id)->latest()->first();

        $remaining_seconds = $workDaySeconds;
        $elapsed_seconds   = 0;
        $status            = 'running';
        $button_status     = 1; // default to show if no timer exists

        if ($timer) {
            $remaining_seconds = $timer->remaining_seconds;
            $elapsed_seconds   = $workDaySeconds - $remaining_seconds;
            $status            = $timer->status;
            $button_status     = $timer->button_status ?? 1;
        }

        return view('dashboard.career', compact(
            'remaining_seconds',
            'elapsed_seconds',
            'status',
            'button_status'
        ));
    }

    public function operation()
    {
        // Fetch timer settings dynamically
        $settings = TimerSetting::first();
        if (!$settings) {
            return response()->json(['error' => 'Timer settings not configured'], 500);
        }
        $workDaySeconds = $settings->work_day_seconds;

        $user  = Auth::user();
        $timer = UserTimerLog::where('user_id', $user->id)->latest()->first();

        $remaining_seconds = $workDaySeconds;
        $elapsed_seconds   = 0;
        $status            = 'running';
        $button_status     = 1; // default to show if no timer exists

        if ($timer) {
            $remaining_seconds = $timer->remaining_seconds;
            $elapsed_seconds   = $workDaySeconds - $remaining_seconds;
            $status            = $timer->status;
            $button_status     = $timer->button_status ?? 1;
        }

        return view('dashboard.operation', compact(
            'remaining_seconds',
            'elapsed_seconds',
            'status',
            'button_status'
        ));
    }

    public function resource()
    {
        // Fetch timer settings dynamically
        $settings = TimerSetting::first();
        if (!$settings) {
            return response()->json(['error' => 'Timer settings not configured'], 500);
        }
        $workDaySeconds = $settings->work_day_seconds;

        $user  = Auth::user();
        $timer = UserTimerLog::where('user_id', $user->id)->latest()->first();

        $remaining_seconds = $workDaySeconds;
        $elapsed_seconds   = 0;
        $status            = 'running';
        $button_status     = 1; // default to show if no timer exists

        if ($timer) {
            $remaining_seconds = $timer->remaining_seconds;
            $elapsed_seconds   = $workDaySeconds - $remaining_seconds;
            $status            = $timer->status;
            $button_status     = $timer->button_status ?? 1;
        }

        return view('dashboard.resource', compact(
            'remaining_seconds',
            'elapsed_seconds',
            'status',
            'button_status'
        ));
    }

    public function support()
    {
        // Fetch timer settings dynamically
        $settings = TimerSetting::first();
        if (!$settings) {
            return response()->json(['error' => 'Timer settings not configured'], 500);
        }
        $workDaySeconds = $settings->work_day_seconds;

        $user  = Auth::user();
        $timer = UserTimerLog::where('user_id', $user->id)->latest()->first();

        $remaining_seconds = $workDaySeconds;
        $elapsed_seconds   = 0;
        $status            = 'running';
        $button_status     = 1; // default to show if no timer exists

        if ($timer) {
            $remaining_seconds = $timer->remaining_seconds;
            $elapsed_seconds   = $workDaySeconds - $remaining_seconds;
            $status            = $timer->status;
            $button_status     = $timer->button_status ?? 1;
        }

        return view('dashboard.support', compact(
            'remaining_seconds',
            'elapsed_seconds',
            'status',
            'button_status'
        ));
    }

    public function seniorassociate()
    {
        // Fetch timer settings dynamically
        $settings = TimerSetting::first();
        if (!$settings) {
            return response()->json(['error' => 'Timer settings not configured'], 500);
        }
        $workDaySeconds = $settings->work_day_seconds;

        $user  = Auth::user();
        $timer = UserTimerLog::where('user_id', $user->id)->latest()->first();

        $remaining_seconds = $workDaySeconds;
        $elapsed_seconds   = 0;
        $status            = 'running';
        $button_status     = 1; // default to show if no timer exists

        if ($timer) {
            $remaining_seconds = $timer->remaining_seconds;
            $elapsed_seconds   = $workDaySeconds - $remaining_seconds;
            $status            = $timer->status;
            $button_status     = $timer->button_status ?? 1;
        }

        return view('dashboard.seniorassociate', compact(
            'remaining_seconds',
            'elapsed_seconds',
            'status',
            'button_status'
        ));
    }

    public function writer()
    {
        // Fetch timer settings dynamically
        $settings = TimerSetting::first();
        if (!$settings) {
            return response()->json(['error' => 'Timer settings not configured'], 500);
        }
        $workDaySeconds = $settings->work_day_seconds;

        $user  = Auth::user();
        $timer = UserTimerLog::where('user_id', $user->id)->latest()->first();

        $remaining_seconds = $workDaySeconds;
        $elapsed_seconds   = 0;
        $status            = 'running';
        $button_status     = 1; // default to show if no timer exists

        if ($timer) {
            $remaining_seconds = $timer->remaining_seconds;
            $elapsed_seconds   = $workDaySeconds - $remaining_seconds;
            $status            = $timer->status;
            $button_status     = $timer->button_status ?? 1;
        }

        return view('dashboard.writer', compact(
            'remaining_seconds',
            'elapsed_seconds',
            'status',
            'button_status'
        ));
    }

    public function customer()
    {
        $user     = Auth::user();
        $payments = Payment::where('customer_id', $user->id)->get();

        return view('dashboard.customer', compact('payments'));
    }

    public function updateTimer(Request $request)
    {
        $userId = $request->input('user_id');
        $user = $userId ? User::find($userId) : Auth::user();
        $action = $request->input('action');

        // Get the latest timer log for the user
        $timer = UserTimerLog::where('user_id', $user->id)->latest()->first();
        if (!$timer) {
            return response()->json(['error' => 'Timer not found'], 404);
        }

        // Current time
        $currentTime = now();

        // Fetch work day duration from settings
        $timerSetting = TimerSetting::first();
        if (!$timerSetting) {
            return response()->json(['error' => 'Timer settings not configured'], 500);
        }

        $workDaySeconds = $timerSetting->work_day_seconds;

        // Update remaining seconds if timer is running
        if ($timer->status === 'running') {
            $secondsPassed = $currentTime->diffInSeconds($timer->updated_at);
            $timer->remaining_seconds = max(0, $timer->remaining_seconds + ($secondsPassed / 2));
        }

        // Store previous status before any change
        $previousStatus = $timer->status;
        $previousPauseType = $timer->pause_type;

        // Helper: avoid duplicate pause
        $createPauseIfChanged = function ($status, $pauseType, $remainingSeconds) use ($timer, $user) {
            $lastPause = UserTimerPause::where('user_id', $user->id)
                ->latest('id')
                ->first();

            // Only create if values differ from last
            if (
                !$lastPause ||
                $lastPause->status !== $status ||
                $lastPause->pause_type !== $pauseType ||
                $lastPause->remaining_seconds != $remainingSeconds
            ) {

                UserTimerPause::create([
                    'user_timer_log_id' => $timer->id,
                    'user_id'           => $user->id,
                    'status'            => $status,
                    'pause_type'        => $pauseType,
                    'remaining_seconds' => $remainingSeconds,
                    'event_time'        => now(),
                ]);
            }
        };

        if ($action === 'resume' || $action === 'resumebreak') {
            if ($timer->status === 'paused' && in_array($timer->pause_type, ['break', 'lunch', 'tea'])) {
                if ($action !== 'resumebreak') {
                    return; // silently skip if normal resume is triggered
                }
            }

            $timer->status = 'running';
            $timer->pause_type = 'resume';

            $latestLog = UserTimerLog::where('user_id', $user->id)
                ->whereNotIn('pause_type', ['lunch', 'break', 'tea'])
                ->latest('id')
                ->first();

            if ($latestLog) {
                $latestLog->update([
                    'remaining_seconds' => $timer->remaining_seconds,
                    'status'            => 'running',
                    'pause_type'        => 'resume',
                ]);
            } else {
                $createPauseIfChanged('running', $action, $timer->remaining_seconds);
            }

            if ($previousStatus === 'paused') {
                $createPauseIfChanged('running', 'resume', $timer->remaining_seconds);
            }
        } elseif (in_array($action, ['lunch', 'tea', 'break'])) {
            $timer->status = 'paused';
            $timer->pause_type = $action;

            $latestLog = UserTimerLog::where('user_id', $user->id)
                ->latest('id')
                ->first();

            if ($latestLog) {
                $latestLog->update([
                    'remaining_seconds' => $timer->remaining_seconds,
                    'status'            => 'paused',
                    'pause_type'        => $action,
                ]);
            } else {
                $createPauseIfChanged('paused', $action, $timer->remaining_seconds);
            }

            $createPauseIfChanged('paused', $action, $timer->remaining_seconds);
        } elseif ($action !== 'tick') {
            $timer->status = 'paused';
            $timer->pause_type = 'inactive';

            $latestLog = UserTimerLog::where('user_id', $user->id)
                ->latest('id')
                ->first();

            if ($latestLog) {
                if (!in_array($latestLog->pause_type, ['lunch', 'tea', 'break'])) {
                    $latestLog->update([
                        'remaining_seconds' => $timer->remaining_seconds,
                        'status'            => 'paused',
                        'pause_type'        => 'inactive',
                    ]);
                    $createPauseIfChanged('paused', 'inactive', $timer->remaining_seconds);
                }
            } else {
                $createPauseIfChanged('paused', 'inactive', $timer->remaining_seconds);
            }
        }


        // Update timestamp and save
        $timer->updated_at = $currentTime;
        $timer->save();

        // Calculate elapsed time
        $elapsedSeconds = max(0, $workDaySeconds - $timer->remaining_seconds);

        // Return response
        return response()->json([
            'success'           => true,
            'remaining_seconds' => $timer->remaining_seconds,
            'elapsed_seconds'   => $elapsedSeconds,
            'status'            => $timer->status,
            'pause_type'        => $timer->pause_type,
            'notice_status'     => $timer->notice_status,
            'logout'            => $timer->remaining_seconds <= 0
        ]);
    }



    public function editall()
    {
        // Get only users that have SMTP settings
        $juniorUsers = User::whereHas('smtpSetting')->where('is_deleted', 0)->get();

        return view('smtp.editall', compact('juniorUsers'));
    }

    public function targetall()
    {
        $targetUsers = User::whereIn('role', ['senior', 'junior'])->where('is_deleted', 0)->get();
        return view('target.editall', compact('targetUsers'));
    }

    public function targetedit($id)
    {
        $targetUsers = User::whereIn('role', ['senior', 'junior'])
            ->where('is_deleted', 0)
            ->where('id', $id)
            ->firstOrFail();

        return view('target.edit', compact('targetUsers'));
    }

    public function targetadd($id)
    {
        $targetUsers = User::whereIn('role', ['senior', 'junior'])
            ->where('is_deleted', 0)
            ->where('id', $id)
            ->firstOrFail();

        return view('target.edit', compact('targetUsers'));
    }

    public function targetSave(Request $request, $id)
    {
        $validated = $request->validate([
            'target' => 'required|integer|min:1',
            'target_date' => 'required|date',
            'index' => 'nullable|integer'
        ]);

        $user = User::where('id', $id)
            ->where('is_deleted', 0)
            ->firstOrFail();


        // Split existing values cleanly
        $targets = $user->target ? array_map('trim', explode('|', $user->target)) : [];
        $targetDates = $user->target_date ? array_map('trim', explode('|', $user->target_date)) : [];

        // ✅ Edit existing target (if index provided)
        if ($request->filled('index')) {
            $index = (int) $request->index;

            if (!isset($targets[$index])) {
                return back()->with('error', 'Invalid target index.');
            }

            $targets[$index] = $validated['target'];
            $targetDates[$index] = $validated['target_date'];
        }
        // ✅ Add new target if no index is passed
        else {
            $targets[] = $validated['target'];
            $targetDates[] = $validated['target_date'];
        }

        // ✅ Update user targets and target_dates
        $user->update([
            'target' => implode(' | ', $targets),
            'target_date' => implode(' | ', $targetDates),
        ]);

        return redirect()->back()->with('success', 'Target saved successfully!');
    }

    public function targetDelete(Request $request, $id)
    {
        $user = User::where('is_deleted', 0)
            ->findOrFail($id);
        $index = (int) $request->index;

        $targets = $user->target ? array_map('trim', explode('|', $user->target)) : [];
        $targetDates = $user->target_date ? array_map('trim', explode('|', $user->target_date)) : [];

        if (!isset($targets[$index])) {
            return back()->with('error', 'Invalid target index.');
        }

        unset($targets[$index]);
        unset($targetDates[$index]);

        // Reindex arrays and update DB
        $user->update([
            'target' => implode(' | ', array_values($targets)),
            'target_date' => implode(' | ', array_values($targetDates)),
        ]);

        return redirect()->back()->with('success', 'Target deleted successfully!');
    }


    public function add()
    {
        // Get all user IDs that already have SMTP settings
        $smtpUserIds = SmtpSetting::pluck('user_id')->toArray();

        // Get users who do not have SMTP settings
        $users = User::where('is_deleted', 0)
            ->whereNotIn('id', $smtpUserIds)
            ->get();


        return view('smtp.add', compact('users'));
    }

    // Show the form to edit SMTP settings
    public function edit($userId)
    {
        $smtp = SmtpSetting::where('user_id', $userId)->first(); // Get SMTP for specific user
        $users = User::where('is_deleted', 0)->get();
        // Keep for dropdown or selection if needed

        return view('smtp.edit', compact('smtp', 'users'));
    }

    public function addupdate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'mailer' => 'required|string',
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'required|email',
            'password' => 'nullable|string',
            'encryption' => 'required|string',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        // Find existing SMTP setting for this user or create new
        $smtp = SmtpSetting::firstOrNew(['user_id' => $request->user_id]);

        $smtp->user_id = $request->user_id;
        $smtp->mailer = $request->mailer;
        $smtp->host = $request->host;
        $smtp->port = $request->port;
        $smtp->username = $request->username;
        if ($request->filled('password')) {
            $smtp->password = encrypt($request->password); // encrypt password
        }
        $smtp->encryption = $request->encryption;
        $smtp->from_address = $request->from_address;
        $smtp->from_name = $request->from_name;

        $smtp->save();

        return redirect()->route('smtp.editall')->with('success', 'SMTP settings saved successfully!');
    }

    public function update(Request $request, $userId)
    {
        $request->validate([
            'mailer' => 'required|string',
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'required|email',
            'password' => 'nullable|string',
            'encryption' => 'required|string',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        $smtp = SmtpSetting::where('user_id', $userId)->firstOrFail();

        $smtp->mailer = $request->mailer;
        $smtp->host = $request->host;
        $smtp->port = $request->port;
        $smtp->username = $request->username;
        if ($request->filled('password')) {
            $smtp->password = encrypt($request->password);
        }
        $smtp->encryption = $request->encryption;
        $smtp->from_address = $request->from_address;
        $smtp->from_name = $request->from_name;

        $smtp->save();

        return redirect()->back()->with('success', 'SMTP settings updated successfully!');
    }

    public function uploadGeneratedPdfs(Request $request)
    {
        $storedFiles = [];

        foreach ($request->file('pdf_files') as $file) {

            $fileName = 'form_' . time() . '_' . uniqid() . '.pdf';

            $path = $file->storeAs('public/forms', $fileName);

            $storedFiles[] = storage_path('app/' . $path);
        }

        return response()->json([
            'success' => true,
            'paths'   => $storedFiles
        ]);
    }


    public function sendPaymentMail(Request $request, $smtpId)
    {
        $smtp = SmtpSetting::findOrFail($smtpId); // ID = 2 loaded normally

        // Apply SMTP settings (same as before)
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => $smtp->mailer ?? 'smtp',
            'mail.mailers.smtp.host' => $smtp->host,
            'mail.mailers.smtp.port' => $smtp->port,
            'mail.mailers.smtp.username' => $smtp->username,
            'mail.mailers.smtp.password' => decrypt($smtp->password),
            'mail.mailers.smtp.encryption' => $smtp->encryption,
            'mail.from.address' => $smtp->from_address,
            'mail.from.name' => $smtp->from_name,
        ]);

        // ▶️ HARD-CODED RECEIVER EMAIL (as you requested)
        $receiverEmail = "addmin.abhijeet@gmail.com";  // <-- write your receiver email here

        // Optional email validation
        if (!filter_var($receiverEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid receiver email address.'
            ]);
        }

        // Static subject + message
        $pdfs = $request->pdf_paths ?? [];
        $subject = "Payment Confirmation";
        $messageBody = "Your payment is confirmed";

        try {
            Mail::raw($messageBody, function ($message) use ($receiverEmail, $subject, $pdfs) {
                // Hard-coded receiver
                $message->to($receiverEmail)->subject($subject);
            });

            return response()->json([
                'success' => true,
                'message' => "Payment mail sent successfully to {$receiverEmail}!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send payment mail: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ],
            ]);
        }
    }


    private function validateSmtpConnection($smtp)
    {
        try {
            $useSsl = strtolower($smtp->encryption) === 'ssl';

            $transport = new EsmtpTransport(
                $smtp->host,
                (int) $smtp->port,
                $useSsl
            );

            if (!empty($smtp->username)) {
                $transport->setUsername($smtp->username);
                $transport->setPassword(decrypt($smtp->password));
            }

            // Actual SMTP handshake test
            $transport->start();

            return true;
        } catch (\Throwable $e) {
            throw new \Exception('SMTP connection failed: ' . $e->getMessage());
        }
    }

    public function test(Request $request, $smtpId)
    {
        $smtp = SmtpSetting::findOrFail($smtpId); // ← changed

        if (!$smtp) {
            return response()->json([
                'status' => 'error',
                'message' => 'No SMTP settings found.'
            ]);
        }

        try {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => $smtp->mailer ?? 'smtp',
                'mail.mailers.smtp.host' => $smtp->host,
                'mail.mailers.smtp.port' => $smtp->port,
                'mail.mailers.smtp.username' => $smtp->username,
                'mail.mailers.smtp.password' => decrypt($smtp->password),
                'mail.mailers.smtp.encryption' => $smtp->encryption,
                'mail.from.address' => $smtp->from_address,
                'mail.from.name' => $smtp->from_name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to configure SMTP: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ],
            ]);
        }

        $testEmail = trim($request->input('test_email'));

        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid test email address.'
            ]);
        }

        $subject = "Testing the Mail Functionality";
        $messageBody = "Hi Test,\n\nThis is a test email sent to verify SMTP configuration.\n\nBest,\nYour App";

        try {
            // Validate SMTP first
            $this->validateSmtpConnection($smtp);

            // Reset mailer cache
            app()->forgetInstance('mail.manager');
            app()->forgetInstance('mailer');

            $attempts = 0;
            $maxAttempts = 3;
            $sent = false;

            while ($attempts < $maxAttempts && !$sent) {
                try {
                    Mail::raw($messageBody, function ($message) use ($testEmail, $subject) {
                        $message->to($testEmail)->subject($subject);
                    });
                    $sent = true;
                } catch (\Exception $e) {
                    $attempts++;
                    if ($attempts >= $maxAttempts) {
                        throw $e;
                    }
                    sleep(1); // short delay before retry
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => "✅ Test email sent successfully to {$testEmail}!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ Failed to send test email: ' . $e->getMessage(),
            ]);
        }
    }
}
