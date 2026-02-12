<?php

namespace App\Http\Controllers;

use App\Models\GoogleSheetData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonPeriod;
use Carbon\Carbon;
use App\Models\UserTimerPause;
use App\Models\Holiday;



class CallReportController extends Controller
{
    public function index() {}

    public function senior(Request $request)
    {
        $user = Auth::user();
        $createdByKey = "{$user->id}|senior";
        $juniorUser = $user;
        // ================================
        // Main logic with LIKE filters
        // ================================

        // Total "Called & Mailed" calls
        $calledAndMailedCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->count();

        $selffollowupCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        // Total "Ready To Pay" calls
        $readyToPaidCalls = GoogleSheetData::where(function ($q) {
            $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
        })
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();


        $followUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        $transferedfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->count();

        $totalCalls =
            $calledAndMailedCalls
            + $selffollowupCalls
            + $readyToPaidCalls
            + $followUpCalls
            + $transferedfollowUpCalls;


        // Total other calls (excluding Called & Mailed)
        $otherCalls = GoogleSheetData::where('created_by', 'like', "%{$createdByKey}%")
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        // Selected date (default today)
        $selectedDate = $request->input('selected_date', date('Y-m-d'));
        $selectedMonth = date('Y-m', strtotime($selectedDate));
        [$year, $month] = explode('-', $selectedMonth);

        // Base query filtered by this senior and date
        $query = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate);
        $tquery = GoogleSheetData::where('created_by', 'like', "%{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate);

        // Selected date totals
        // Self follow-up calls (Called & Mailed / Ready To Pay with TransferRemark)
        $SselffollowupCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        // Ready To Pay calls
        $SreadyToPaidCalls = GoogleSheetData::where(function ($q) {
            $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
        })
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();


        // Follow-up calls (Called & Mailed with TransferRemark)
        $SfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();


        // Transferred follow-up calls
        $StransferedfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->count();

        // Other calls (excluding Called & Mailed)
        $SotherCalls = (clone $tquery)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();

        $ScalledAndMailedCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereDate('followup', $selectedDate)
            ->where('transfers', 0)
            ->count();

        $StotalCalls =
            $ScalledAndMailedCalls
            + $SselffollowupCalls
            + $SreadyToPaidCalls
            + $SfollowUpCalls
            + $StransferedfollowUpCalls;

        // Hour-wise "Ready To Pay" counts
        $hourlyReadyToPaid = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where(function ($q) {
                $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                    ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
            })
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Ready To Pay')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();


        // Hourly Called & Mailed
        $hourlyCalledAndMailed = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'")
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereDate('followup', $selectedDate)
            ->where('transfers', 0)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();


        // Hourly Self Follow-up (Called & Mailed / Ready To Pay with TransferRemark)
        $hourlySelfFollowUp = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'")
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();


        // Hourly Follow-up (Called & Mailed with TransferRemark)
        $hourlyFollowUp = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->where('transfers', 0)
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();


        // Hourly Transferred Follow-up
        $hourlyTransferredFollowUp = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->where('transfers', 1)
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();


        // Hourly Other Calls (excluding Called & Mailed)
        $hourlyOtherCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "%{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $holidayDates = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where('is_holiday', 1)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();


        // Initialize hour blocks (10 AM - 8 PM)
        $c8to9am = $hourlyCalledAndMailed[8] ?? 0;
        $c9to10am = $hourlyCalledAndMailed[9] ?? 0;
        $c10to11am = $hourlyCalledAndMailed[10] ?? 0;
        $c11to12pm = $hourlyCalledAndMailed[11] ?? 0;
        $c12to1pm  = $hourlyCalledAndMailed[12] ?? 0;
        $c1to2pm   = $hourlyCalledAndMailed[13] ?? 0;
        $c2to3pm   = $hourlyCalledAndMailed[14] ?? 0;
        $c3to4pm   = $hourlyCalledAndMailed[15] ?? 0;
        $c4to5pm   = $hourlyCalledAndMailed[16] ?? 0;
        $c5to6pm   = $hourlyCalledAndMailed[17] ?? 0;
        $c6to7pm   = $hourlyCalledAndMailed[18] ?? 0;
        $c7to8pm   = $hourlyCalledAndMailed[19] ?? 0;


        $sf8to9am = $hourlySelfFollowUp[8] ?? 0;
        $sf9to10am = $hourlySelfFollowUp[9] ?? 0;
        $sf10to11am = $hourlySelfFollowUp[10] ?? 0;
        $sf11to12pm = $hourlySelfFollowUp[11] ?? 0;
        $sf12to1pm  = $hourlySelfFollowUp[12] ?? 0;
        $sf1to2pm   = $hourlySelfFollowUp[13] ?? 0;
        $sf2to3pm   = $hourlySelfFollowUp[14] ?? 0;
        $sf3to4pm   = $hourlySelfFollowUp[15] ?? 0;
        $sf4to5pm   = $hourlySelfFollowUp[16] ?? 0;
        $sf5to6pm   = $hourlySelfFollowUp[17] ?? 0;
        $sf6to7pm   = $hourlySelfFollowUp[18] ?? 0;
        $sf7to8pm   = $hourlySelfFollowUp[19] ?? 0;


        $f8to9am = $hourlyFollowUp[8] ?? 0;
        $f9to10am = $hourlyFollowUp[9] ?? 0;
        $f10to11am = $hourlyFollowUp[10] ?? 0;
        $f11to12pm = $hourlyFollowUp[11] ?? 0;
        $f12to1pm  = $hourlyFollowUp[12] ?? 0;
        $f1to2pm   = $hourlyFollowUp[13] ?? 0;
        $f2to3pm   = $hourlyFollowUp[14] ?? 0;
        $f3to4pm   = $hourlyFollowUp[15] ?? 0;
        $f4to5pm   = $hourlyFollowUp[16] ?? 0;
        $f5to6pm   = $hourlyFollowUp[17] ?? 0;
        $f6to7pm   = $hourlyFollowUp[18] ?? 0;
        $f7to8pm   = $hourlyFollowUp[19] ?? 0;

        $tf8to9am = $hourlyTransferredFollowUp[8] ?? 0;
        $tf9to10am = $hourlyTransferredFollowUp[9] ?? 0;
        $tf10to11am = $hourlyTransferredFollowUp[10] ?? 0;
        $tf11to12pm = $hourlyTransferredFollowUp[11] ?? 0;
        $tf12to1pm  = $hourlyTransferredFollowUp[12] ?? 0;
        $tf1to2pm   = $hourlyTransferredFollowUp[13] ?? 0;
        $tf2to3pm   = $hourlyTransferredFollowUp[14] ?? 0;
        $tf3to4pm   = $hourlyTransferredFollowUp[15] ?? 0;
        $tf4to5pm   = $hourlyTransferredFollowUp[16] ?? 0;
        $tf5to6pm   = $hourlyTransferredFollowUp[17] ?? 0;
        $tf6to7pm   = $hourlyTransferredFollowUp[18] ?? 0;
        $tf7to8pm   = $hourlyTransferredFollowUp[19] ?? 0;

        $o8to9am = $hourlyOtherCalls[8] ?? 0;
        $o9to10am = $hourlyOtherCalls[9] ?? 0;
        $o10to11am = $hourlyOtherCalls[10] ?? 0;
        $o11to12pm = $hourlyOtherCalls[11] ?? 0;
        $o12to1pm  = $hourlyOtherCalls[12] ?? 0;
        $o1to2pm   = $hourlyOtherCalls[13] ?? 0;
        $o2to3pm   = $hourlyOtherCalls[14] ?? 0;
        $o3to4pm   = $hourlyOtherCalls[15] ?? 0;
        $o4to5pm   = $hourlyOtherCalls[16] ?? 0;
        $o5to6pm   = $hourlyOtherCalls[17] ?? 0;
        $o6to7pm   = $hourlyOtherCalls[18] ?? 0;
        $o7to8pm   = $hourlyOtherCalls[19] ?? 0;

        $r8to9am  = $hourlyReadyToPaid[8]  ?? 0;
        $r9to10am = $hourlyReadyToPaid[9]  ?? 0;
        $r10to11am = $hourlyReadyToPaid[10] ?? 0;
        $r11to12pm = $hourlyReadyToPaid[11] ?? 0;
        $r12to1pm  = $hourlyReadyToPaid[12] ?? 0;
        $r1to2pm   = $hourlyReadyToPaid[13] ?? 0;
        $r2to3pm   = $hourlyReadyToPaid[14] ?? 0;
        $r3to4pm   = $hourlyReadyToPaid[15] ?? 0;
        $r4to5pm   = $hourlyReadyToPaid[16] ?? 0;
        $r5to6pm   = $hourlyReadyToPaid[17] ?? 0;
        $r6to7pm   = $hourlyReadyToPaid[18] ?? 0;
        $r7to8pm   = $hourlyReadyToPaid[19] ?? 0;

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


        $targetAchieved = GoogleSheetData::whereRaw(
            "created_by REGEXP '^{$juniorUser->id}\\\\|senior:[0-9]+\\\\|senior:[0-9]+\\\\|accountant(.*)?$'"
        )
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

        $presentDays    = 0;
        $halfDays       = 0;
        $absentDays     = 0;
        $workingDays    = 0;
        $nonWorkingDays = 0;

        $today = now()->startOfDay(); // 🔒 freeze today until end of day

        // Loop through each day
        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

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



        return view('reports.senior', compact(
            'totalCalls',
            'juniorUser',
            'calledAndMailedCalls',
            'selffollowupCalls',
            'readyToPaidCalls',
            'followUpCalls',
            'transferedfollowUpCalls',
            'otherCalls',

            'SselffollowupCalls',
            'StotalCalls',
            'ScalledAndMailedCalls',
            'SreadyToPaidCalls',
            'StransferedfollowUpCalls',
            'SfollowUpCalls',
            'SotherCalls',
            'selectedDate',


            'r8to9am',
            'r9to10am',
            'r10to11am',
            'r11to12pm',
            'r12to1pm',
            'r1to2pm',
            'r2to3pm',
            'r3to4pm',
            'r4to5pm',
            'r5to6pm',
            'r6to7pm',
            'r7to8pm',


            'c8to9am',
            'c9to10am',
            'c10to11am',
            'c11to12pm',
            'c12to1pm',
            'c1to2pm',
            'c2to3pm',
            'c3to4pm',
            'c4to5pm',
            'c5to6pm',
            'c6to7pm',
            'c7to8pm',


            'sf8to9am',
            'sf9to10am',
            'sf10to11am',
            'sf11to12pm',
            'sf12to1pm',
            'sf1to2pm',
            'sf2to3pm',
            'sf3to4pm',
            'sf4to5pm',
            'sf5to6pm',
            'sf6to7pm',
            'sf7to8pm',


            'f8to9am',
            'f9to10am',
            'f10to11am',
            'f11to12pm',
            'f12to1pm',
            'f1to2pm',
            'f2to3pm',
            'f3to4pm',
            'f4to5pm',
            'f5to6pm',
            'f6to7pm',
            'f7to8pm',


            'tf8to9am',
            'tf9to10am',
            'tf10to11am',
            'tf11to12pm',
            'tf12to1pm',
            'tf1to2pm',
            'tf2to3pm',
            'tf3to4pm',
            'tf4to5pm',
            'tf5to6pm',
            'tf6to7pm',
            'tf7to8pm',


            'o8to9am',
            'o9to10am',
            'o10to11am',
            'o11to12pm',
            'o12to1pm',
            'o1to2pm',
            'o2to3pm',
            'o3to4pm',
            'o4to5pm',
            'o5to6pm',
            'o6to7pm',
            'o7to8pm',


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


    public function seniormonthly(Request $request)
    {
        $user = Auth::user();
        $createdByKey = "{$user->id}|senior";

        // Total "Called & Mailed" calls
        $calledAndMailedCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        $selffollowupCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        // Total "Ready To Pay" calls
        $readyToPaidCalls = GoogleSheetData::where(function ($q) {
            $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
        })
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();

        $followUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->where('transfers', 0)
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->count();

        $transferedfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->where('transfers', 1)
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->count();

        $totalCalls =
            $calledAndMailedCalls
            + $selffollowupCalls
            + $readyToPaidCalls
            + $followUpCalls
            + $transferedfollowUpCalls;

        // Total other calls (excluding Called & Mailed)
        $otherCalls = GoogleSheetData::where('created_by', 'like', "%{$createdByKey}%")
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        // Selected month (default current month in YYYY-MM)
        $selectedMonth = $request->input('selected_month', date('Y-m'));
        [$year, $month] = explode('-', $selectedMonth);

        // Total "Called & Mailed" calls in month
        $McalledAndMailedCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();


        // Self follow-up calls in month (Called & Mailed / Ready To Pay with TransferRemark)
        $MselffollowupCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();


        // Ready To Pay calls in month
        $MreadyToPaidCalls = GoogleSheetData::where(function ($q) {
            $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
        })
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();


        // Follow-up calls in month (Called & Mailed with TransferRemark)
        $MfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        // Transferred follow-up calls in month
        $MtransferedfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->count();

        // Other calls in month (excluding Called & Mailed)
        $MotherCalls = GoogleSheetData::where('created_by', 'like', "%{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();

        $MtotalCalls =
            $McalledAndMailedCalls
            + $MselffollowupCalls
            + $MreadyToPaidCalls
            + $MfollowUpCalls
            + $MtransferedfollowUpCalls;


        // Daily "Called & Mailed" counts
        $dailyCalledAndMailed = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'")
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();


        // Daily "Follow Up" counts
        $dailyFollowUp = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();


        // Daily Ready To Paid
        $dailyReadyToPaid = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->where(function ($q) {
                $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                    ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
            })
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Ready To Pay')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();


        // Daily Self Follow-up (Called & Mailed / Ready To Pay with TransferRemark)
        $dailySelfFollowUp = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();


        // Daily Transferred Follow-up (Called & Mailed with TransferRemark and transfers = 1)
        $dailyTransferredFollowUp = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        // Daily Other Calls (excluding Called & Mailed)
        $dailyOtherCalls = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->where('created_by', 'like', "%{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        $holidayDates = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where('is_holiday', 1)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();



        // --- Initialize daily variables (Day 1 - Day 31) ---
        $cDay1  = $dailyCalledAndMailed[1]  ?? 0;
        $cDay2  = $dailyCalledAndMailed[2]  ?? 0;
        $cDay3  = $dailyCalledAndMailed[3]  ?? 0;
        $cDay4  = $dailyCalledAndMailed[4]  ?? 0;
        $cDay5  = $dailyCalledAndMailed[5]  ?? 0;
        $cDay6  = $dailyCalledAndMailed[6]  ?? 0;
        $cDay7  = $dailyCalledAndMailed[7]  ?? 0;
        $cDay8  = $dailyCalledAndMailed[8]  ?? 0;
        $cDay9  = $dailyCalledAndMailed[9]  ?? 0;
        $cDay10 = $dailyCalledAndMailed[10] ?? 0;
        $cDay11 = $dailyCalledAndMailed[11] ?? 0;
        $cDay12 = $dailyCalledAndMailed[12] ?? 0;
        $cDay13 = $dailyCalledAndMailed[13] ?? 0;
        $cDay14 = $dailyCalledAndMailed[14] ?? 0;
        $cDay15 = $dailyCalledAndMailed[15] ?? 0;
        $cDay16 = $dailyCalledAndMailed[16] ?? 0;
        $cDay17 = $dailyCalledAndMailed[17] ?? 0;
        $cDay18 = $dailyCalledAndMailed[18] ?? 0;
        $cDay19 = $dailyCalledAndMailed[19] ?? 0;
        $cDay20 = $dailyCalledAndMailed[20] ?? 0;
        $cDay21 = $dailyCalledAndMailed[21] ?? 0;
        $cDay22 = $dailyCalledAndMailed[22] ?? 0;
        $cDay23 = $dailyCalledAndMailed[23] ?? 0;
        $cDay24 = $dailyCalledAndMailed[24] ?? 0;
        $cDay25 = $dailyCalledAndMailed[25] ?? 0;
        $cDay26 = $dailyCalledAndMailed[26] ?? 0;
        $cDay27 = $dailyCalledAndMailed[27] ?? 0;
        $cDay28 = $dailyCalledAndMailed[28] ?? 0;
        $cDay29 = $dailyCalledAndMailed[29] ?? 0;
        $cDay30 = $dailyCalledAndMailed[30] ?? 0;
        $cDay31 = $dailyCalledAndMailed[31] ?? 0;

        $sfDay1  = $dailySelfFollowUp[1]  ?? 0;
        $sfDay2  = $dailySelfFollowUp[2]  ?? 0;
        $sfDay3  = $dailySelfFollowUp[3]  ?? 0;
        $sfDay4  = $dailySelfFollowUp[4]  ?? 0;
        $sfDay5  = $dailySelfFollowUp[5]  ?? 0;
        $sfDay6  = $dailySelfFollowUp[6]  ?? 0;
        $sfDay7  = $dailySelfFollowUp[7]  ?? 0;
        $sfDay8  = $dailySelfFollowUp[8]  ?? 0;
        $sfDay9  = $dailySelfFollowUp[9]  ?? 0;
        $sfDay10 = $dailySelfFollowUp[10] ?? 0;
        $sfDay11 = $dailySelfFollowUp[11] ?? 0;
        $sfDay12 = $dailySelfFollowUp[12] ?? 0;
        $sfDay13 = $dailySelfFollowUp[13] ?? 0;
        $sfDay14 = $dailySelfFollowUp[14] ?? 0;
        $sfDay15 = $dailySelfFollowUp[15] ?? 0;
        $sfDay16 = $dailySelfFollowUp[16] ?? 0;
        $sfDay17 = $dailySelfFollowUp[17] ?? 0;
        $sfDay18 = $dailySelfFollowUp[18] ?? 0;
        $sfDay19 = $dailySelfFollowUp[19] ?? 0;
        $sfDay20 = $dailySelfFollowUp[20] ?? 0;
        $sfDay21 = $dailySelfFollowUp[21] ?? 0;
        $sfDay22 = $dailySelfFollowUp[22] ?? 0;
        $sfDay23 = $dailySelfFollowUp[23] ?? 0;
        $sfDay24 = $dailySelfFollowUp[24] ?? 0;
        $sfDay25 = $dailySelfFollowUp[25] ?? 0;
        $sfDay26 = $dailySelfFollowUp[26] ?? 0;
        $sfDay27 = $dailySelfFollowUp[27] ?? 0;
        $sfDay28 = $dailySelfFollowUp[28] ?? 0;
        $sfDay29 = $dailySelfFollowUp[29] ?? 0;
        $sfDay30 = $dailySelfFollowUp[30] ?? 0;
        $sfDay31 = $dailySelfFollowUp[31] ?? 0;


        $tfDay1  = $dailyTransferredFollowUp[1]  ?? 0;
        $tfDay2  = $dailyTransferredFollowUp[2]  ?? 0;
        $tfDay3  = $dailyTransferredFollowUp[3]  ?? 0;
        $tfDay4  = $dailyTransferredFollowUp[4]  ?? 0;
        $tfDay5  = $dailyTransferredFollowUp[5]  ?? 0;
        $tfDay6  = $dailyTransferredFollowUp[6]  ?? 0;
        $tfDay7  = $dailyTransferredFollowUp[7]  ?? 0;
        $tfDay8  = $dailyTransferredFollowUp[8]  ?? 0;
        $tfDay9  = $dailyTransferredFollowUp[9]  ?? 0;
        $tfDay10 = $dailyTransferredFollowUp[10] ?? 0;
        $tfDay11 = $dailyTransferredFollowUp[11] ?? 0;
        $tfDay12 = $dailyTransferredFollowUp[12] ?? 0;
        $tfDay13 = $dailyTransferredFollowUp[13] ?? 0;
        $tfDay14 = $dailyTransferredFollowUp[14] ?? 0;
        $tfDay15 = $dailyTransferredFollowUp[15] ?? 0;
        $tfDay16 = $dailyTransferredFollowUp[16] ?? 0;
        $tfDay17 = $dailyTransferredFollowUp[17] ?? 0;
        $tfDay18 = $dailyTransferredFollowUp[18] ?? 0;
        $tfDay19 = $dailyTransferredFollowUp[19] ?? 0;
        $tfDay20 = $dailyTransferredFollowUp[20] ?? 0;
        $tfDay21 = $dailyTransferredFollowUp[21] ?? 0;
        $tfDay22 = $dailyTransferredFollowUp[22] ?? 0;
        $tfDay23 = $dailyTransferredFollowUp[23] ?? 0;
        $tfDay24 = $dailyTransferredFollowUp[24] ?? 0;
        $tfDay25 = $dailyTransferredFollowUp[25] ?? 0;
        $tfDay26 = $dailyTransferredFollowUp[26] ?? 0;
        $tfDay27 = $dailyTransferredFollowUp[27] ?? 0;
        $tfDay28 = $dailyTransferredFollowUp[28] ?? 0;
        $tfDay29 = $dailyTransferredFollowUp[29] ?? 0;
        $tfDay30 = $dailyTransferredFollowUp[30] ?? 0;
        $tfDay31 = $dailyTransferredFollowUp[31] ?? 0;

        $oDay1  = $dailyOtherCalls[1]  ?? 0;
        $oDay2  = $dailyOtherCalls[2]  ?? 0;
        $oDay3  = $dailyOtherCalls[3]  ?? 0;
        $oDay4  = $dailyOtherCalls[4]  ?? 0;
        $oDay5  = $dailyOtherCalls[5]  ?? 0;
        $oDay6  = $dailyOtherCalls[6]  ?? 0;
        $oDay7  = $dailyOtherCalls[7]  ?? 0;
        $oDay8  = $dailyOtherCalls[8]  ?? 0;
        $oDay9  = $dailyOtherCalls[9]  ?? 0;
        $oDay10 = $dailyOtherCalls[10] ?? 0;
        $oDay11 = $dailyOtherCalls[11] ?? 0;
        $oDay12 = $dailyOtherCalls[12] ?? 0;
        $oDay13 = $dailyOtherCalls[13] ?? 0;
        $oDay14 = $dailyOtherCalls[14] ?? 0;
        $oDay15 = $dailyOtherCalls[15] ?? 0;
        $oDay16 = $dailyOtherCalls[16] ?? 0;
        $oDay17 = $dailyOtherCalls[17] ?? 0;
        $oDay18 = $dailyOtherCalls[18] ?? 0;
        $oDay19 = $dailyOtherCalls[19] ?? 0;
        $oDay20 = $dailyOtherCalls[20] ?? 0;
        $oDay21 = $dailyOtherCalls[21] ?? 0;
        $oDay22 = $dailyOtherCalls[22] ?? 0;
        $oDay23 = $dailyOtherCalls[23] ?? 0;
        $oDay24 = $dailyOtherCalls[24] ?? 0;
        $oDay25 = $dailyOtherCalls[25] ?? 0;
        $oDay26 = $dailyOtherCalls[26] ?? 0;
        $oDay27 = $dailyOtherCalls[27] ?? 0;
        $oDay28 = $dailyOtherCalls[28] ?? 0;
        $oDay29 = $dailyOtherCalls[29] ?? 0;
        $oDay30 = $dailyOtherCalls[30] ?? 0;
        $oDay31 = $dailyOtherCalls[31] ?? 0;


        $fDay1  = $dailyFollowUp[1]  ?? 0;
        $fDay2  = $dailyFollowUp[2]  ?? 0;
        $fDay3  = $dailyFollowUp[3]  ?? 0;
        $fDay4  = $dailyFollowUp[4]  ?? 0;
        $fDay5  = $dailyFollowUp[5]  ?? 0;
        $fDay6  = $dailyFollowUp[6]  ?? 0;
        $fDay7  = $dailyFollowUp[7]  ?? 0;
        $fDay8  = $dailyFollowUp[8]  ?? 0;
        $fDay9  = $dailyFollowUp[9]  ?? 0;
        $fDay10 = $dailyFollowUp[10] ?? 0;
        $fDay11 = $dailyFollowUp[11] ?? 0;
        $fDay12 = $dailyFollowUp[12] ?? 0;
        $fDay13 = $dailyFollowUp[13] ?? 0;
        $fDay14 = $dailyFollowUp[14] ?? 0;
        $fDay15 = $dailyFollowUp[15] ?? 0;
        $fDay16 = $dailyFollowUp[16] ?? 0;
        $fDay17 = $dailyFollowUp[17] ?? 0;
        $fDay18 = $dailyFollowUp[18] ?? 0;
        $fDay19 = $dailyFollowUp[19] ?? 0;
        $fDay20 = $dailyFollowUp[20] ?? 0;
        $fDay21 = $dailyFollowUp[21] ?? 0;
        $fDay22 = $dailyFollowUp[22] ?? 0;
        $fDay23 = $dailyFollowUp[23] ?? 0;
        $fDay24 = $dailyFollowUp[24] ?? 0;
        $fDay25 = $dailyFollowUp[25] ?? 0;
        $fDay26 = $dailyFollowUp[26] ?? 0;
        $fDay27 = $dailyFollowUp[27] ?? 0;
        $fDay28 = $dailyFollowUp[28] ?? 0;
        $fDay29 = $dailyFollowUp[29] ?? 0;
        $fDay30 = $dailyFollowUp[30] ?? 0;
        $fDay31 = $dailyFollowUp[31] ?? 0;

        $rDay1  = $dailyReadyToPaid[1]  ?? 0;
        $rDay2  = $dailyReadyToPaid[2]  ?? 0;
        $rDay3  = $dailyReadyToPaid[3]  ?? 0;
        $rDay4  = $dailyReadyToPaid[4]  ?? 0;
        $rDay5  = $dailyReadyToPaid[5]  ?? 0;
        $rDay6  = $dailyReadyToPaid[6]  ?? 0;
        $rDay7  = $dailyReadyToPaid[7]  ?? 0;
        $rDay8  = $dailyReadyToPaid[8]  ?? 0;
        $rDay9  = $dailyReadyToPaid[9]  ?? 0;
        $rDay10 = $dailyReadyToPaid[10] ?? 0;
        $rDay11 = $dailyReadyToPaid[11] ?? 0;
        $rDay12 = $dailyReadyToPaid[12] ?? 0;
        $rDay13 = $dailyReadyToPaid[13] ?? 0;
        $rDay14 = $dailyReadyToPaid[14] ?? 0;
        $rDay15 = $dailyReadyToPaid[15] ?? 0;
        $rDay16 = $dailyReadyToPaid[16] ?? 0;
        $rDay17 = $dailyReadyToPaid[17] ?? 0;
        $rDay18 = $dailyReadyToPaid[18] ?? 0;
        $rDay19 = $dailyReadyToPaid[19] ?? 0;
        $rDay20 = $dailyReadyToPaid[20] ?? 0;
        $rDay21 = $dailyReadyToPaid[21] ?? 0;
        $rDay22 = $dailyReadyToPaid[22] ?? 0;
        $rDay23 = $dailyReadyToPaid[23] ?? 0;
        $rDay24 = $dailyReadyToPaid[24] ?? 0;
        $rDay25 = $dailyReadyToPaid[25] ?? 0;
        $rDay26 = $dailyReadyToPaid[26] ?? 0;
        $rDay27 = $dailyReadyToPaid[27] ?? 0;
        $rDay28 = $dailyReadyToPaid[28] ?? 0;
        $rDay29 = $dailyReadyToPaid[29] ?? 0;
        $rDay30 = $dailyReadyToPaid[30] ?? 0;
        $rDay31 = $dailyReadyToPaid[31] ?? 0;

        $juniorUser = $user;

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

        $targetAchieved = GoogleSheetData::whereRaw(
            "created_by REGEXP '^{$juniorUser->id}\\\\|senior:[0-9]+\\\\|senior:[0-9]+\\\\|accountant(.*)?$'"
        )
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

        $presentDays    = 0;
        $halfDays       = 0;
        $absentDays     = 0;
        $workingDays    = 0;
        $nonWorkingDays = 0;

        $today = now()->startOfDay(); // 🔒 freeze today until end of day

        // Loop through each day
        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

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

        $MAvgTotalCalls = $presentDays > 0 ? intval($McalledAndMailedCalls / $presentDays) : 0;

        return view('reports.seniormonthly', compact(
            'totalCalls',
            'MAvgTotalCalls',
            'juniorUser',
            'calledAndMailedCalls',
            'readyToPaidCalls',
            'followUpCalls',
            'otherCalls',
            'MtotalCalls',
            'McalledAndMailedCalls',
            'MfollowUpCalls',
            'MreadyToPaidCalls',
            'MotherCalls',
            'selectedMonth',
            'MselffollowupCalls',
            'MtransferedfollowUpCalls',

            // --- Called & Mailed daily ---
            'cDay1',
            'cDay2',
            'cDay3',
            'cDay4',
            'cDay5',
            'cDay6',
            'cDay7',
            'cDay8',
            'cDay9',
            'cDay10',
            'cDay11',
            'cDay12',
            'cDay13',
            'cDay14',
            'cDay15',
            'cDay16',
            'cDay17',
            'cDay18',
            'cDay19',
            'cDay20',
            'cDay21',
            'cDay22',
            'cDay23',
            'cDay24',
            'cDay25',
            'cDay26',
            'cDay27',
            'cDay28',
            'cDay29',
            'cDay30',
            'cDay31',


            // --- Other Calls daily ---
            'sfDay1',
            'sfDay2',
            'sfDay3',
            'sfDay4',
            'sfDay5',
            'sfDay6',
            'sfDay7',
            'sfDay8',
            'sfDay9',
            'sfDay10',
            'sfDay11',
            'sfDay12',
            'sfDay13',
            'sfDay14',
            'sfDay15',
            'sfDay16',
            'sfDay17',
            'sfDay18',
            'sfDay19',
            'sfDay20',
            'sfDay21',
            'sfDay22',
            'sfDay23',
            'sfDay24',
            'sfDay25',
            'sfDay26',
            'sfDay27',
            'sfDay28',
            'sfDay29',
            'sfDay30',
            'sfDay31',


            'fDay1',
            'fDay2',
            'fDay3',
            'fDay4',
            'fDay5',
            'fDay6',
            'fDay7',
            'fDay8',
            'fDay9',
            'fDay10',
            'fDay11',
            'fDay12',
            'fDay13',
            'fDay14',
            'fDay15',
            'fDay16',
            'fDay17',
            'fDay18',
            'fDay19',
            'fDay20',
            'fDay21',
            'fDay22',
            'fDay23',
            'fDay24',
            'fDay25',
            'fDay26',
            'fDay27',
            'fDay28',
            'fDay29',
            'fDay30',
            'fDay31',


            'tfDay1',
            'tfDay2',
            'tfDay3',
            'tfDay4',
            'tfDay5',
            'tfDay6',
            'tfDay7',
            'tfDay8',
            'tfDay9',
            'tfDay10',
            'tfDay11',
            'tfDay12',
            'tfDay13',
            'tfDay14',
            'tfDay15',
            'tfDay16',
            'tfDay17',
            'tfDay18',
            'tfDay19',
            'tfDay20',
            'tfDay21',
            'tfDay22',
            'tfDay23',
            'tfDay24',
            'tfDay25',
            'tfDay26',
            'tfDay27',
            'tfDay28',
            'tfDay29',
            'tfDay30',
            'tfDay31',

            'oDay1',
            'oDay2',
            'oDay3',
            'oDay4',
            'oDay5',
            'oDay6',
            'oDay7',
            'oDay8',
            'oDay9',
            'oDay10',
            'oDay11',
            'oDay12',
            'oDay13',
            'oDay14',
            'oDay15',
            'oDay16',
            'oDay17',
            'oDay18',
            'oDay19',
            'oDay20',
            'oDay21',
            'oDay22',
            'oDay23',
            'oDay24',
            'oDay25',
            'oDay26',
            'oDay27',
            'oDay28',
            'oDay29',
            'oDay30',
            'oDay31',

            'rDay1',
            'rDay2',
            'rDay3',
            'rDay4',
            'rDay5',
            'rDay6',
            'rDay7',
            'rDay8',
            'rDay9',
            'rDay10',
            'rDay11',
            'rDay12',
            'rDay13',
            'rDay14',
            'rDay15',
            'rDay16',
            'rDay17',
            'rDay18',
            'rDay19',
            'rDay20',
            'rDay21',
            'rDay22',
            'rDay23',
            'rDay24',
            'rDay25',
            'rDay26',
            'rDay27',
            'rDay28',
            'rDay29',
            'rDay30',
            'rDay31',


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


    public function alljuniorlist(Request $request)
    {
        // Fetch all users with role 'junior'
        $juniorUsers = User::where('role', 'junior')->where('is_deleted', 0)->get();

        // Pass users to the view
        return view('reports.alljuniorlist', compact('juniorUsers'));
    }

    public function allseniorlist(Request $request)
    {
        // Fetch all users with role 'senior'
        $seniorUsers = User::where('role', 'senior')->where('is_deleted', 0)->get();

        // Pass users to the view
        return view('reports.allseniorlist', compact('seniorUsers'));
    }

    public function allaccountantlist(Request $request)
    {
        // Fetch all users with role 'senior'
        $accountantUsers = User::where('role', 'accountant')->where('is_deleted', 0)->get();

        // Pass users to the view
        return view('reports.allaccountantlist', compact('accountantUsers'));
    }

    public function alltrainerlist(Request $request)
    {
        // Fetch all users with role 'trainer'
        $trainerUsers = User::where('role', 'trainer')->where('is_deleted', 0)->get();

        // Pass users to the view
        return view('reports.alltrainerlist', compact('trainerUsers'));
    }

    public function reportsender(Request $request)
    {
        $juniorUsers = User::where('is_deleted', 0)->whereIn('role', ['junior', 'senior'])->get();
        // Pass users to the view
        return view('reports.reportsender', compact('juniorUsers'));
    }

    public function allreport(Request $request, $userId)
    {
        $userIds = explode(',', $userId);

        $users = User::whereIn('id', $userIds)
            ->where('is_deleted', 0)
            ->get();

        abort_if($users->isEmpty(), 404);

        $reports = [];

        foreach ($users as $juniorUser) {

            $createdByKey = "{$juniorUser->id}|junior";

            // Total calls for this junior (including hierarchical keys)
            $totalCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")->count();

            // Total "Called & Mailed" calls for this junior
            $calledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
                ->where('Exe_Remarks', 'Called & Mailed')
                ->count();

            // Total other calls for this junior
            $otherCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
                ->where(function ($q) {
                    $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                        ->orWhereNull('Exe_Remarks');
                })
                ->count();


            // Group data by hour of updated_at (for this junior)
            $hourlyCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
                ->where('created_by', 'like', "{$createdByKey}%")
                ->groupBy('hour')
                ->orderBy('hour')
                ->pluck('count', 'hour')
                ->toArray();

            // Selected date (default today)
            $selectedDate = $request->input('selected_date', date('Y-m-d'));
            $selectedMonth = date('Y-m', strtotime($selectedDate));
            [$year, $month] = explode('-', $selectedMonth);

            // Base query filtered by this junior and date
            $query = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
                ->whereDate('updated_at', $selectedDate);
            $tquery = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
                ->whereDate('updated_at', $selectedDate);

            // Selected date totals for this junior
            $StotalCalls = $query->count();

            $ScalledAndMailedCalls = (clone $query)
                ->where('Exe_Remarks', 'Called & Mailed')
                ->whereDate('followup', $selectedDate)
                ->count();

            $SotherCalls = (clone $query)
                ->where(function ($q) {
                    $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                        ->orWhereNull('Exe_Remarks');
                })
                ->count();

            $Stotaltransfers = (clone $query)
                ->where(function ($q) {
                    $q->where('transfers', 1);
                })
                ->count();


            // Hour-wise "Called & Mailed" counts
            $hourlyCalledMailed = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
                ->where('created_by', 'like', "{$createdByKey}%")
                ->whereDate('updated_at', $selectedDate)
                ->where('Exe_Remarks', 'Called & Mailed')
                ->whereDate('followup', $selectedDate)
                ->groupBy('hour')
                ->pluck('count', 'hour')
                ->toArray();

            $hourlyOtherCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
                ->where('created_by', 'like', "{$createdByKey}%")
                ->whereDate('updated_at', $selectedDate)
                ->where(function ($q) {
                    $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                        ->orWhereNull('Exe_Remarks');
                })
                ->groupBy('hour')
                ->pluck('count', 'hour')
                ->toArray();

            $hourlyTransfers = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
                ->where('created_by', 'like', "{$createdByKey}%")
                ->whereDate('updated_at', $selectedDate)
                ->where('transfers', 1)
                ->groupBy('hour')
                ->pluck('count', 'hour')
                ->toArray();

            $holidayDates = Holiday::whereYear('holiday_date', $year)
                ->whereMonth('holiday_date', $month)
                ->where('is_holiday', 1)
                ->pluck('holiday_date')
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                ->toArray();

            // Initialize hour blocks (10 AM - 8 PM)
            $t8to9am = $hourlyCalledMailed[8] ?? 0;
            $t9to10am = $hourlyCalledMailed[9] ?? 0;
            $t10to11am = $hourlyCalledMailed[10] ?? 0;
            $t11to12pm = $hourlyCalledMailed[11] ?? 0;
            $t12to1pm  = $hourlyCalledMailed[12] ?? 0;
            $t1to2pm   = $hourlyCalledMailed[13] ?? 0;
            $t2to3pm   = $hourlyCalledMailed[14] ?? 0;
            $t3to4pm   = $hourlyCalledMailed[15] ?? 0;
            $t4to5pm   = $hourlyCalledMailed[16] ?? 0;
            $t5to6pm   = $hourlyCalledMailed[17] ?? 0;
            $t6to7pm   = $hourlyCalledMailed[18] ?? 0;
            $t7to8pm   = $hourlyCalledMailed[19] ?? 0;

            $tr8to9am  = $hourlyTransfers[8]  ?? 0;
            $tr9to10am = $hourlyTransfers[9]  ?? 0;
            $tr10to11am = $hourlyTransfers[10] ?? 0;
            $tr11to12pm = $hourlyTransfers[11] ?? 0;
            $tr12to1pm = $hourlyTransfers[12] ?? 0;
            $tr1to2pm  = $hourlyTransfers[13] ?? 0;
            $tr2to3pm  = $hourlyTransfers[14] ?? 0;
            $tr3to4pm  = $hourlyTransfers[15] ?? 0;
            $tr4to5pm  = $hourlyTransfers[16] ?? 0;
            $tr5to6pm  = $hourlyTransfers[17] ?? 0;
            $tr6to7pm  = $hourlyTransfers[18] ?? 0;
            $tr7to8pm  = $hourlyTransfers[19] ?? 0;

            $o8to9am = $hourlyOtherCalls[8] ?? 0;
            $o9to10am = $hourlyOtherCalls[9] ?? 0;
            $o10to11am = $hourlyOtherCalls[10] ?? 0;
            $o11to12pm = $hourlyOtherCalls[11] ?? 0;
            $o12to1pm  = $hourlyOtherCalls[12] ?? 0;
            $o1to2pm   = $hourlyOtherCalls[13] ?? 0;
            $o2to3pm   = $hourlyOtherCalls[14] ?? 0;
            $o3to4pm   = $hourlyOtherCalls[15] ?? 0;
            $o4to5pm   = $hourlyOtherCalls[16] ?? 0;
            $o5to6pm   = $hourlyOtherCalls[17] ?? 0;
            $o6to7pm   = $hourlyOtherCalls[18] ?? 0;
            $o7to8pm   = $hourlyOtherCalls[19] ?? 0;

            $Mtotaltransfers = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
                ->whereYear('updated_at', $year)
                ->whereMonth('updated_at', $month)
                ->where('transfers', 1)
                ->count();

            // Total "Called & Mailed" calls
            $McalledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
                ->whereYear('updated_at', $year)
                ->whereMonth('updated_at', $month)
                ->where('Exe_Remarks', 'Called & Mailed')
                ->count();

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

            $targetAchieved = GoogleSheetData::whereRaw(
                "created_by REGEXP '^{$juniorUser->id}\\\\|junior:[0-9]+\\\\|senior:[0-9]+\\\\|accountant(.*)?$'"
            )
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

            $today = now()->startOfDay();

            // Loop through each day
            foreach ($daysInMonth as $day) {
                /** @var Carbon $day */
                $dateStr = $day->format('Y-m-d');

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

            // --- Averages ---
            $MAvgTotalCalls       = $presentDays > 0 ? intval($McalledAndMailedCalls / $presentDays) : 0;
            $MAvgtotaltransfers   = $presentDays > 0 ? intval($Mtotaltransfers / $presentDays) : 0;

            $reports[] = compact(
                'totalCalls',
                'calledAndMailedCalls',
                'otherCalls',
                'juniorUser',
                'StotalCalls',
                'Stotaltransfers',
                'ScalledAndMailedCalls',
                'SotherCalls',
                'selectedDate',
                't8to9am',
                't9to10am',
                't10to11am',
                't11to12pm',
                't12to1pm',
                't1to2pm',
                't2to3pm',
                't3to4pm',
                't4to5pm',
                't5to6pm',
                't6to7pm',
                't7to8pm',
                'tr8to9am',
                'tr9to10am',
                'tr10to11am',
                'tr11to12pm',
                'tr12to1pm',
                'tr1to2pm',
                'tr2to3pm',
                'tr3to4pm',
                'tr4to5pm',
                'tr5to6pm',
                'tr6to7pm',
                'tr7to8pm',
                'o8to9am',
                'o9to10am',
                'o10to11am',
                'o11to12pm',
                'o12to1pm',
                'o1to2pm',
                'o2to3pm',
                'o3to4pm',
                'o4to5pm',
                'o5to6pm',
                'o6to7pm',
                'o7to8pm',
                'targetGiven',
                'targetAchieved',
                'targetYetToAchieve',
                'daysLeft',
                'presentDays',
                'absentDays',
                'workingDays',
                'nonWorkingDays',
                'MAvgTotalCalls',
                'Mtotaltransfers',
                'MAvgtotaltransfers',
            );
        }

        // Sort reports by ScalledAndMailedCalls (ascending)
        usort($reports, function ($a, $b) {
            return $a['ScalledAndMailedCalls'] <=> $b['ScalledAndMailedCalls'];
        });


        return view('reports.allreport', compact('reports'));
    }


    public function alljuniordaily(Request $request, $userId)
    {
        // Get the junior user
        $juniorUser =  User::where('id', $userId)
            ->where('is_deleted', 0)
            ->firstOrFail();
        $createdByKey = "{$juniorUser->id}|junior";

        // Total calls for this junior (including hierarchical keys)
        $totalCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")->count();

        // Total "Called & Mailed" calls for this junior
        $calledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        // Total other calls for this junior
        $otherCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        // Group data by hour of updated_at (for this junior)
        $hourlyCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Selected date (default today)
        $selectedDate = $request->input('selected_date', date('Y-m-d'));
        $selectedMonth = date('Y-m', strtotime($selectedDate));
        [$year, $month] = explode('-', $selectedMonth);

        // Base query filtered by this junior and date
        $query = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate);
        $tquery = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate);

        // Selected date totals for this junior
        $StotalCalls = $query->count();

        $ScalledAndMailedCalls = (clone $query)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereDate('followup', $selectedDate)
            ->count();

        $SotherCalls = (clone $query)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();

        $Stotaltransfers = (clone $query)
            ->where(function ($q) {
                $q->where('transfers', 1);
            })
            ->whereDate('transfer_date', $selectedDate)
            ->count();


        // Hour-wise "Called & Mailed" counts
        $hourlyCalledMailed = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereDate('followup', $selectedDate)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $hourlyOtherCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $hourlyTransfers = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('transfer_date', $selectedDate)
            ->where('transfers', 1)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $holidayDates = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where('is_holiday', 1)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Initialize hour blocks (10 AM - 8 PM)
        $t8to9am = $hourlyCalledMailed[8] ?? 0;
        $t9to10am = $hourlyCalledMailed[9] ?? 0;
        $t10to11am = $hourlyCalledMailed[10] ?? 0;
        $t11to12pm = $hourlyCalledMailed[11] ?? 0;
        $t12to1pm  = $hourlyCalledMailed[12] ?? 0;
        $t1to2pm   = $hourlyCalledMailed[13] ?? 0;
        $t2to3pm   = $hourlyCalledMailed[14] ?? 0;
        $t3to4pm   = $hourlyCalledMailed[15] ?? 0;
        $t4to5pm   = $hourlyCalledMailed[16] ?? 0;
        $t5to6pm   = $hourlyCalledMailed[17] ?? 0;
        $t6to7pm   = $hourlyCalledMailed[18] ?? 0;
        $t7to8pm   = $hourlyCalledMailed[19] ?? 0;

        $tr8to9am  = $hourlyTransfers[8]  ?? 0;
        $tr9to10am = $hourlyTransfers[9]  ?? 0;
        $tr10to11am = $hourlyTransfers[10] ?? 0;
        $tr11to12pm = $hourlyTransfers[11] ?? 0;
        $tr12to1pm = $hourlyTransfers[12] ?? 0;
        $tr1to2pm  = $hourlyTransfers[13] ?? 0;
        $tr2to3pm  = $hourlyTransfers[14] ?? 0;
        $tr3to4pm  = $hourlyTransfers[15] ?? 0;
        $tr4to5pm  = $hourlyTransfers[16] ?? 0;
        $tr5to6pm  = $hourlyTransfers[17] ?? 0;
        $tr6to7pm  = $hourlyTransfers[18] ?? 0;
        $tr7to8pm  = $hourlyTransfers[19] ?? 0;

        $o8to9am = $hourlyOtherCalls[8] ?? 0;
        $o9to10am = $hourlyOtherCalls[9] ?? 0;
        $o10to11am = $hourlyOtherCalls[10] ?? 0;
        $o11to12pm = $hourlyOtherCalls[11] ?? 0;
        $o12to1pm  = $hourlyOtherCalls[12] ?? 0;
        $o1to2pm   = $hourlyOtherCalls[13] ?? 0;
        $o2to3pm   = $hourlyOtherCalls[14] ?? 0;
        $o3to4pm   = $hourlyOtherCalls[15] ?? 0;
        $o4to5pm   = $hourlyOtherCalls[16] ?? 0;
        $o5to6pm   = $hourlyOtherCalls[17] ?? 0;
        $o6to7pm   = $hourlyOtherCalls[18] ?? 0;
        $o7to8pm   = $hourlyOtherCalls[19] ?? 0;

        $Mtotaltransfers = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('transfers', 1)
            ->count();

        // Total "Called & Mailed" calls
        $McalledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

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

        $targetAchieved = GoogleSheetData::whereRaw(
            "created_by REGEXP '^{$juniorUser->id}\\\\|junior:[0-9]+\\\\|senior:[0-9]+\\\\|accountant(.*)?$'"
        )
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

        $today = now()->startOfDay();

        // Loop through each day
        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

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

        // --- Averages ---
        $MAvgTotalCalls       = $presentDays > 0 ? intval($McalledAndMailedCalls / $presentDays) : 0;
        $MAvgtotaltransfers   = $presentDays > 0 ? intval($Mtotaltransfers / $presentDays) : 0;

        return view('reports.alljuniordaily', compact(
            'totalCalls',
            'calledAndMailedCalls',
            'otherCalls',
            'juniorUser',
            'StotalCalls',
            'Stotaltransfers',
            'ScalledAndMailedCalls',
            'SotherCalls',
            'selectedDate',
            't8to9am',
            't9to10am',
            't10to11am',
            't11to12pm',
            't12to1pm',
            't1to2pm',
            't2to3pm',
            't3to4pm',
            't4to5pm',
            't5to6pm',
            't6to7pm',
            't7to8pm',
            'tr8to9am',
            'tr9to10am',
            'tr10to11am',
            'tr11to12pm',
            'tr12to1pm',
            'tr1to2pm',
            'tr2to3pm',
            'tr3to4pm',
            'tr4to5pm',
            'tr5to6pm',
            'tr6to7pm',
            'tr7to8pm',
            'o8to9am',
            'o9to10am',
            'o10to11am',
            'o11to12pm',
            'o12to1pm',
            'o1to2pm',
            'o2to3pm',
            'o3to4pm',
            'o4to5pm',
            'o5to6pm',
            'o6to7pm',
            'o7to8pm',
            'targetGiven',
            'targetAchieved',
            'targetYetToAchieve',
            'daysLeft',
            'presentDays',
            'absentDays',
            'workingDays',
            'nonWorkingDays',
            'MAvgTotalCalls',
            'Mtotaltransfers',
            'MAvgtotaltransfers',
        ));
    }

    public function alljuniorweekly(Request $request, $userId)
    {
        // Get the junior user
        $juniorUser =  User::where('id', $userId)
            ->where('is_deleted', 0)
            ->firstOrFail();
        $createdByKey = "{$juniorUser->id}|junior";

        // Total calls for this junior (including hierarchical keys)
        $totalCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")->count();

        // Total "Called & Mailed" calls for this junior
        $calledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        // Total other calls for this junior
        $otherCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        // Group data by hour of updated_at (for this junior)
        $hourlyCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $selectedWeek = trim(
            $request->input('selected_week', now()->format('Y-\WW'))
        );

        [$year, $week] = explode('-W', $selectedWeek);
        $weekCarbon = Carbon::now()->setISODate((int)$year, (int)$week);


        $weekStart = $weekCarbon->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd   = $weekCarbon->endOfWeek(Carbon::SATURDAY)->endOfDay();

        $weekDates = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = $weekStart->copy()->addDays($i)->format('Y-m-d');
        }

        // Optional readable variables if you really want them
        [$firstdate, $seconddate, $thirddate, $fourthdate, $fifthdate, $sixthdate, $seventhdate] = $weekDates;

        // For compatibility with existing month logic
        $year  = $weekStart->year;
        $month = $weekStart->month;
        $selectedMonth = $weekStart->format('Y-m');

        // Base query filtered by this junior and date
        $query = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            });

        $tquery = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            });

        // Selected date totals for this junior
        $StotalCalls = $query->count();

        $ScalledAndMailedCalls = (clone $query)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();


        $SotherCalls = (clone $query)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();

        $Stotaltransfers = (clone $query)
            ->where(function ($q) {
                $q->where('transfers', 1);
            })
            ->count();


        // Hour-wise "Called & Mailed" counts
        $hourlyCalledMailed = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();


        $hourlyOtherCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();


        $hourlyTransfers = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where('transfers', 1)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();


        $holidayDates = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where('is_holiday', 1)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Initialize hour blocks (10 AM - 8 PM)
        $t8to9am = $hourlyCalledMailed[8] ?? 0;
        $t9to10am = $hourlyCalledMailed[9] ?? 0;
        $t10to11am = $hourlyCalledMailed[10] ?? 0;
        $t11to12pm = $hourlyCalledMailed[11] ?? 0;
        $t12to1pm  = $hourlyCalledMailed[12] ?? 0;
        $t1to2pm   = $hourlyCalledMailed[13] ?? 0;
        $t2to3pm   = $hourlyCalledMailed[14] ?? 0;
        $t3to4pm   = $hourlyCalledMailed[15] ?? 0;
        $t4to5pm   = $hourlyCalledMailed[16] ?? 0;
        $t5to6pm   = $hourlyCalledMailed[17] ?? 0;
        $t6to7pm   = $hourlyCalledMailed[18] ?? 0;
        $t7to8pm   = $hourlyCalledMailed[19] ?? 0;

        $tr8to9am  = $hourlyTransfers[8]  ?? 0;
        $tr9to10am = $hourlyTransfers[9]  ?? 0;
        $tr10to11am = $hourlyTransfers[10] ?? 0;
        $tr11to12pm = $hourlyTransfers[11] ?? 0;
        $tr12to1pm = $hourlyTransfers[12] ?? 0;
        $tr1to2pm  = $hourlyTransfers[13] ?? 0;
        $tr2to3pm  = $hourlyTransfers[14] ?? 0;
        $tr3to4pm  = $hourlyTransfers[15] ?? 0;
        $tr4to5pm  = $hourlyTransfers[16] ?? 0;
        $tr5to6pm  = $hourlyTransfers[17] ?? 0;
        $tr6to7pm  = $hourlyTransfers[18] ?? 0;
        $tr7to8pm  = $hourlyTransfers[19] ?? 0;

        $o8to9am = $hourlyOtherCalls[8] ?? 0;
        $o9to10am = $hourlyOtherCalls[9] ?? 0;
        $o10to11am = $hourlyOtherCalls[10] ?? 0;
        $o11to12pm = $hourlyOtherCalls[11] ?? 0;
        $o12to1pm  = $hourlyOtherCalls[12] ?? 0;
        $o1to2pm   = $hourlyOtherCalls[13] ?? 0;
        $o2to3pm   = $hourlyOtherCalls[14] ?? 0;
        $o3to4pm   = $hourlyOtherCalls[15] ?? 0;
        $o4to5pm   = $hourlyOtherCalls[16] ?? 0;
        $o5to6pm   = $hourlyOtherCalls[17] ?? 0;
        $o6to7pm   = $hourlyOtherCalls[18] ?? 0;
        $o7to8pm   = $hourlyOtherCalls[19] ?? 0;

        $Mtotaltransfers = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('transfers', 1)
            ->count();

        // Total "Called & Mailed" calls
        $McalledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

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

        $targetAchieved = GoogleSheetData::whereRaw(
            "created_by REGEXP '^{$juniorUser->id}\\\\|junior:[0-9]+\\\\|senior:[0-9]+\\\\|accountant(.*)?$'"
        )
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

        $today = now()->startOfDay();

        // Loop through each day
        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

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

        // --- Averages ---
        $MAvgTotalCalls       = $presentDays > 0 ? intval($McalledAndMailedCalls / $presentDays) : 0;
        $MAvgtotaltransfers   = $presentDays > 0 ? intval($Mtotaltransfers / $presentDays) : 0;

        return view('reports.alljuniorweekly', compact(
            'totalCalls',
            'calledAndMailedCalls',
            'otherCalls',
            'juniorUser',
            'StotalCalls',
            'Stotaltransfers',
            'ScalledAndMailedCalls',
            'SotherCalls',
            'selectedWeek',
            't8to9am',
            't9to10am',
            't10to11am',
            't11to12pm',
            't12to1pm',
            't1to2pm',
            't2to3pm',
            't3to4pm',
            't4to5pm',
            't5to6pm',
            't6to7pm',
            't7to8pm',
            'tr8to9am',
            'tr9to10am',
            'tr10to11am',
            'tr11to12pm',
            'tr12to1pm',
            'tr1to2pm',
            'tr2to3pm',
            'tr3to4pm',
            'tr4to5pm',
            'tr5to6pm',
            'tr6to7pm',
            'tr7to8pm',
            'o8to9am',
            'o9to10am',
            'o10to11am',
            'o11to12pm',
            'o12to1pm',
            'o1to2pm',
            'o2to3pm',
            'o3to4pm',
            'o4to5pm',
            'o5to6pm',
            'o6to7pm',
            'o7to8pm',
            'targetGiven',
            'targetAchieved',
            'targetYetToAchieve',
            'daysLeft',
            'presentDays',
            'absentDays',
            'workingDays',
            'nonWorkingDays',
            'MAvgTotalCalls',
            'Mtotaltransfers',
            'MAvgtotaltransfers',
        ));
    }

    public function alltrainerdaily(Request $request, $userId)
    {
        // Get the trainer user
        $trainerUser =  User::where('id', $userId)
            ->where('is_deleted', 0)
            ->firstOrFail();
        $createdByKey = "{$trainerUser->id}|trainer";

        // ================================
        // Main logic with LIKE filters
        // ================================

        // Total calls for this trainer (including hierarchical keys)
        $totalCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")->count();

        // Total "Called & Mailed" calls for this trainer
        $calledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        // Total other calls for this trainer
        $otherCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        // Group data by hour of updated_at (for this trainer)
        $hourlyCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Selected date (default today)
        $selectedDate = $request->input('selected_date', date('Y-m-d'));

        // Base query filtered by this trainer and date
        $query = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate);

        // Selected date totals for this trainer
        $StotalCalls = $query->count();

        $ScalledAndMailedCalls = (clone $query)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        $SotherCalls = (clone $query)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        // Hour-wise "Called & Mailed" counts
        $hourlyCalledMailed = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $hourlyOtherCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();



        // Initialize hour blocks (10 AM - 8 PM)
        $t8to9am = $hourlyCalledMailed[8] ?? 0;
        $t9to10am = $hourlyCalledMailed[9] ?? 0;
        $t10to11am = $hourlyCalledMailed[10] ?? 0;
        $t11to12pm = $hourlyCalledMailed[11] ?? 0;
        $t12to1pm  = $hourlyCalledMailed[12] ?? 0;
        $t1to2pm   = $hourlyCalledMailed[13] ?? 0;
        $t2to3pm   = $hourlyCalledMailed[14] ?? 0;
        $t3to4pm   = $hourlyCalledMailed[15] ?? 0;
        $t4to5pm   = $hourlyCalledMailed[16] ?? 0;
        $t5to6pm   = $hourlyCalledMailed[17] ?? 0;
        $t6to7pm   = $hourlyCalledMailed[18] ?? 0;
        $t7to8pm   = $hourlyCalledMailed[19] ?? 0;

        $o8to9am = $hourlyOtherCalls[8] ?? 0;
        $o9to10am = $hourlyOtherCalls[9] ?? 0;
        $o10to11am = $hourlyOtherCalls[10] ?? 0;
        $o11to12pm = $hourlyOtherCalls[11] ?? 0;
        $o12to1pm  = $hourlyOtherCalls[12] ?? 0;
        $o1to2pm   = $hourlyOtherCalls[13] ?? 0;
        $o2to3pm   = $hourlyOtherCalls[14] ?? 0;
        $o3to4pm   = $hourlyOtherCalls[15] ?? 0;
        $o4to5pm   = $hourlyOtherCalls[16] ?? 0;
        $o5to6pm   = $hourlyOtherCalls[17] ?? 0;
        $o6to7pm   = $hourlyOtherCalls[18] ?? 0;
        $o7to8pm   = $hourlyOtherCalls[19] ?? 0;

        return view('reports.alltrainerdaily', compact(
            'totalCalls',
            'calledAndMailedCalls',
            'otherCalls',
            'StotalCalls',
            'ScalledAndMailedCalls',
            'SotherCalls',
            'selectedDate',
            't8to9am',
            't9to10am',
            't10to11am',
            't11to12pm',
            't12to1pm',
            't1to2pm',
            't2to3pm',
            't3to4pm',
            't4to5pm',
            't5to6pm',
            't6to7pm',
            't7to8pm',
            'o8to9am',
            'o9to10am',
            'o10to11am',
            'o11to12pm',
            'o12to1pm',
            'o1to2pm',
            'o2to3pm',
            'o3to4pm',
            'o4to5pm',
            'o5to6pm',
            'o6to7pm',
            'o7to8pm'
        ));
    }


    public function allaccountantdaily(Request $request, $userId)
    {
        // Get the accountant user
        $juniorUser =  User::where('id', $userId)
            ->where('is_deleted', 0)
            ->firstOrFail();
        $createdByKey = "{$juniorUser->id}|junior";

        // ================================
        // Main logic with LIKE filters
        // ================================

        // Total calls for this junior (including hierarchical keys)
        $totalCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")->count();

        // Total "Ready To Pay" calls for this junior
        $calledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();

        // Total other calls for this junior
        $otherCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Ready To Pay')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        // Group data by hour of updated_at (for this junior)
        $hourlyCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Selected date (default today)
        $selectedDate = $request->input('selected_date', date('Y-m-d'));

        // Base query filtered by this junior and date
        $query = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate);

        // Selected date totals for this junior
        $StotalCalls = $query->count();

        $ScalledAndMailedCalls = (clone $query)
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();

        $SotherCalls = (clone $query)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Ready To Pay')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        // Hour-wise "Ready To Pay" counts
        $hourlyCalledMailed = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Ready To Pay')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $hourlyOtherCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Ready To Pay')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();



