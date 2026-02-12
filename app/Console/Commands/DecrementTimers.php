<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;
use Carbon\Carbon;

class DecrementTimers extends Command
{
    protected $signature = 'timers:decrement';
    protected $description = 'Decrement running/resumed timers every 3 seconds with exactly 60 seconds per minute';

    protected $lastRemaining = [];

    public function handle()
    {
        $this->info('Timer decrement process started...');

        $startTime = Carbon::now();
        $endTime   = $startTime->copy()->addMinute()->startOfMinute();

        $interval = 3; // seconds
        $nextTick = microtime(true);

        $cycleCounter = 0;
        $totalCyclesPerMinute = 60 / $interval; // 20 cycles

        while (Carbon::now()->lessThan($endTime)) {
            try {
                $latestTimers = DB::table('user_timer_logs')
                    ->whereIn('id', function ($sub) {
                        $sub->select(DB::raw('MAX(id)'))
                            ->from('user_timer_logs')
                            ->where('status', 'running')
                            ->groupBy('user_id');
                    })
                    ->where('pause_type', 'resume')
                    ->get();

                $affected = 0;
                $decrement = 3;

                foreach ($latestTimers as $timer) {
                    $userId = $timer->user_id;
                    $currentRemaining = $timer->remaining_seconds;

                    if (
                        !isset($this->lastRemaining[$userId]) ||
                        $this->lastRemaining[$userId] === $currentRemaining
                    ) {
                        DB::table('user_timer_logs')
                            ->where('id', $timer->id)
                            ->update([
                                'remaining_seconds' => max($currentRemaining - $decrement, 0),
                                'updated_at'        => now(),
                            ]);

                        $this->lastRemaining[$userId] = $currentRemaining - $decrement;
                        $affected++;
                    } else {
                        $this->lastRemaining[$userId] = $currentRemaining;
                    }
                }

                if ($affected > 0) {
                    $this->line(
                        now() . " → Updated {$affected} timers (-{$decrement}s)"
                    );
                }


                $cycleCounter++;
                if ($cycleCounter >= $totalCyclesPerMinute) {
                    break;
                }

                $nextTick += $interval;
                $sleepTime = $nextTick - microtime(true);

                if ($sleepTime > 0) {
                    usleep((int) ($sleepTime * 1_000_000));
                }
            } catch (Throwable $e) {
                $this->error(" Error: " . $e->getMessage());

                $cycleCounter++;
                $nextTick += $interval;

                $sleepTime = $nextTick - microtime(true);
                if ($sleepTime > 0) {
                    usleep((int) ($sleepTime * 1_000_000));
                }
            }
        }

        $this->info('⏹ Timer decrement process completed for this minute (60 seconds applied).');
    }
}
