<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('google_sheet_data', function (Blueprint $table) {
            $table->string('created_by')->nullable()->after('data');
        });
    }

    public function down()
    {
        Schema::table('google_sheet_data', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }

};