        // Initialize hour blocks (10 AM - 8 PM)
        $t8to9am = $hourlyCalledMailed[8] ?? 0;
        $t9to10am = $hourlyCalledMailed[9] ?? 0;
        $t10to11am = $hourlyCalledMailed[10] ?? 0;
        $t11to12pm = $hourlyCalledMailed[11] ?? 0;
        $t12to1pm  = $hourlyCalledMailed[12] ?? 0;
        $t1to2pm   = $hourlyCalledMailed[13] ?? 0;
        $t2to3pm   = $hourlyCalledMailed[14] ?? 0;
        $t3to4pm   = $hourlyCalledMailed[15] ?? 0;
        $t4to5pm   = $hourlyCalledMailed[16] ?? 0;
        $t5to6pm   = $hourlyCalledMailed[17] ?? 0;
        $t6to7pm   = $hourlyCalledMailed[18] ?? 0;
        $t7to8pm   = $hourlyCalledMailed[19] ?? 0;

        $o8to9am = $hourlyOtherCalls[8] ?? 0;
        $o9to10am = $hourlyOtherCalls[9] ?? 0;
        $o10to11am = $hourlyOtherCalls[10] ?? 0;
        $o11to12pm = $hourlyOtherCalls[11] ?? 0;
        $o12to1pm  = $hourlyOtherCalls[12] ?? 0;
        $o1to2pm   = $hourlyOtherCalls[13] ?? 0;
        $o2to3pm   = $hourlyOtherCalls[14] ?? 0;
        $o3to4pm   = $hourlyOtherCalls[15] ?? 0;
        $o4to5pm   = $hourlyOtherCalls[16] ?? 0;
        $o5to6pm   = $hourlyOtherCalls[17] ?? 0;
        $o6to7pm   = $hourlyOtherCalls[18] ?? 0;
        $o7to8pm   = $hourlyOtherCalls[19] ?? 0;

