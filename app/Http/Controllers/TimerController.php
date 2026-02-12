<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserTimerLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\UserTimerPause;
use App\Models\TimerSetting;

class TimerController extends Controller
{

    public function index()
    {
        $timersetting = TimerSetting::first();
        return view('timers.admin', compact('timersetting'));
    }

    // Update Work Day Duration only
    public function updateWorkDay(Request $request)
    {
        $request->validate([
            'hours' => 'required|integer|min:0|max:24',
            'minutes' => 'required|integer|min:0|max:59',
        ]);

        $timersetting = TimerSetting::first() ?? new TimerSetting();

        $timersetting->work_day_seconds = ($request->hours * 3600) + ($request->minutes * 60);
        $timersetting->save();

        return redirect()->back()->with('success', 'Work Day Duration updated successfully!');
    }

    // Update Daily Base Time only
    public function updateBaseTime(Request $request)
    {
        $request->validate([
            'daily_base_time' => 'required|date_format:H:i',
        ]);

        $timersetting = TimerSetting::first() ?? new TimerSetting();
        $timersetting->daily_base_time = $request->daily_base_time;
        $timersetting->save();

        return redirect()->back()->with('success', 'Daily Base Time updated successfully!');
    }

    public function seniorTimers()
    {
        $timerSetting = TimerSetting::first();
        $workDaySeconds = $timerSetting ? $timerSetting->work_day_seconds : 8 * 60 * 60;

        $juniors = User::where('role', 'junior')
        ->where('is_deleted', 0)
        ->get();

        $login_user = User::where('status')->where('is_deleted', 0)->get();
        $timers = $juniors->map(function ($junior) use ($workDaySeconds) {
            $timer = UserTimerLog::where('user_id', $junior->id)->latest()->first();

            if ($timer) {
                $remaining_seconds = $timer->remaining_seconds;
                $elapsed_seconds = $workDaySeconds - $remaining_seconds;
                $status = $timer->status;
                $button_status = $timer->button_status;
                $notice_status = $timer->notice_status;
                $pause_type        = $timer->pause_type;
            } else {
                $remaining_seconds = $workDaySeconds;
                $elapsed_seconds = 0;
                $status = 'running';
                $button_status = 1;
                $notice_status = 0;
                $pause_type        = null;
            }

            return [
                'user_id'          => $junior->id,
                'name'             => $junior->name,
                'image'             => $junior->image,
                'email'            => $junior->email,
                'remaining_seconds' => $remaining_seconds,
                'elapsed_seconds'  => $elapsed_seconds,
                'status'           => $status,
                'button_status'    => $button_status,
                'notice_status'    => $notice_status,
                'pause_type'        => $pause_type,
            ];
        });

        return view('timers.senior', compact('timers', 'juniors', 'login_user'));
    }

    public function allseniorTimers()
    {
        $timerSetting = TimerSetting::first();
        $workDaySeconds = $timerSetting ? $timerSetting->work_day_seconds : 8 * 60 * 60;

        $juniors = User::where('role', 'senior')->where('is_deleted', 0)->get();
        $login_user = User::where('status')->where('is_deleted', 0)->get();
        $timers = $juniors->map(function ($junior) use ($workDaySeconds) {
            $timer = UserTimerLog::where('user_id', $junior->id)->latest()->first();

            if ($timer) {
                $remaining_seconds = $timer->remaining_seconds;
                $elapsed_seconds = $workDaySeconds - $remaining_seconds;
                $status = $timer->status;
                $button_status = $timer->button_status;
                $notice_status = $timer->notice_status;
                $pause_type        = $timer->pause_type;
            } else {
                $remaining_seconds = $workDaySeconds;
                $elapsed_seconds = 0;
                $status = 'running';
                $button_status = 1;
                $notice_status = 0;
                $pause_type        = null;
            }

            return [
                'user_id'          => $junior->id,
                'name'             => $junior->name,
                'image'             => $junior->image,
                'email'            => $junior->email,
                'remaining_seconds' => $remaining_seconds,
                'elapsed_seconds'  => $elapsed_seconds,
                'status'           => $status,
                'button_status'    => $button_status,
                'notice_status'    => $notice_status,
                'pause_type'        => $pause_type,
            ];
        });

        return view('timers.allsenior', compact('timers', 'juniors', 'login_user'));
    }

    public function toggleButtonStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'action'  => 'required|string|in:enable,disable',
        ]);

        $userId = $request->user_id;
        $action = $request->action;

        $timerLog = UserTimerLog::where('user_id', $userId)->latest()->first();
        if (!$timerLog) return response()->json(['success' => false, 'message' => 'Timer not found']);

        $timerLog->button_status = $action == 'enable' ? 1 : 0;
        $timerLog->save();

        return response()->json([
            'success' => true,
            'button_status' => $timerLog->button_status
        ]);
    }




    public function allJuniorTimers()
    {
        // Fetch work day duration from settings
        $timerSetting = TimerSetting::first();
        $workDaySeconds = $timerSetting ? $timerSetting->work_day_seconds : 8 * 60 * 60;

        // Fetch all juniors
        $juniors = User::where('role', 'junior')->where('is_deleted', 0)->get();

        $timers = $juniors->map(function ($junior) use ($workDaySeconds) {
            $timer = UserTimerLog::where('user_id', $junior->id)->latest()->first();

            // Default to full workday if no timer exists
            $remaining_seconds = $workDaySeconds;
            $status = 'running';
            $pause_type = null;

            if ($timer) {
                $remaining_seconds = $timer->remaining_seconds ?? $workDaySeconds;
                $status = $timer->status ?? 'running';
                $pause_type = $timer->pause_type ?? null;
            }

            return [
                'user_id'          => $junior->id,
                'remaining_seconds' => $remaining_seconds,
                'elapsed_seconds'  => $workDaySeconds - $remaining_seconds,
                'status'           => $status,
                'pause_type'       => $pause_type,
                'logout'           => $remaining_seconds <= 0,
            ];
        });

        return response()->json($timers);
    }
}
