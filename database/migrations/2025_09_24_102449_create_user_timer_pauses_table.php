<?php
// database/migrations/xxxx_xx_xx_create_user_timer_pauses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('user_timer_pauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_timer_log_id')->constrained('user_timer_logs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status');       // running / paused
            $table->string('pause_type')->nullable(); // lunch, tea, break, etc.
            $table->integer('remaining_seconds')->default(0);
            $table->integer('elapsed_seconds')->default(0);
            $table->timestamp('event_time'); // when the change happened
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_timer_pauses');
    }
};