        return view('reports.allaccountantdaily', compact(
            'totalCalls',
            'calledAndMailedCalls',
            'otherCalls',
            'StotalCalls',
            'ScalledAndMailedCalls',
            'SotherCalls',
            'selectedDate',
            't8to9am',
            'juniorUser',
            't9to10am',
            't10to11am',
            't11to12pm',
            't12to1pm',
            't1to2pm',
            't2to3pm',
            't3to4pm',
            't4to5pm',
            't5to6pm',
            't6to7pm',
            't7to8pm',
            'o8to9am',
            'o9to10am',
            'o10to11am',
            'o11to12pm',
            'o12to1pm',
            'o1to2pm',
            'o2to3pm',
            'o3to4pm',
            'o4to5pm',
            'o5to6pm',
            'o6to7pm',
            'o7to8pm'
        ));
    }

    public function allseniordaily(Request $request, $userId)
    {
        $juniorUser =  User::where('id', $userId)
            ->where('is_deleted', 0)
            ->firstOrFail();
        $createdByKey = "{$juniorUser->id}|senior";

        // ================================
        // Main logic with LIKE filters
        // ================================
        // Total "Called & Mailed" calls
        $calledAndMailedCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        $selffollowupCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        // Total "Ready To Pay" calls
        $readyToPaidCalls = GoogleSheetData::where(function ($q) {
            $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
        })
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();

        $followUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        $transferedfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->count();


        // Total other calls (excluding Called & Mailed)
        $otherCalls = GoogleSheetData::where('created_by', 'like', "%{$createdByKey}%")
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();

        $totalCalls =
            $calledAndMailedCalls
            + $selffollowupCalls
            + $readyToPaidCalls
            + $followUpCalls
            + $transferedfollowUpCalls;


        // Selected date (default today)
        $selectedDate = $request->input('selected_date', date('Y-m-d'));
        $selectedMonth = date('Y-m', strtotime($selectedDate));
        [$year, $month] = explode('-', $selectedMonth);

        // Base query filtered by this senior and date
        $query = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate);
        $tquery = GoogleSheetData::where('created_by', 'like', "%{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate);

        // Selected date totals
        // Self follow-up calls (Called & Mailed / Ready To Pay with TransferRemark)
        $SselffollowupCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();


        // Ready To Pay calls
        $SreadyToPaidCalls = GoogleSheetData::where(function ($q) {
            $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
        })
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();

        // Follow-up calls (Called & Mailed with TransferRemark)
        $SfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        // Transferred follow-up calls
        $StransferedfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->count();

        // Other calls (excluding Called & Mailed)
        $SotherCalls = (clone $tquery)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        $ScalledAndMailedCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        $StotalCalls =
            $ScalledAndMailedCalls
            + $SselffollowupCalls
            + $SreadyToPaidCalls
            + $SfollowUpCalls
            + $StransferedfollowUpCalls;

        // Hour-wise "Ready To Pay" counts
        $hourlyReadyToPaid = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where(function ($q) {
                $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                    ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
            })
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Ready To Pay')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Hourly Called & Mailed
        $hourlyCalledAndMailed = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'")
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Hourly Self Follow-up (Called & Mailed / Ready To Pay with TransferRemark)
        $hourlySelfFollowUp = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'")
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Hourly Follow-up (Called & Mailed with TransferRemark)
        $hourlyFollowUp = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Hourly Transferred Follow-up
        $hourlyTransferredFollowUp = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Hourly Other Calls (excluding Called & Mailed)
        $hourlyOtherCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "%{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $holidayDates = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where('is_holiday', 1)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Initialize hour blocks (10 AM - 8 PM)
        $c8to9am = $hourlyCalledAndMailed[8] ?? 0;
        $c9to10am = $hourlyCalledAndMailed[9] ?? 0;
        $c10to11am = $hourlyCalledAndMailed[10] ?? 0;
        $c11to12pm = $hourlyCalledAndMailed[11] ?? 0;
        $c12to1pm  = $hourlyCalledAndMailed[12] ?? 0;
        $c1to2pm   = $hourlyCalledAndMailed[13] ?? 0;
        $c2to3pm   = $hourlyCalledAndMailed[14] ?? 0;
        $c3to4pm   = $hourlyCalledAndMailed[15] ?? 0;
        $c4to5pm   = $hourlyCalledAndMailed[16] ?? 0;
        $c5to6pm   = $hourlyCalledAndMailed[17] ?? 0;
        $c6to7pm   = $hourlyCalledAndMailed[18] ?? 0;
        $c7to8pm   = $hourlyCalledAndMailed[19] ?? 0;

        $sf8to9am = $hourlySelfFollowUp[8] ?? 0;
        $sf9to10am = $hourlySelfFollowUp[9] ?? 0;
        $sf10to11am = $hourlySelfFollowUp[10] ?? 0;
        $sf11to12pm = $hourlySelfFollowUp[11] ?? 0;
        $sf12to1pm  = $hourlySelfFollowUp[12] ?? 0;
        $sf1to2pm   = $hourlySelfFollowUp[13] ?? 0;
        $sf2to3pm   = $hourlySelfFollowUp[14] ?? 0;
        $sf3to4pm   = $hourlySelfFollowUp[15] ?? 0;
        $sf4to5pm   = $hourlySelfFollowUp[16] ?? 0;
        $sf5to6pm   = $hourlySelfFollowUp[17] ?? 0;
        $sf6to7pm   = $hourlySelfFollowUp[18] ?? 0;
        $sf7to8pm   = $hourlySelfFollowUp[19] ?? 0;

        $r8to9am = $hourlyReadyToPaid[8] ?? 0;
        $r9to10am = $hourlyReadyToPaid[9] ?? 0;
        $r10to11am = $hourlyReadyToPaid[10] ?? 0;
        $r11to12pm = $hourlyReadyToPaid[11] ?? 0;
        $r12to1pm  = $hourlyReadyToPaid[12] ?? 0;
        $r1to2pm   = $hourlyReadyToPaid[13] ?? 0;
        $r2to3pm   = $hourlyReadyToPaid[14] ?? 0;
        $r3to4pm   = $hourlyReadyToPaid[15] ?? 0;
        $r4to5pm   = $hourlyReadyToPaid[16] ?? 0;
        $r5to6pm   = $hourlyReadyToPaid[17] ?? 0;
        $r6to7pm   = $hourlyReadyToPaid[18] ?? 0;
        $r7to8pm   = $hourlyReadyToPaid[19] ?? 0;

        $f8to9am = $hourlyFollowUp[8] ?? 0;
        $f9to10am = $hourlyFollowUp[9] ?? 0;
        $f10to11am = $hourlyFollowUp[10] ?? 0;
        $f11to12pm = $hourlyFollowUp[11] ?? 0;
        $f12to1pm  = $hourlyFollowUp[12] ?? 0;
        $f1to2pm   = $hourlyFollowUp[13] ?? 0;
        $f2to3pm   = $hourlyFollowUp[14] ?? 0;
        $f3to4pm   = $hourlyFollowUp[15] ?? 0;
        $f4to5pm   = $hourlyFollowUp[16] ?? 0;
        $f5to6pm   = $hourlyFollowUp[17] ?? 0;
        $f6to7pm   = $hourlyFollowUp[18] ?? 0;
        $f7to8pm   = $hourlyFollowUp[19] ?? 0;

        $tf8to9am = $hourlyTransferredFollowUp[8] ?? 0;
        $tf9to10am = $hourlyTransferredFollowUp[9] ?? 0;
        $tf10to11am = $hourlyTransferredFollowUp[10] ?? 0;
        $tf11to12pm = $hourlyTransferredFollowUp[11] ?? 0;
        $tf12to1pm  = $hourlyTransferredFollowUp[12] ?? 0;
        $tf1to2pm   = $hourlyTransferredFollowUp[13] ?? 0;
        $tf2to3pm   = $hourlyTransferredFollowUp[14] ?? 0;
        $tf3to4pm   = $hourlyTransferredFollowUp[15] ?? 0;
        $tf4to5pm   = $hourlyTransferredFollowUp[16] ?? 0;
        $tf5to6pm   = $hourlyTransferredFollowUp[17] ?? 0;
        $tf6to7pm   = $hourlyTransferredFollowUp[18] ?? 0;
        $tf7to8pm   = $hourlyTransferredFollowUp[19] ?? 0;

        $o8to9am = $hourlyOtherCalls[8] ?? 0;
        $o9to10am = $hourlyOtherCalls[9] ?? 0;
        $o10to11am = $hourlyOtherCalls[10] ?? 0;
        $o11to12pm = $hourlyOtherCalls[11] ?? 0;
        $o12to1pm  = $hourlyOtherCalls[12] ?? 0;
        $o1to2pm   = $hourlyOtherCalls[13] ?? 0;
        $o2to3pm   = $hourlyOtherCalls[14] ?? 0;
        $o3to4pm   = $hourlyOtherCalls[15] ?? 0;
        $o4to5pm   = $hourlyOtherCalls[16] ?? 0;
        $o5to6pm   = $hourlyOtherCalls[17] ?? 0;
        $o6to7pm   = $hourlyOtherCalls[18] ?? 0;
        $o7to8pm   = $hourlyOtherCalls[19] ?? 0;

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

        $targetAchieved = GoogleSheetData::whereRaw(
            "created_by REGEXP '^{$juniorUser->id}\\\\|senior:[0-9]+\\\\|senior:[0-9]+\\\\|accountant(.*)?$'"
        )
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

        $presentDays    = 0;
        $halfDays       = 0;
        $absentDays     = 0;
        $workingDays    = 0;
        $nonWorkingDays = 0;

        $today = now()->startOfDay(); // 🔒 freeze today until end of day

        // Loop through each day
        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

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


        return view('reports.allseniordaily', compact(
            'totalCalls',
            'juniorUser',
            'calledAndMailedCalls',
            'selffollowupCalls',
            'readyToPaidCalls',
            'followUpCalls',
            'transferedfollowUpCalls',
            'otherCalls',

            'SselffollowupCalls',
            'StotalCalls',
            'ScalledAndMailedCalls',
            'SreadyToPaidCalls',
            'StransferedfollowUpCalls',
            'SfollowUpCalls',
            'SotherCalls',
            'selectedDate',


            'r8to9am',
            'r9to10am',
            'r10to11am',
            'r11to12pm',
            'r12to1pm',
            'r1to2pm',
            'r2to3pm',
            'r3to4pm',
            'r4to5pm',
            'r5to6pm',
            'r6to7pm',
            'r7to8pm',


            'c8to9am',
            'c9to10am',
            'c10to11am',
            'c11to12pm',
            'c12to1pm',
            'c1to2pm',
            'c2to3pm',
            'c3to4pm',
            'c4to5pm',
            'c5to6pm',
            'c6to7pm',
            'c7to8pm',


            'sf8to9am',
            'sf9to10am',
            'sf10to11am',
            'sf11to12pm',
            'sf12to1pm',
            'sf1to2pm',
            'sf2to3pm',
            'sf3to4pm',
            'sf4to5pm',
            'sf5to6pm',
            'sf6to7pm',
            'sf7to8pm',


            'f8to9am',
            'f9to10am',
            'f10to11am',
            'f11to12pm',
            'f12to1pm',
            'f1to2pm',
            'f2to3pm',
            'f3to4pm',
            'f4to5pm',
            'f5to6pm',
            'f6to7pm',
            'f7to8pm',


            'tf8to9am',
            'tf9to10am',
            'tf10to11am',
            'tf11to12pm',
            'tf12to1pm',
            'tf1to2pm',
            'tf2to3pm',
            'tf3to4pm',
            'tf4to5pm',
            'tf5to6pm',
            'tf6to7pm',
            'tf7to8pm',


            'o8to9am',
            'o9to10am',
            'o10to11am',
            'o11to12pm',
            'o12to1pm',
            'o1to2pm',
            'o2to3pm',
            'o3to4pm',
            'o4to5pm',
            'o5to6pm',
            'o6to7pm',
            'o7to8pm',


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

    public function allseniorweekly(Request $request, $userId)
    {
        $juniorUser =  User::where('id', $userId)
            ->where('is_deleted', 0)
            ->firstOrFail();
        $createdByKey = "{$juniorUser->id}|senior";

        // ================================
        // Main logic with LIKE filters
        // ================================
        // Total "Called & Mailed" calls
        $calledAndMailedCalls = GoogleSheetData::where(
            'created_by',
            "{$juniorUser->id}|senior:0|senior"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();


        $selffollowupCalls = GoogleSheetData::where(
            'created_by',
            "{$juniorUser->id}|senior:0|senior"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        // Total "Ready To Pay" calls
        $readyToPaidCalls = GoogleSheetData::where(function ($q) {
            $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
        })
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();

        $followUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        $transferedfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->count();


        // Total other calls (excluding Called & Mailed)
        $otherCalls = GoogleSheetData::where('created_by', 'like', "%{$createdByKey}%")
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();

        $totalCalls =
            $calledAndMailedCalls
            + $selffollowupCalls
            + $readyToPaidCalls
            + $followUpCalls
            + $transferedfollowUpCalls;


        $selectedWeek = trim(
            $request->input('selected_week', now()->format('Y-\WW'))
        );

        [$year, $week] = explode('-W', $selectedWeek);
        $weekCarbon = Carbon::now()->setISODate((int)$year, (int)$week);


        $weekStart = $weekCarbon->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd   = $weekCarbon->endOfWeek(Carbon::SATURDAY)->endOfDay();

        $weekDates = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = $weekStart->copy()->addDays($i)->format('Y-m-d');
        }

        // Optional readable variables if you really want them
        [$firstdate, $seconddate, $thirddate, $fourthdate, $fifthdate, $sixthdate, $seventhdate] = $weekDates;

        // For compatibility with existing month logic
        $year  = $weekStart->year;
        $month = $weekStart->month;
        $selectedMonth = $weekStart->format('Y-m');

        // Base query filtered by this senior and date
        $query = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            });

        $tquery = GoogleSheetData::where('created_by', 'like', "%{$createdByKey}%")
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            });

        // Selected date totals
        // Self follow-up calls (Called & Mailed / Ready To Pay with TransferRemark)
        $SselffollowupCalls = GoogleSheetData::where(
            'created_by',
            "{$juniorUser->id}|senior:0|senior"
        )
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();


        // Ready To Pay calls
        $SreadyToPaidCalls = GoogleSheetData::where(function ($q) {
            $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
        })
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();

        // Follow-up calls (Called & Mailed with TransferRemark)
        $SfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        // Transferred follow-up calls
        $StransferedfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->count();

        // Other calls (excluding Called & Mailed)
        $SotherCalls = (clone $tquery)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        $ScalledAndMailedCalls = GoogleSheetData::where(
            'created_by',
            "{$juniorUser->id}|senior:0|senior"
        )
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        $StotalCalls =
            $ScalledAndMailedCalls
            + $SselffollowupCalls
            + $SreadyToPaidCalls
            + $SfollowUpCalls
            + $StransferedfollowUpCalls;

        // Hour-wise "Ready To Pay" counts
        $hourlyReadyToPaid = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where(function ($q) {
                $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                    ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
            })
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where('Exe_Remarks', 'Ready To Pay')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Hourly Called & Mailed
        $hourlyCalledAndMailed = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where(
                'created_by',
                "{$juniorUser->id}|senior:0|senior"
            )
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where('Exe_Remarks', 'Called & Mailed')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Hourly Self Follow-up (Called & Mailed / Ready To Pay with TransferRemark)
        $hourlySelfFollowUp = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where(
                'created_by',
                "{$juniorUser->id}|senior:0|senior"
            )
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Hourly Follow-up (Called & Mailed with TransferRemark)
        $hourlyFollowUp = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Hourly Transferred Follow-up
        $hourlyTransferredFollowUp = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Hourly Other Calls (excluding Called & Mailed)
        $hourlyOtherCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "%{$createdByKey}%")
            ->where(function ($q) use ($weekDates) {
                foreach ($weekDates as $date) {
                    $q->orWhereDate('updated_at', $date);
                }
            })
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $holidayDates = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where('is_holiday', 1)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Initialize hour blocks (10 AM - 8 PM)
        $c8to9am = $hourlyCalledAndMailed[8] ?? 0;
        $c9to10am = $hourlyCalledAndMailed[9] ?? 0;
        $c10to11am = $hourlyCalledAndMailed[10] ?? 0;
        $c11to12pm = $hourlyCalledAndMailed[11] ?? 0;
        $c12to1pm  = $hourlyCalledAndMailed[12] ?? 0;
        $c1to2pm   = $hourlyCalledAndMailed[13] ?? 0;
        $c2to3pm   = $hourlyCalledAndMailed[14] ?? 0;
        $c3to4pm   = $hourlyCalledAndMailed[15] ?? 0;
        $c4to5pm   = $hourlyCalledAndMailed[16] ?? 0;
        $c5to6pm   = $hourlyCalledAndMailed[17] ?? 0;
        $c6to7pm   = $hourlyCalledAndMailed[18] ?? 0;
        $c7to8pm   = $hourlyCalledAndMailed[19] ?? 0;

        $sf8to9am = $hourlySelfFollowUp[8] ?? 0;
        $sf9to10am = $hourlySelfFollowUp[9] ?? 0;
        $sf10to11am = $hourlySelfFollowUp[10] ?? 0;
        $sf11to12pm = $hourlySelfFollowUp[11] ?? 0;
        $sf12to1pm  = $hourlySelfFollowUp[12] ?? 0;
        $sf1to2pm   = $hourlySelfFollowUp[13] ?? 0;
        $sf2to3pm   = $hourlySelfFollowUp[14] ?? 0;
        $sf3to4pm   = $hourlySelfFollowUp[15] ?? 0;
        $sf4to5pm   = $hourlySelfFollowUp[16] ?? 0;
        $sf5to6pm   = $hourlySelfFollowUp[17] ?? 0;
        $sf6to7pm   = $hourlySelfFollowUp[18] ?? 0;
        $sf7to8pm   = $hourlySelfFollowUp[19] ?? 0;

        $r8to9am = $hourlyReadyToPaid[8] ?? 0;
        $r9to10am = $hourlyReadyToPaid[9] ?? 0;
        $r10to11am = $hourlyReadyToPaid[10] ?? 0;
        $r11to12pm = $hourlyReadyToPaid[11] ?? 0;
        $r12to1pm  = $hourlyReadyToPaid[12] ?? 0;
        $r1to2pm   = $hourlyReadyToPaid[13] ?? 0;
        $r2to3pm   = $hourlyReadyToPaid[14] ?? 0;
        $r3to4pm   = $hourlyReadyToPaid[15] ?? 0;
        $r4to5pm   = $hourlyReadyToPaid[16] ?? 0;
        $r5to6pm   = $hourlyReadyToPaid[17] ?? 0;
        $r6to7pm   = $hourlyReadyToPaid[18] ?? 0;
        $r7to8pm   = $hourlyReadyToPaid[19] ?? 0;

        $f8to9am = $hourlyFollowUp[8] ?? 0;
        $f9to10am = $hourlyFollowUp[9] ?? 0;
        $f10to11am = $hourlyFollowUp[10] ?? 0;
        $f11to12pm = $hourlyFollowUp[11] ?? 0;
        $f12to1pm  = $hourlyFollowUp[12] ?? 0;
        $f1to2pm   = $hourlyFollowUp[13] ?? 0;
        $f2to3pm   = $hourlyFollowUp[14] ?? 0;
        $f3to4pm   = $hourlyFollowUp[15] ?? 0;
        $f4to5pm   = $hourlyFollowUp[16] ?? 0;
        $f5to6pm   = $hourlyFollowUp[17] ?? 0;
        $f6to7pm   = $hourlyFollowUp[18] ?? 0;
        $f7to8pm   = $hourlyFollowUp[19] ?? 0;

        $tf8to9am = $hourlyTransferredFollowUp[8] ?? 0;
        $tf9to10am = $hourlyTransferredFollowUp[9] ?? 0;
        $tf10to11am = $hourlyTransferredFollowUp[10] ?? 0;
        $tf11to12pm = $hourlyTransferredFollowUp[11] ?? 0;
        $tf12to1pm  = $hourlyTransferredFollowUp[12] ?? 0;
        $tf1to2pm   = $hourlyTransferredFollowUp[13] ?? 0;
        $tf2to3pm   = $hourlyTransferredFollowUp[14] ?? 0;
        $tf3to4pm   = $hourlyTransferredFollowUp[15] ?? 0;
        $tf4to5pm   = $hourlyTransferredFollowUp[16] ?? 0;
        $tf5to6pm   = $hourlyTransferredFollowUp[17] ?? 0;
        $tf6to7pm   = $hourlyTransferredFollowUp[18] ?? 0;
        $tf7to8pm   = $hourlyTransferredFollowUp[19] ?? 0;

        $o8to9am = $hourlyOtherCalls[8] ?? 0;
        $o9to10am = $hourlyOtherCalls[9] ?? 0;
        $o10to11am = $hourlyOtherCalls[10] ?? 0;
        $o11to12pm = $hourlyOtherCalls[11] ?? 0;
        $o12to1pm  = $hourlyOtherCalls[12] ?? 0;
        $o1to2pm   = $hourlyOtherCalls[13] ?? 0;
        $o2to3pm   = $hourlyOtherCalls[14] ?? 0;
        $o3to4pm   = $hourlyOtherCalls[15] ?? 0;
        $o4to5pm   = $hourlyOtherCalls[16] ?? 0;
        $o5to6pm   = $hourlyOtherCalls[17] ?? 0;
        $o6to7pm   = $hourlyOtherCalls[18] ?? 0;
        $o7to8pm   = $hourlyOtherCalls[19] ?? 0;

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

        $targetAchieved = GoogleSheetData::whereRaw(
            "created_by REGEXP '^{$juniorUser->id}\\\\|senior:[0-9]+\\\\|senior:[0-9]+\\\\|accountant(.*)?$'"
        )
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

        $presentDays    = 0;
        $halfDays       = 0;
        $absentDays     = 0;
        $workingDays    = 0;
        $nonWorkingDays = 0;

        $today = now()->startOfDay(); // 🔒 freeze today until end of day

        // Loop through each day
        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

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


        return view('reports.allseniorweekly', compact(
            'totalCalls',
            'juniorUser',
            'calledAndMailedCalls',
            'selffollowupCalls',
            'readyToPaidCalls',
            'followUpCalls',
            'transferedfollowUpCalls',
            'otherCalls',

            'SselffollowupCalls',
            'StotalCalls',
            'ScalledAndMailedCalls',
            'SreadyToPaidCalls',
            'StransferedfollowUpCalls',
            'SfollowUpCalls',
            'SotherCalls',
            'selectedWeek',


            'r8to9am',
            'r9to10am',
            'r10to11am',
            'r11to12pm',
            'r12to1pm',
            'r1to2pm',
            'r2to3pm',
            'r3to4pm',
            'r4to5pm',
            'r5to6pm',
            'r6to7pm',
            'r7to8pm',


            'c8to9am',
            'c9to10am',
            'c10to11am',
            'c11to12pm',
            'c12to1pm',
            'c1to2pm',
            'c2to3pm',
            'c3to4pm',
            'c4to5pm',
            'c5to6pm',
            'c6to7pm',
            'c7to8pm',


            'sf8to9am',
            'sf9to10am',
            'sf10to11am',
            'sf11to12pm',
            'sf12to1pm',
            'sf1to2pm',
            'sf2to3pm',
            'sf3to4pm',
            'sf4to5pm',
            'sf5to6pm',
            'sf6to7pm',
            'sf7to8pm',


            'f8to9am',
            'f9to10am',
            'f10to11am',
            'f11to12pm',
            'f12to1pm',
            'f1to2pm',
            'f2to3pm',
            'f3to4pm',
            'f4to5pm',
            'f5to6pm',
            'f6to7pm',
            'f7to8pm',


            'tf8to9am',
            'tf9to10am',
            'tf10to11am',
            'tf11to12pm',
            'tf12to1pm',
            'tf1to2pm',
            'tf2to3pm',
            'tf3to4pm',
            'tf4to5pm',
            'tf5to6pm',
            'tf6to7pm',
            'tf7to8pm',


            'o8to9am',
            'o9to10am',
            'o10to11am',
            'o11to12pm',
            'o12to1pm',
            'o1to2pm',
            'o2to3pm',
            'o3to4pm',
            'o4to5pm',
            'o5to6pm',
            'o6to7pm',
            'o7to8pm',


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


    public function alljuniormonthly(Request $request, $userId)
    {
        $juniorUser =  User::where('id', $userId)
            ->where('is_deleted', 0)
            ->firstOrFail();
        $createdByKey = "{$juniorUser->id}|junior";

        // Selected month (default current month in YYYY-MM)
        $selectedMonth = $request->input('selected_month', date('Y-m'));
        [$year, $month] = explode('-', $selectedMonth);

        // Total calls for this junior in the selected month (including hierarchical keys)
        $MtotalCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->count();

        $Mtotaltransfers = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('transfers', 1)
            ->count();

        // Total "Called & Mailed" calls
        $McalledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        // Total other calls (not "Called & Mailed")
        $MotherCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();

        // --- Daily "Called & Mailed + Ready To Pay" counts ---
        $dailyCalledMailed = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        // --- Daily "Other Calls" counts ---
        $dailyOtherCalls = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        // --- Daily "Transfers" counts ---
        $dailyTransfers = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('transfers', 1)
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        $holidayDates = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where('is_holiday', 1)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // --- Create daily variables 1 to 31 ---
        $tDay1  = $dailyCalledMailed[1]  ?? 0;
        $tDay2  = $dailyCalledMailed[2]  ?? 0;
        $tDay3  = $dailyCalledMailed[3]  ?? 0;
        $tDay4  = $dailyCalledMailed[4]  ?? 0;
        $tDay5  = $dailyCalledMailed[5]  ?? 0;
        $tDay6  = $dailyCalledMailed[6]  ?? 0;
        $tDay7  = $dailyCalledMailed[7]  ?? 0;
        $tDay8  = $dailyCalledMailed[8]  ?? 0;
        $tDay9  = $dailyCalledMailed[9]  ?? 0;
        $tDay10 = $dailyCalledMailed[10] ?? 0;
        $tDay11 = $dailyCalledMailed[11] ?? 0;
        $tDay12 = $dailyCalledMailed[12] ?? 0;
        $tDay13 = $dailyCalledMailed[13] ?? 0;
        $tDay14 = $dailyCalledMailed[14] ?? 0;
        $tDay15 = $dailyCalledMailed[15] ?? 0;
        $tDay16 = $dailyCalledMailed[16] ?? 0;
        $tDay17 = $dailyCalledMailed[17] ?? 0;
        $tDay18 = $dailyCalledMailed[18] ?? 0;
        $tDay19 = $dailyCalledMailed[19] ?? 0;
        $tDay20 = $dailyCalledMailed[20] ?? 0;
        $tDay21 = $dailyCalledMailed[21] ?? 0;
        $tDay22 = $dailyCalledMailed[22] ?? 0;
        $tDay23 = $dailyCalledMailed[23] ?? 0;
        $tDay24 = $dailyCalledMailed[24] ?? 0;
        $tDay25 = $dailyCalledMailed[25] ?? 0;
        $tDay26 = $dailyCalledMailed[26] ?? 0;
        $tDay27 = $dailyCalledMailed[27] ?? 0;
        $tDay28 = $dailyCalledMailed[28] ?? 0;
        $tDay29 = $dailyCalledMailed[29] ?? 0;
        $tDay30 = $dailyCalledMailed[30] ?? 0;
        $tDay31 = $dailyCalledMailed[31] ?? 0;

        $oDay1  = $dailyOtherCalls[1]  ?? 0;
        $oDay2  = $dailyOtherCalls[2]  ?? 0;
        $oDay3  = $dailyOtherCalls[3]  ?? 0;
        $oDay4  = $dailyOtherCalls[4]  ?? 0;
        $oDay5  = $dailyOtherCalls[5]  ?? 0;
        $oDay6  = $dailyOtherCalls[6]  ?? 0;
        $oDay7  = $dailyOtherCalls[7]  ?? 0;
        $oDay8  = $dailyOtherCalls[8]  ?? 0;
        $oDay9  = $dailyOtherCalls[9]  ?? 0;
        $oDay10 = $dailyOtherCalls[10] ?? 0;
        $oDay11 = $dailyOtherCalls[11] ?? 0;
        $oDay12 = $dailyOtherCalls[12] ?? 0;
        $oDay13 = $dailyOtherCalls[13] ?? 0;
        $oDay14 = $dailyOtherCalls[14] ?? 0;
        $oDay15 = $dailyOtherCalls[15] ?? 0;
        $oDay16 = $dailyOtherCalls[16] ?? 0;
        $oDay17 = $dailyOtherCalls[17] ?? 0;
        $oDay18 = $dailyOtherCalls[18] ?? 0;
        $oDay19 = $dailyOtherCalls[19] ?? 0;
        $oDay20 = $dailyOtherCalls[20] ?? 0;
        $oDay21 = $dailyOtherCalls[21] ?? 0;
        $oDay22 = $dailyOtherCalls[22] ?? 0;
        $oDay23 = $dailyOtherCalls[23] ?? 0;
        $oDay24 = $dailyOtherCalls[24] ?? 0;
        $oDay25 = $dailyOtherCalls[25] ?? 0;
        $oDay26 = $dailyOtherCalls[26] ?? 0;
        $oDay27 = $dailyOtherCalls[27] ?? 0;
        $oDay28 = $dailyOtherCalls[28] ?? 0;
        $oDay29 = $dailyOtherCalls[29] ?? 0;
        $oDay30 = $dailyOtherCalls[30] ?? 0;
        $oDay31 = $dailyOtherCalls[31] ?? 0;

        $trDay1  = $dailyTransfers[1]  ?? 0;
        $trDay2  = $dailyTransfers[2]  ?? 0;
        $trDay3  = $dailyTransfers[3]  ?? 0;
        $trDay4  = $dailyTransfers[4]  ?? 0;
        $trDay5  = $dailyTransfers[5]  ?? 0;
        $trDay6  = $dailyTransfers[6]  ?? 0;
        $trDay7  = $dailyTransfers[7]  ?? 0;
        $trDay8  = $dailyTransfers[8]  ?? 0;
        $trDay9  = $dailyTransfers[9]  ?? 0;
        $trDay10 = $dailyTransfers[10] ?? 0;
        $trDay11 = $dailyTransfers[11] ?? 0;
        $trDay12 = $dailyTransfers[12] ?? 0;
        $trDay13 = $dailyTransfers[13] ?? 0;
        $trDay14 = $dailyTransfers[14] ?? 0;
        $trDay15 = $dailyTransfers[15] ?? 0;
        $trDay16 = $dailyTransfers[16] ?? 0;
        $trDay17 = $dailyTransfers[17] ?? 0;
        $trDay18 = $dailyTransfers[18] ?? 0;
        $trDay19 = $dailyTransfers[19] ?? 0;
        $trDay20 = $dailyTransfers[20] ?? 0;
        $trDay21 = $dailyTransfers[21] ?? 0;
        $trDay22 = $dailyTransfers[22] ?? 0;
        $trDay23 = $dailyTransfers[23] ?? 0;
        $trDay24 = $dailyTransfers[24] ?? 0;
        $trDay25 = $dailyTransfers[25] ?? 0;
        $trDay26 = $dailyTransfers[26] ?? 0;
        $trDay27 = $dailyTransfers[27] ?? 0;
        $trDay28 = $dailyTransfers[28] ?? 0;
        $trDay29 = $dailyTransfers[29] ?? 0;
        $trDay30 = $dailyTransfers[30] ?? 0;
        $trDay31 = $dailyTransfers[31] ?? 0;

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

        $targetAchieved = GoogleSheetData::whereRaw(
            "created_by REGEXP '^{$juniorUser->id}\\\\|junior:[0-9]+\\\\|senior:[0-9]+\\\\|accountant(.*)?$'"
        )
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

        $presentDays    = 0;
        $halfDays       = 0;
        $absentDays     = 0;
        $workingDays    = 0;
        $nonWorkingDays = 0;

        $today = now()->startOfDay(); // 🔒 Freeze today until end of day

        // Loop through each day
        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

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
        // --- Averages ---
        $MAvgTotalCalls     = $presentDays > 0 ? intval($McalledAndMailedCalls / $presentDays) : 0;
        $MAvgtotaltransfers = $presentDays > 0 ? intval($Mtotaltransfers / $presentDays) : 0;

        return view('reports.alljuniormonthly', compact(
            'juniorUser',
            'MtotalCalls',
            'McalledAndMailedCalls',
            'MotherCalls',
            'selectedMonth',
            'tDay1',
            'tDay2',
            'tDay3',
            'tDay4',
            'tDay5',
            'tDay6',
            'tDay7',
            'tDay8',
            'tDay9',
            'tDay10',
            'tDay11',
            'tDay12',
            'tDay13',
            'tDay14',
            'tDay15',
            'tDay16',
            'tDay17',
            'tDay18',
            'tDay19',
            'tDay20',
            'tDay21',
            'tDay22',
            'tDay23',
            'tDay24',
            'tDay25',
            'tDay26',
            'tDay27',
            'tDay28',
            'tDay29',
            'tDay30',
            'tDay31',
            'oDay1',
            'oDay2',
            'oDay3',
            'oDay4',
            'oDay5',
            'oDay6',
            'oDay7',
            'oDay8',
            'oDay9',
            'oDay10',
            'oDay11',
            'oDay12',
            'oDay13',
            'oDay14',
            'oDay15',
            'oDay16',
            'oDay17',
            'oDay18',
            'oDay19',
            'oDay20',
            'oDay21',
            'oDay22',
            'oDay23',
            'oDay24',
            'oDay25',
            'oDay26',
            'oDay27',
            'oDay28',
            'oDay29',
            'oDay30',
            'oDay31',
            'trDay1',
            'trDay2',
            'trDay3',
            'trDay4',
            'trDay5',
            'trDay6',
            'trDay7',
            'trDay8',
            'trDay9',
            'trDay10',
            'trDay11',
            'trDay12',
            'trDay13',
            'trDay14',
            'trDay15',
            'trDay16',
            'trDay17',
            'trDay18',
            'trDay19',
            'trDay20',
            'trDay21',
            'trDay22',
            'trDay23',
            'trDay24',
            'trDay25',
            'trDay26',
            'trDay27',
            'trDay28',
            'trDay29',
            'trDay30',
            'trDay31',
            'targetGiven',
            'targetAchieved',
            'targetYetToAchieve',
            'daysLeft',
            'presentDays',
            'absentDays',
            'workingDays',
            'nonWorkingDays',
            'MAvgTotalCalls',
            'Mtotaltransfers',
            'MAvgtotaltransfers',
        ));
    }

    public function allaccountantmonthly(Request $request, $userId)
    {
        $accountantUser =  User::where('id', $userId)
            ->where('is_deleted', 0)
            ->firstOrFail();
        $createdByKey = "{$accountantUser->id}|accountant";

        // Selected month (default current month in YYYY-MM)
        $selectedMonth = $request->input('selected_month', date('Y-m'));
        [$year, $month] = explode('-', $selectedMonth);

        // Total calls for this accountant in the selected month (including hierarchical keys)
        $MtotalCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->count();

        // Total "Ready To Pay" calls
        $McalledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();

        // Total other calls (not "Ready To Pay")
        $MotherCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Ready To Pay')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        // Hour-wise "Ready To Pay" counts
        $hourlyCalledMailed = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Ready To Pay')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Hour-wise "Other Calls" counts
        $hourlyOtherCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Ready To Pay')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();
        $juniorUser = $accountantUser;

        // Initialize hour blocks (10 AM - 8 PM)
        $t8to9am = $hourlyCalledMailed[8] ?? 0;
        $t9to10am = $hourlyCalledMailed[9] ?? 0;
        $t10to11am = $hourlyCalledMailed[10] ?? 0;
        $t11to12pm = $hourlyCalledMailed[11] ?? 0;
        $t12to1pm  = $hourlyCalledMailed[12] ?? 0;
        $t1to2pm   = $hourlyCalledMailed[13] ?? 0;
        $t2to3pm   = $hourlyCalledMailed[14] ?? 0;
        $t3to4pm   = $hourlyCalledMailed[15] ?? 0;
        $t4to5pm   = $hourlyCalledMailed[16] ?? 0;
        $t5to6pm   = $hourlyCalledMailed[17] ?? 0;
        $t6to7pm   = $hourlyCalledMailed[18] ?? 0;
        $t7to8pm   = $hourlyCalledMailed[19] ?? 0;

        $o8to9am = $hourlyOtherCalls[8] ?? 0;
        $o9to10am = $hourlyOtherCalls[9] ?? 0;
        $o10to11am = $hourlyOtherCalls[10] ?? 0;
        $o11to12pm = $hourlyOtherCalls[11] ?? 0;
        $o12to1pm  = $hourlyOtherCalls[12] ?? 0;
        $o1to2pm   = $hourlyOtherCalls[13] ?? 0;
        $o2to3pm   = $hourlyOtherCalls[14] ?? 0;
        $o3to4pm   = $hourlyOtherCalls[15] ?? 0;
        $o4to5pm   = $hourlyOtherCalls[16] ?? 0;
        $o5to6pm   = $hourlyOtherCalls[17] ?? 0;
        $o6to7pm   = $hourlyOtherCalls[18] ?? 0;
        $o7to8pm   = $hourlyOtherCalls[19] ?? 0;

        return view('reports.allaccountantmonthly', compact(
            'juniorUser',
            'MtotalCalls',
            'McalledAndMailedCalls',
            'MotherCalls',
            'selectedMonth',
            't8to9am',
            't9to10am',
            't10to11am',
            't11to12pm',
            't12to1pm',
            't1to2pm',
            't2to3pm',
            't3to4pm',
            't4to5pm',
            't5to6pm',
            't6to7pm',
            't7to8pm',
            'o8to9am',
            'o9to10am',
            'o10to11am',
            'o11to12pm',
            'o12to1pm',
            'o1to2pm',
            'o2to3pm',
            'o3to4pm',
            'o4to5pm',
            'o5to6pm',
            'o6to7pm',
            'o7to8pm'
        ));
    }

    public function alltrainermonthly(Request $request, $userId)
    {
        $trainerUser =  User::where('id', $userId)
            ->where('is_deleted', 0)
            ->firstOrFail();
        $createdByKey = "{$trainerUser->id}|trainer";

        // Selected month (default current month in YYYY-MM)
        $selectedMonth = $request->input('selected_month', date('Y-m'));
        [$year, $month] = explode('-', $selectedMonth);

        // Total calls for this trainer in the selected month (including hierarchical keys)
        $MtotalCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->count();

        // Total "Called & Mailed" calls
        $McalledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        // Total other calls (not "Called & Mailed")
        $MotherCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        // Hour-wise "Called & Mailed" counts
        $hourlyCalledMailed = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Hour-wise "Other Calls" counts
        $hourlyOtherCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();


        // Initialize hour blocks (10 AM - 8 PM)
        $t8to9am = $hourlyCalledMailed[8] ?? 0;
        $t9to10am = $hourlyCalledMailed[9] ?? 0;
        $t10to11am = $hourlyCalledMailed[10] ?? 0;
        $t11to12pm = $hourlyCalledMailed[11] ?? 0;
        $t12to1pm  = $hourlyCalledMailed[12] ?? 0;
        $t1to2pm   = $hourlyCalledMailed[13] ?? 0;
        $t2to3pm   = $hourlyCalledMailed[14] ?? 0;
        $t3to4pm   = $hourlyCalledMailed[15] ?? 0;
        $t4to5pm   = $hourlyCalledMailed[16] ?? 0;
        $t5to6pm   = $hourlyCalledMailed[17] ?? 0;
        $t6to7pm   = $hourlyCalledMailed[18] ?? 0;
        $t7to8pm   = $hourlyCalledMailed[19] ?? 0;

        $o8to9am = $hourlyOtherCalls[8] ?? 0;
        $o9to10am = $hourlyOtherCalls[9] ?? 0;
        $o10to11am = $hourlyOtherCalls[10] ?? 0;
        $o11to12pm = $hourlyOtherCalls[11] ?? 0;
        $o12to1pm  = $hourlyOtherCalls[12] ?? 0;
        $o1to2pm   = $hourlyOtherCalls[13] ?? 0;
        $o2to3pm   = $hourlyOtherCalls[14] ?? 0;
        $o3to4pm   = $hourlyOtherCalls[15] ?? 0;
        $o4to5pm   = $hourlyOtherCalls[16] ?? 0;
        $o5to6pm   = $hourlyOtherCalls[17] ?? 0;
        $o6to7pm   = $hourlyOtherCalls[18] ?? 0;
        $o7to8pm   = $hourlyOtherCalls[19] ?? 0;

        return view('reports.alltrainermonthly', compact(
            'trainerUser',
            'MtotalCalls',
            'McalledAndMailedCalls',
            'MotherCalls',
            'selectedMonth',
            't8to9am',
            't9to10am',
            't10to11am',
            't11to12pm',
            't12to1pm',
            't1to2pm',
            't2to3pm',
            't3to4pm',
            't4to5pm',
            't5to6pm',
            't6to7pm',
            't7to8pm',
            'o8to9am',
            'o9to10am',
            'o10to11am',
            'o11to12pm',
            'o12to1pm',
            'o1to2pm',
            'o2to3pm',
            'o3to4pm',
            'o4to5pm',
            'o5to6pm',
            'o6to7pm',
            'o7to8pm'
        ));
    }

    public function allseniormonthly(Request $request, $userId)
    {
        $createdByKey = "{$userId}|senior";
        $user =  User::where('id', $userId)
            ->where('is_deleted', 0)
            ->firstOrFail();

        // Total "Called & Mailed" calls
        $calledAndMailedCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        $selffollowupCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        // Total "Ready To Pay" calls
        $readyToPaidCalls = GoogleSheetData::where(function ($q) {
            $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
        })
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();

        $followUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        $transferedfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->count();

        $totalCalls =
            $calledAndMailedCalls
            + $selffollowupCalls
            + $readyToPaidCalls
            + $followUpCalls
            + $transferedfollowUpCalls;


        // Total other calls (excluding Called & Mailed)
        $otherCalls = GoogleSheetData::where('created_by', 'like', "%{$createdByKey}%")
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();



        // Selected month (default current month in YYYY-MM)
        $selectedMonth = $request->input('selected_month', date('Y-m'));
        [$year, $month] = explode('-', $selectedMonth);

        // Total "Called & Mailed" calls in month
        $McalledAndMailedCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        // Self follow-up calls in month (Called & Mailed / Ready To Pay with TransferRemark)
        $MselffollowupCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'"
        )
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();

        // Ready To Pay calls in month
        $MreadyToPaidCalls = GoogleSheetData::where(function ($q) {
            $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
        })
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Ready To Pay')
            ->count();

        // Follow-up calls in month (Called & Mailed with TransferRemark)
        $MfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->count();


        // Transferred follow-up calls in month
        $MtransferedfollowUpCalls = GoogleSheetData::whereRaw(
            "created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'"
        )
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->count();

        // Other calls in month (excluding Called & Mailed)
        $MotherCalls = GoogleSheetData::where('created_by', 'like', "%{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        $MtotalCalls =
            $McalledAndMailedCalls
            + $MselffollowupCalls
            + $MreadyToPaidCalls
            + $MfollowUpCalls
            + $MtransferedfollowUpCalls;

        // Daily "Called & Mailed" counts
        $dailyCalledMailed = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        // Daily "Follow Up" counts
        $dailyFollowUp = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        // Daily Ready To Paid
        $dailyReadyToPaid = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->where(function ($q) {
                $q->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
                    ->orWhereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'");
            })
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Ready To Pay')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        // Daily Called & Mailed
        $dailyCalledAndMailed = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'")
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();


        // Daily Self Follow-up (Called & Mailed / Ready To Pay with TransferRemark)
        $dailySelfFollowUp = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:[0-9]+\\|senior:0\\|senior$'")
            ->whereRaw("created_by NOT REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0)
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();


        // Daily Transferred Follow-up (Called & Mailed with TransferRemark and transfers = 1)
        $dailyTransferredFollowUp = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->whereRaw("created_by REGEXP '^[0-9]+\\|junior:0\\|senior$'")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        // Daily Other Calls (excluding Called & Mailed)
        $dailyOtherCalls = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->where('created_by', 'like', "%{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        $holidayDates = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where('is_holiday', 1)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();


        // --- Initialize daily variables (Day 1 - Day 31) ---
        $cDay1  = $dailyCalledAndMailed[1]  ?? 0;
        $cDay2  = $dailyCalledAndMailed[2]  ?? 0;
        $cDay3  = $dailyCalledAndMailed[3]  ?? 0;
        $cDay4  = $dailyCalledAndMailed[4]  ?? 0;
        $cDay5  = $dailyCalledAndMailed[5]  ?? 0;
        $cDay6  = $dailyCalledAndMailed[6]  ?? 0;
        $cDay7  = $dailyCalledAndMailed[7]  ?? 0;
        $cDay8  = $dailyCalledAndMailed[8]  ?? 0;
        $cDay9  = $dailyCalledAndMailed[9]  ?? 0;
        $cDay10 = $dailyCalledAndMailed[10] ?? 0;
        $cDay11 = $dailyCalledAndMailed[11] ?? 0;
        $cDay12 = $dailyCalledAndMailed[12] ?? 0;
        $cDay13 = $dailyCalledAndMailed[13] ?? 0;
        $cDay14 = $dailyCalledAndMailed[14] ?? 0;
        $cDay15 = $dailyCalledAndMailed[15] ?? 0;
        $cDay16 = $dailyCalledAndMailed[16] ?? 0;
        $cDay17 = $dailyCalledAndMailed[17] ?? 0;
        $cDay18 = $dailyCalledAndMailed[18] ?? 0;
        $cDay19 = $dailyCalledAndMailed[19] ?? 0;
        $cDay20 = $dailyCalledAndMailed[20] ?? 0;
        $cDay21 = $dailyCalledAndMailed[21] ?? 0;
        $cDay22 = $dailyCalledAndMailed[22] ?? 0;
        $cDay23 = $dailyCalledAndMailed[23] ?? 0;
        $cDay24 = $dailyCalledAndMailed[24] ?? 0;
        $cDay25 = $dailyCalledAndMailed[25] ?? 0;
        $cDay26 = $dailyCalledAndMailed[26] ?? 0;
        $cDay27 = $dailyCalledAndMailed[27] ?? 0;
        $cDay28 = $dailyCalledAndMailed[28] ?? 0;
        $cDay29 = $dailyCalledAndMailed[29] ?? 0;
        $cDay30 = $dailyCalledAndMailed[30] ?? 0;
        $cDay31 = $dailyCalledAndMailed[31] ?? 0;

        $sfDay1  = $dailySelfFollowUp[1]  ?? 0;
        $sfDay2  = $dailySelfFollowUp[2]  ?? 0;
        $sfDay3  = $dailySelfFollowUp[3]  ?? 0;
        $sfDay4  = $dailySelfFollowUp[4]  ?? 0;
        $sfDay5  = $dailySelfFollowUp[5]  ?? 0;
        $sfDay6  = $dailySelfFollowUp[6]  ?? 0;
        $sfDay7  = $dailySelfFollowUp[7]  ?? 0;
        $sfDay8  = $dailySelfFollowUp[8]  ?? 0;
        $sfDay9  = $dailySelfFollowUp[9]  ?? 0;
        $sfDay10 = $dailySelfFollowUp[10] ?? 0;
        $sfDay11 = $dailySelfFollowUp[11] ?? 0;
        $sfDay12 = $dailySelfFollowUp[12] ?? 0;
        $sfDay13 = $dailySelfFollowUp[13] ?? 0;
        $sfDay14 = $dailySelfFollowUp[14] ?? 0;
        $sfDay15 = $dailySelfFollowUp[15] ?? 0;
        $sfDay16 = $dailySelfFollowUp[16] ?? 0;
        $sfDay17 = $dailySelfFollowUp[17] ?? 0;
        $sfDay18 = $dailySelfFollowUp[18] ?? 0;
        $sfDay19 = $dailySelfFollowUp[19] ?? 0;
        $sfDay20 = $dailySelfFollowUp[20] ?? 0;
        $sfDay21 = $dailySelfFollowUp[21] ?? 0;
        $sfDay22 = $dailySelfFollowUp[22] ?? 0;
        $sfDay23 = $dailySelfFollowUp[23] ?? 0;
        $sfDay24 = $dailySelfFollowUp[24] ?? 0;
        $sfDay25 = $dailySelfFollowUp[25] ?? 0;
        $sfDay26 = $dailySelfFollowUp[26] ?? 0;
        $sfDay27 = $dailySelfFollowUp[27] ?? 0;
        $sfDay28 = $dailySelfFollowUp[28] ?? 0;
        $sfDay29 = $dailySelfFollowUp[29] ?? 0;
        $sfDay30 = $dailySelfFollowUp[30] ?? 0;
        $sfDay31 = $dailySelfFollowUp[31] ?? 0;


        $tfDay1  = $dailyTransferredFollowUp[1]  ?? 0;
        $tfDay2  = $dailyTransferredFollowUp[2]  ?? 0;
        $tfDay3  = $dailyTransferredFollowUp[3]  ?? 0;
        $tfDay4  = $dailyTransferredFollowUp[4]  ?? 0;
        $tfDay5  = $dailyTransferredFollowUp[5]  ?? 0;
        $tfDay6  = $dailyTransferredFollowUp[6]  ?? 0;
        $tfDay7  = $dailyTransferredFollowUp[7]  ?? 0;
        $tfDay8  = $dailyTransferredFollowUp[8]  ?? 0;
        $tfDay9  = $dailyTransferredFollowUp[9]  ?? 0;
        $tfDay10 = $dailyTransferredFollowUp[10] ?? 0;
        $tfDay11 = $dailyTransferredFollowUp[11] ?? 0;
        $tfDay12 = $dailyTransferredFollowUp[12] ?? 0;
        $tfDay13 = $dailyTransferredFollowUp[13] ?? 0;
        $tfDay14 = $dailyTransferredFollowUp[14] ?? 0;
        $tfDay15 = $dailyTransferredFollowUp[15] ?? 0;
        $tfDay16 = $dailyTransferredFollowUp[16] ?? 0;
        $tfDay17 = $dailyTransferredFollowUp[17] ?? 0;
        $tfDay18 = $dailyTransferredFollowUp[18] ?? 0;
        $tfDay19 = $dailyTransferredFollowUp[19] ?? 0;
        $tfDay20 = $dailyTransferredFollowUp[20] ?? 0;
        $tfDay21 = $dailyTransferredFollowUp[21] ?? 0;
        $tfDay22 = $dailyTransferredFollowUp[22] ?? 0;
        $tfDay23 = $dailyTransferredFollowUp[23] ?? 0;
        $tfDay24 = $dailyTransferredFollowUp[24] ?? 0;
        $tfDay25 = $dailyTransferredFollowUp[25] ?? 0;
        $tfDay26 = $dailyTransferredFollowUp[26] ?? 0;
        $tfDay27 = $dailyTransferredFollowUp[27] ?? 0;
        $tfDay28 = $dailyTransferredFollowUp[28] ?? 0;
        $tfDay29 = $dailyTransferredFollowUp[29] ?? 0;
        $tfDay30 = $dailyTransferredFollowUp[30] ?? 0;
        $tfDay31 = $dailyTransferredFollowUp[31] ?? 0;

        $oDay1  = $dailyOtherCalls[1]  ?? 0;
        $oDay2  = $dailyOtherCalls[2]  ?? 0;
        $oDay3  = $dailyOtherCalls[3]  ?? 0;
        $oDay4  = $dailyOtherCalls[4]  ?? 0;
        $oDay5  = $dailyOtherCalls[5]  ?? 0;
        $oDay6  = $dailyOtherCalls[6]  ?? 0;
        $oDay7  = $dailyOtherCalls[7]  ?? 0;
        $oDay8  = $dailyOtherCalls[8]  ?? 0;
        $oDay9  = $dailyOtherCalls[9]  ?? 0;
        $oDay10 = $dailyOtherCalls[10] ?? 0;
        $oDay11 = $dailyOtherCalls[11] ?? 0;
        $oDay12 = $dailyOtherCalls[12] ?? 0;
        $oDay13 = $dailyOtherCalls[13] ?? 0;
        $oDay14 = $dailyOtherCalls[14] ?? 0;
        $oDay15 = $dailyOtherCalls[15] ?? 0;
        $oDay16 = $dailyOtherCalls[16] ?? 0;
        $oDay17 = $dailyOtherCalls[17] ?? 0;
        $oDay18 = $dailyOtherCalls[18] ?? 0;
        $oDay19 = $dailyOtherCalls[19] ?? 0;
        $oDay20 = $dailyOtherCalls[20] ?? 0;
        $oDay21 = $dailyOtherCalls[21] ?? 0;
        $oDay22 = $dailyOtherCalls[22] ?? 0;
        $oDay23 = $dailyOtherCalls[23] ?? 0;
        $oDay24 = $dailyOtherCalls[24] ?? 0;
        $oDay25 = $dailyOtherCalls[25] ?? 0;
        $oDay26 = $dailyOtherCalls[26] ?? 0;
        $oDay27 = $dailyOtherCalls[27] ?? 0;
        $oDay28 = $dailyOtherCalls[28] ?? 0;
        $oDay29 = $dailyOtherCalls[29] ?? 0;
        $oDay30 = $dailyOtherCalls[30] ?? 0;
        $oDay31 = $dailyOtherCalls[31] ?? 0;


        $fDay1  = $dailyFollowUp[1]  ?? 0;
        $fDay2  = $dailyFollowUp[2]  ?? 0;
        $fDay3  = $dailyFollowUp[3]  ?? 0;
        $fDay4  = $dailyFollowUp[4]  ?? 0;
        $fDay5  = $dailyFollowUp[5]  ?? 0;
        $fDay6  = $dailyFollowUp[6]  ?? 0;
        $fDay7  = $dailyFollowUp[7]  ?? 0;
        $fDay8  = $dailyFollowUp[8]  ?? 0;
        $fDay9  = $dailyFollowUp[9]  ?? 0;
        $fDay10 = $dailyFollowUp[10] ?? 0;
        $fDay11 = $dailyFollowUp[11] ?? 0;
        $fDay12 = $dailyFollowUp[12] ?? 0;
        $fDay13 = $dailyFollowUp[13] ?? 0;
        $fDay14 = $dailyFollowUp[14] ?? 0;
        $fDay15 = $dailyFollowUp[15] ?? 0;
        $fDay16 = $dailyFollowUp[16] ?? 0;
        $fDay17 = $dailyFollowUp[17] ?? 0;
        $fDay18 = $dailyFollowUp[18] ?? 0;
        $fDay19 = $dailyFollowUp[19] ?? 0;
        $fDay20 = $dailyFollowUp[20] ?? 0;
        $fDay21 = $dailyFollowUp[21] ?? 0;
        $fDay22 = $dailyFollowUp[22] ?? 0;
        $fDay23 = $dailyFollowUp[23] ?? 0;
        $fDay24 = $dailyFollowUp[24] ?? 0;
        $fDay25 = $dailyFollowUp[25] ?? 0;
        $fDay26 = $dailyFollowUp[26] ?? 0;
        $fDay27 = $dailyFollowUp[27] ?? 0;
        $fDay28 = $dailyFollowUp[28] ?? 0;
        $fDay29 = $dailyFollowUp[29] ?? 0;
        $fDay30 = $dailyFollowUp[30] ?? 0;
        $fDay31 = $dailyFollowUp[31] ?? 0;

        $rDay1  = $dailyReadyToPaid[1]  ?? 0;
        $rDay2  = $dailyReadyToPaid[2]  ?? 0;
        $rDay3  = $dailyReadyToPaid[3]  ?? 0;
        $rDay4  = $dailyReadyToPaid[4]  ?? 0;
        $rDay5  = $dailyReadyToPaid[5]  ?? 0;
        $rDay6  = $dailyReadyToPaid[6]  ?? 0;
        $rDay7  = $dailyReadyToPaid[7]  ?? 0;
        $rDay8  = $dailyReadyToPaid[8]  ?? 0;
        $rDay9  = $dailyReadyToPaid[9]  ?? 0;
        $rDay10 = $dailyReadyToPaid[10] ?? 0;
        $rDay11 = $dailyReadyToPaid[11] ?? 0;
        $rDay12 = $dailyReadyToPaid[12] ?? 0;
        $rDay13 = $dailyReadyToPaid[13] ?? 0;
        $rDay14 = $dailyReadyToPaid[14] ?? 0;
        $rDay15 = $dailyReadyToPaid[15] ?? 0;
        $rDay16 = $dailyReadyToPaid[16] ?? 0;
        $rDay17 = $dailyReadyToPaid[17] ?? 0;
        $rDay18 = $dailyReadyToPaid[18] ?? 0;
        $rDay19 = $dailyReadyToPaid[19] ?? 0;
        $rDay20 = $dailyReadyToPaid[20] ?? 0;
        $rDay21 = $dailyReadyToPaid[21] ?? 0;
        $rDay22 = $dailyReadyToPaid[22] ?? 0;
        $rDay23 = $dailyReadyToPaid[23] ?? 0;
        $rDay24 = $dailyReadyToPaid[24] ?? 0;
        $rDay25 = $dailyReadyToPaid[25] ?? 0;
        $rDay26 = $dailyReadyToPaid[26] ?? 0;
        $rDay27 = $dailyReadyToPaid[27] ?? 0;
        $rDay28 = $dailyReadyToPaid[28] ?? 0;
        $rDay29 = $dailyReadyToPaid[29] ?? 0;
        $rDay30 = $dailyReadyToPaid[30] ?? 0;
        $rDay31 = $dailyReadyToPaid[31] ?? 0;

        $juniorUser = $user;

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

        $targetAchieved = GoogleSheetData::whereRaw(
            "created_by REGEXP '^{$juniorUser->id}\\\\|senior:[0-9]+\\\\|senior:[0-9]+\\\\|accountant(.*)?$'"
        )
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

        $presentDays    = 0;
        $halfDays       = 0;
        $absentDays     = 0;
        $workingDays    = 0;
        $nonWorkingDays = 0;

        $today = now()->startOfDay(); // 🔒 freeze today until end of day

        // Loop through each day
        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

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

        $MAvgTotalCalls = $presentDays > 0 ? intval($McalledAndMailedCalls / $presentDays) : 0;

        return view('reports.allseniormonthly', compact(
            'totalCalls',
            'MAvgTotalCalls',
            'juniorUser',
            'calledAndMailedCalls',
            'readyToPaidCalls',
            'followUpCalls',
            'otherCalls',
            'MtotalCalls',
            'McalledAndMailedCalls',
            'MfollowUpCalls',
            'MreadyToPaidCalls',
            'MotherCalls',
            'selectedMonth',
            'MselffollowupCalls',
            'MtransferedfollowUpCalls',

            // --- Called & Mailed daily ---
            'cDay1',
            'cDay2',
            'cDay3',
            'cDay4',
            'cDay5',
            'cDay6',
            'cDay7',
            'cDay8',
            'cDay9',
            'cDay10',
            'cDay11',
            'cDay12',
            'cDay13',
            'cDay14',
            'cDay15',
            'cDay16',
            'cDay17',
            'cDay18',
            'cDay19',
            'cDay20',
            'cDay21',
            'cDay22',
            'cDay23',
            'cDay24',
            'cDay25',
            'cDay26',
            'cDay27',
            'cDay28',
            'cDay29',
            'cDay30',
            'cDay31',


            // --- Other Calls daily ---
            'sfDay1',
            'sfDay2',
            'sfDay3',
            'sfDay4',
            'sfDay5',
            'sfDay6',
            'sfDay7',
            'sfDay8',
            'sfDay9',
            'sfDay10',
            'sfDay11',
            'sfDay12',
            'sfDay13',
            'sfDay14',
            'sfDay15',
            'sfDay16',
            'sfDay17',
            'sfDay18',
            'sfDay19',
            'sfDay20',
            'sfDay21',
            'sfDay22',
            'sfDay23',
            'sfDay24',
            'sfDay25',
            'sfDay26',
            'sfDay27',
            'sfDay28',
            'sfDay29',
            'sfDay30',
            'sfDay31',


            'fDay1',
            'fDay2',
            'fDay3',
            'fDay4',
            'fDay5',
            'fDay6',
            'fDay7',
            'fDay8',
            'fDay9',
            'fDay10',
            'fDay11',
            'fDay12',
            'fDay13',
            'fDay14',
            'fDay15',
            'fDay16',
            'fDay17',
            'fDay18',
            'fDay19',
            'fDay20',
            'fDay21',
            'fDay22',
            'fDay23',
            'fDay24',
            'fDay25',
            'fDay26',
            'fDay27',
            'fDay28',
            'fDay29',
            'fDay30',
            'fDay31',


            'tfDay1',
            'tfDay2',
            'tfDay3',
            'tfDay4',
            'tfDay5',
            'tfDay6',
            'tfDay7',
            'tfDay8',
            'tfDay9',
            'tfDay10',
            'tfDay11',
            'tfDay12',
            'tfDay13',
            'tfDay14',
            'tfDay15',
            'tfDay16',
            'tfDay17',
            'tfDay18',
            'tfDay19',
            'tfDay20',
            'tfDay21',
            'tfDay22',
            'tfDay23',
            'tfDay24',
            'tfDay25',
            'tfDay26',
            'tfDay27',
            'tfDay28',
            'tfDay29',
            'tfDay30',
            'tfDay31',

            'oDay1',
            'oDay2',
            'oDay3',
            'oDay4',
            'oDay5',
            'oDay6',
            'oDay7',
            'oDay8',
            'oDay9',
            'oDay10',
            'oDay11',
            'oDay12',
            'oDay13',
            'oDay14',
            'oDay15',
            'oDay16',
            'oDay17',
            'oDay18',
            'oDay19',
            'oDay20',
            'oDay21',
            'oDay22',
            'oDay23',
            'oDay24',
            'oDay25',
            'oDay26',
            'oDay27',
            'oDay28',
            'oDay29',
            'oDay30',
            'oDay31',

            'rDay1',
            'rDay2',
            'rDay3',
            'rDay4',
            'rDay5',
            'rDay6',
            'rDay7',
            'rDay8',
            'rDay9',
            'rDay10',
            'rDay11',
            'rDay12',
            'rDay13',
            'rDay14',
            'rDay15',
            'rDay16',
            'rDay17',
            'rDay18',
            'rDay19',
            'rDay20',
            'rDay21',
            'rDay22',
            'rDay23',
            'rDay24',
            'rDay25',
            'rDay26',
            'rDay27',
            'rDay28',
            'rDay29',
            'rDay30',
            'rDay31',


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



    public function junior(Request $request)
    {
        $user = Auth::user();
        $createdByKey = "{$user->id}|junior";

        // ================================
        // Main logic with LIKE filters
        // ================================

        // Total calls for this junior (including hierarchical keys)
        $totalCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")->count();

        // Total "Called & Mailed" calls for this junior
        $calledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        // Total other calls for this junior
        $otherCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();


        // Group data by hour of updated_at (for this junior)
        $hourlyCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Selected date (default today)
        $selectedDate = $request->input('selected_date', date('Y-m-d'));
        $selectedMonth = date('Y-m', strtotime($selectedDate));
        [$year, $month] = explode('-', $selectedMonth);

        // Base query filtered by this junior and date
        $query = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate);
        $tquery = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate);

        // Selected date totals for this junior
        $StotalCalls = $query->count();

        $ScalledAndMailedCalls = (clone $tquery)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereDate('followup', $selectedDate)
            ->count();

        $SotherCalls = (clone $query)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();

        $Stotaltransfers = (clone $query)
            ->where(function ($q) {
                $q->where('transfers', 1);
            })
            ->whereDate('transfer_date', $selectedDate)
            ->count();


        // Hour-wise "Called & Mailed" counts
        $hourlyCalledMailed = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereDate('followup', $selectedDate)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $hourlyOtherCalls = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $selectedDate)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $hourlyTransfers = GoogleSheetData::selectRaw('HOUR(updated_at) as hour, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('transfer_date', $selectedDate)
            ->where('transfers', 1)
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $holidayDates = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where('is_holiday', 1)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $juniorUser = $user;

        // Initialize hour blocks (8 PM - 6 AM)
        $t8to9am = $hourlyCalledMailed[8] ?? 0;
        $t9to10am = $hourlyCalledMailed[9] ?? 0;
        $t10to11am = $hourlyCalledMailed[10] ?? 0;
        $t11to12pm = $hourlyCalledMailed[11] ?? 0;
        $t12to1pm  = $hourlyCalledMailed[12] ?? 0;
        $t1to2pm   = $hourlyCalledMailed[13] ?? 0;
        $t2to3pm   = $hourlyCalledMailed[14] ?? 0;
        $t3to4pm   = $hourlyCalledMailed[15] ?? 0;
        $t4to5pm   = $hourlyCalledMailed[16] ?? 0;
        $t5to6pm   = $hourlyCalledMailed[17] ?? 0;
        $t6to7pm   = $hourlyCalledMailed[18] ?? 0;
        $t7to8pm   = $hourlyCalledMailed[19] ?? 0;

        $tr8to9am  = $hourlyTransfers[8]  ?? 0;
        $tr9to10am = $hourlyTransfers[9]  ?? 0;
        $tr10to11am = $hourlyTransfers[10] ?? 0;
        $tr11to12pm = $hourlyTransfers[11] ?? 0;
        $tr12to1pm = $hourlyTransfers[12] ?? 0;
        $tr1to2pm  = $hourlyTransfers[13] ?? 0;
        $tr2to3pm  = $hourlyTransfers[14] ?? 0;
        $tr3to4pm  = $hourlyTransfers[15] ?? 0;
        $tr4to5pm  = $hourlyTransfers[16] ?? 0;
        $tr5to6pm  = $hourlyTransfers[17] ?? 0;
        $tr6to7pm  = $hourlyTransfers[18] ?? 0;
        $tr7to8pm  = $hourlyTransfers[19] ?? 0;

        $o8to9am = $hourlyOtherCalls[8] ?? 0;
        $o9to10am = $hourlyOtherCalls[9] ?? 0;
        $o10to11am = $hourlyOtherCalls[10] ?? 0;
        $o11to12pm = $hourlyOtherCalls[11] ?? 0;
        $o12to1pm  = $hourlyOtherCalls[12] ?? 0;
        $o1to2pm   = $hourlyOtherCalls[13] ?? 0;
        $o2to3pm   = $hourlyOtherCalls[14] ?? 0;
        $o3to4pm   = $hourlyOtherCalls[15] ?? 0;
        $o4to5pm   = $hourlyOtherCalls[16] ?? 0;
        $o5to6pm   = $hourlyOtherCalls[17] ?? 0;
        $o6to7pm   = $hourlyOtherCalls[18] ?? 0;
        $o7to8pm   = $hourlyOtherCalls[19] ?? 0;

        $Mtotaltransfers = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('transfers', 1)
            ->count();

        // Total "Called & Mailed" calls
        $McalledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

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

        $targetAchieved = GoogleSheetData::whereRaw(
            "created_by REGEXP '^{$juniorUser->id}\\\\|junior:[0-9]+\\\\|senior:[0-9]+\\\\|accountant(.*)?$'"
        )
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

            // ⛔ Skip today completely
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

        // --- Averages ---
        $MAvgTotalCalls       = $presentDays > 0 ? intval($McalledAndMailedCalls / $presentDays) : 0;
        $MAvgtotaltransfers   = $presentDays > 0 ? intval($Mtotaltransfers / $presentDays) : 0;

        return view('reports.junior', compact(
            'totalCalls',
            'calledAndMailedCalls',
            'otherCalls',
            'juniorUser',
            'StotalCalls',
            'Stotaltransfers',
            'ScalledAndMailedCalls',
            'SotherCalls',
            'selectedDate',
            't8to9am',
            't9to10am',
            't10to11am',
            't11to12pm',
            't12to1pm',
            't1to2pm',
            't2to3pm',
            't3to4pm',
            't4to5pm',
            't5to6pm',
            't6to7pm',
            't7to8pm',
            'tr8to9am',
            'tr9to10am',
            'tr10to11am',
            'tr11to12pm',
            'tr12to1pm',
            'tr1to2pm',
            'tr2to3pm',
            'tr3to4pm',
            'tr4to5pm',
            'tr5to6pm',
            'tr6to7pm',
            'tr7to8pm',
            'o8to9am',
            'o9to10am',
            'o10to11am',
            'o11to12pm',
            'o12to1pm',
            'o1to2pm',
            'o2to3pm',
            'o3to4pm',
            'o4to5pm',
            'o5to6pm',
            'o6to7pm',
            'o7to8pm',
            'targetGiven',
            'targetAchieved',
            'targetYetToAchieve',
            'daysLeft',
            'presentDays',
            'absentDays',
            'workingDays',
            'nonWorkingDays',
            'MAvgTotalCalls',
            'Mtotaltransfers',
            'MAvgtotaltransfers',

        ));
    }


    public function juniormonthly(Request $request)
    {
        $user = Auth::user();
        $createdByKey = "{$user->id}|junior";


        // Selected month (default current month in YYYY-MM)
        $selectedMonth = $request->input('selected_month', date('Y-m'));
        [$year, $month] = explode('-', $selectedMonth);

        // Total calls for this junior in the selected month (including hierarchical keys)
        $MtotalCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->count();

        $Mtotaltransfers = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('transfers', 1)
            ->count();

        // Total "Called & Mailed" calls
        $McalledAndMailedCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->count();

        // Total other calls (not "Called & Mailed")
        $MotherCalls = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->count();

        // --- Daily "Called & Mailed + Ready To Pay" counts ---
        $dailyCalledMailed = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        // --- Daily "Other Calls" counts ---
        $dailyOtherCalls = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where(function ($q) {
                $q->where('Exe_Remarks', '<>', 'Called & Mailed')
                    ->orWhereNull('Exe_Remarks');
            })
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        // --- Daily "Transfers" counts ---
        $dailyTransfers = GoogleSheetData::selectRaw('DAY(updated_at) as day, COUNT(*) as count')
            ->where('created_by', 'like', "{$createdByKey}%")
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->where('transfers', 1)
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        $holidayDates = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->where('is_holiday', 1)
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // --- Create daily variables 1 to 31 ---
        $tDay1  = $dailyCalledMailed[1]  ?? 0;
        $tDay2  = $dailyCalledMailed[2]  ?? 0;
        $tDay3  = $dailyCalledMailed[3]  ?? 0;
        $tDay4  = $dailyCalledMailed[4]  ?? 0;
        $tDay5  = $dailyCalledMailed[5]  ?? 0;
        $tDay6  = $dailyCalledMailed[6]  ?? 0;
        $tDay7  = $dailyCalledMailed[7]  ?? 0;
        $tDay8  = $dailyCalledMailed[8]  ?? 0;
        $tDay9  = $dailyCalledMailed[9]  ?? 0;
        $tDay10 = $dailyCalledMailed[10] ?? 0;
        $tDay11 = $dailyCalledMailed[11] ?? 0;
        $tDay12 = $dailyCalledMailed[12] ?? 0;
        $tDay13 = $dailyCalledMailed[13] ?? 0;
        $tDay14 = $dailyCalledMailed[14] ?? 0;
        $tDay15 = $dailyCalledMailed[15] ?? 0;
        $tDay16 = $dailyCalledMailed[16] ?? 0;
        $tDay17 = $dailyCalledMailed[17] ?? 0;
        $tDay18 = $dailyCalledMailed[18] ?? 0;
        $tDay19 = $dailyCalledMailed[19] ?? 0;
        $tDay20 = $dailyCalledMailed[20] ?? 0;
        $tDay21 = $dailyCalledMailed[21] ?? 0;
        $tDay22 = $dailyCalledMailed[22] ?? 0;
        $tDay23 = $dailyCalledMailed[23] ?? 0;
        $tDay24 = $dailyCalledMailed[24] ?? 0;
        $tDay25 = $dailyCalledMailed[25] ?? 0;
        $tDay26 = $dailyCalledMailed[26] ?? 0;
        $tDay27 = $dailyCalledMailed[27] ?? 0;
        $tDay28 = $dailyCalledMailed[28] ?? 0;
        $tDay29 = $dailyCalledMailed[29] ?? 0;
        $tDay30 = $dailyCalledMailed[30] ?? 0;
        $tDay31 = $dailyCalledMailed[31] ?? 0;

        $oDay1  = $dailyOtherCalls[1]  ?? 0;
        $oDay2  = $dailyOtherCalls[2]  ?? 0;
        $oDay3  = $dailyOtherCalls[3]  ?? 0;
        $oDay4  = $dailyOtherCalls[4]  ?? 0;
        $oDay5  = $dailyOtherCalls[5]  ?? 0;
        $oDay6  = $dailyOtherCalls[6]  ?? 0;
        $oDay7  = $dailyOtherCalls[7]  ?? 0;
        $oDay8  = $dailyOtherCalls[8]  ?? 0;
        $oDay9  = $dailyOtherCalls[9]  ?? 0;
        $oDay10 = $dailyOtherCalls[10] ?? 0;
        $oDay11 = $dailyOtherCalls[11] ?? 0;
        $oDay12 = $dailyOtherCalls[12] ?? 0;
        $oDay13 = $dailyOtherCalls[13] ?? 0;
        $oDay14 = $dailyOtherCalls[14] ?? 0;
        $oDay15 = $dailyOtherCalls[15] ?? 0;
        $oDay16 = $dailyOtherCalls[16] ?? 0;
        $oDay17 = $dailyOtherCalls[17] ?? 0;
        $oDay18 = $dailyOtherCalls[18] ?? 0;
        $oDay19 = $dailyOtherCalls[19] ?? 0;
        $oDay20 = $dailyOtherCalls[20] ?? 0;
        $oDay21 = $dailyOtherCalls[21] ?? 0;
        $oDay22 = $dailyOtherCalls[22] ?? 0;
        $oDay23 = $dailyOtherCalls[23] ?? 0;
        $oDay24 = $dailyOtherCalls[24] ?? 0;
        $oDay25 = $dailyOtherCalls[25] ?? 0;
        $oDay26 = $dailyOtherCalls[26] ?? 0;
        $oDay27 = $dailyOtherCalls[27] ?? 0;
        $oDay28 = $dailyOtherCalls[28] ?? 0;
        $oDay29 = $dailyOtherCalls[29] ?? 0;
        $oDay30 = $dailyOtherCalls[30] ?? 0;
        $oDay31 = $dailyOtherCalls[31] ?? 0;

        $trDay1  = $dailyTransfers[1]  ?? 0;
        $trDay2  = $dailyTransfers[2]  ?? 0;
        $trDay3  = $dailyTransfers[3]  ?? 0;
        $trDay4  = $dailyTransfers[4]  ?? 0;
        $trDay5  = $dailyTransfers[5]  ?? 0;
        $trDay6  = $dailyTransfers[6]  ?? 0;
        $trDay7  = $dailyTransfers[7]  ?? 0;
        $trDay8  = $dailyTransfers[8]  ?? 0;
        $trDay9  = $dailyTransfers[9]  ?? 0;
        $trDay10 = $dailyTransfers[10] ?? 0;
        $trDay11 = $dailyTransfers[11] ?? 0;
        $trDay12 = $dailyTransfers[12] ?? 0;
        $trDay13 = $dailyTransfers[13] ?? 0;
        $trDay14 = $dailyTransfers[14] ?? 0;
        $trDay15 = $dailyTransfers[15] ?? 0;
        $trDay16 = $dailyTransfers[16] ?? 0;
        $trDay17 = $dailyTransfers[17] ?? 0;
        $trDay18 = $dailyTransfers[18] ?? 0;
        $trDay19 = $dailyTransfers[19] ?? 0;
        $trDay20 = $dailyTransfers[20] ?? 0;
        $trDay21 = $dailyTransfers[21] ?? 0;
        $trDay22 = $dailyTransfers[22] ?? 0;
        $trDay23 = $dailyTransfers[23] ?? 0;
        $trDay24 = $dailyTransfers[24] ?? 0;
        $trDay25 = $dailyTransfers[25] ?? 0;
        $trDay26 = $dailyTransfers[26] ?? 0;
        $trDay27 = $dailyTransfers[27] ?? 0;
        $trDay28 = $dailyTransfers[28] ?? 0;
        $trDay29 = $dailyTransfers[29] ?? 0;
        $trDay30 = $dailyTransfers[30] ?? 0;
        $trDay31 = $dailyTransfers[31] ?? 0;

        $juniorUser = $user;

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

        $targetAchieved = GoogleSheetData::whereRaw(
            "created_by REGEXP '^{$juniorUser->id}\\\\|junior:[0-9]+\\\\|senior:[0-9]+\\\\|accountant(.*)?$'"
        )
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

        $presentDays    = 0;
        $halfDays       = 0;
        $absentDays     = 0;
        $workingDays    = 0;
        $nonWorkingDays = 0;

        $today = now()->startOfDay(); // 🔒 freeze today until end of day

        // Loop through each day
        foreach ($daysInMonth as $day) {
            /** @var Carbon $day */
            $dateStr = $day->format('Y-m-d');

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
        $MAvgTotalCalls = $presentDays > 0 ? intval($McalledAndMailedCalls / $presentDays) : 0;
        $MAvgtotaltransfers = $presentDays > 0 ? intval($Mtotaltransfers / $presentDays) : 0;

        return view('reports.juniormonthly', compact(
            'juniorUser',
            'MtotalCalls',
            'McalledAndMailedCalls',
            'MotherCalls',
            'selectedMonth',
            'tDay1',
            'tDay2',
            'tDay3',
            'tDay4',
            'tDay5',
            'tDay6',
            'tDay7',
            'tDay8',
            'tDay9',
            'tDay10',
            'tDay11',
            'tDay12',
            'tDay13',
            'tDay14',
            'tDay15',
            'tDay16',
            'tDay17',
            'tDay18',
            'tDay19',
            'tDay20',
            'tDay21',
            'tDay22',
            'tDay23',
            'tDay24',
            'tDay25',
            'tDay26',
            'tDay27',
            'tDay28',
            'tDay29',
            'tDay30',
            'tDay31',
            'oDay1',
            'oDay2',
            'oDay3',
            'oDay4',
            'oDay5',
            'oDay6',
            'oDay7',
            'oDay8',
            'oDay9',
            'oDay10',
            'oDay11',
            'oDay12',
            'oDay13',
            'oDay14',
            'oDay15',
            'oDay16',
            'oDay17',
            'oDay18',
            'oDay19',
            'oDay20',
            'oDay21',
            'oDay22',
            'oDay23',
            'oDay24',
            'oDay25',
            'oDay26',
            'oDay27',
            'oDay28',
            'oDay29',
            'oDay30',
            'oDay31',
            'trDay1',
            'trDay2',
            'trDay3',
            'trDay4',
            'trDay5',
            'trDay6',
            'trDay7',
            'trDay8',
            'trDay9',
            'trDay10',
            'trDay11',
            'trDay12',
            'trDay13',
            'trDay14',
            'trDay15',
            'trDay16',
            'trDay17',
            'trDay18',
            'trDay19',
            'trDay20',
            'trDay21',
            'trDay22',
            'trDay23',
            'trDay24',
            'trDay25',
            'trDay26',
            'trDay27',
            'trDay28',
            'trDay29',
            'trDay30',
            'trDay31',
            'targetGiven',
            'targetAchieved',
            'targetYetToAchieve',
            'daysLeft',
            'presentDays',
            'absentDays',
            'workingDays',
            'nonWorkingDays',
            'MAvgTotalCalls',
            'Mtotaltransfers',
            'MAvgtotaltransfers',
        ));
    }
}
