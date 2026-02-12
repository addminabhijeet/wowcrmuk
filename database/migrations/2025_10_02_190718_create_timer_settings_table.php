<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timer_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('work_day_seconds')->default(9 * 60 * 60); // 9 hours in seconds
            $table->time('daily_base_time')->default('20:00:00');      // 20:00 IST
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timer_settings');
    }
};
