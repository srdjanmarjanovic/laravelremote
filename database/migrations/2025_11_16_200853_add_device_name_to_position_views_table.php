<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('position_views', function (Blueprint $table) {
            $table->string('device_name', 100)->nullable()->after('device_type');
            $table->string('browser', 50)->nullable()->after('device_name');
            $table->string('os', 50)->nullable()->after('browser');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('position_views', function (Blueprint $table) {
            $table->dropColumn(['device_name', 'browser', 'os']);
        });
    }
};
