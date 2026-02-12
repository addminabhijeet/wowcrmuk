<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserTimerLog;

class TimerApiController extends Controller
{
    /**
     * GET: Fetch latest timers for all users
     * POST: Update remaining_seconds for a specific user
     */
    public function update(Request $request)
    {
        if ($request->isMethod('post')) {
            // Update remaining_seconds for a user
            $userId = $request->input('user_id');
            $remainingSeconds = $request->input('remaining_seconds');
            $status = $request->input('status', 'running');
            $pauseType = $request->input('pause_type', 'resume');

            $timer = UserTimerLog::where('user_id', $userId)->latest()->first();
            if (!$timer) {
                return response()->json(['success' => false, 'message' => 'Timer not found'], 404);
            }

            $timer->remaining_seconds = $remainingSeconds;
            $timer->status = $status;
            $timer->pause_type = $pauseType;
            $timer->updated_at = now();
            $timer->save();

            return response()->json([
                'success' => true,
                'message' => "User {$userId} timer updated",
                'timer'   => [
                    'user_id' => $timer->user_id,
                    'remaining_seconds' => $timer->remaining_seconds,
                    'status' => $timer->status,
                    'pause_type' => $timer->pause_type,
                ]
            ]);
        }

        // GET: Fetch latest timer for each user
        $latestTimers = UserTimerLog::select('user_id')
            ->groupBy('user_id')
            ->get()
            ->map(function ($user) {
                $timer = UserTimerLog::where('user_id', $user->user_id)
                    ->latest('created_at')
                    ->first();

                return [
                    'user_id' => $timer->user_id,
                    'remaining_seconds' => $timer->remaining_seconds,
                    'status' => $timer->status,
                    'pause_type' => $timer->pause_type,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $latestTimers
        ]);
    }
}
