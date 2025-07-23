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
        Schema::table('consecutive', function (Blueprint $table) {
            $table->date('intermediate_date')->nullable()->change();
            $table->time('intermediate_time_from')->nullable()->change();
            $table->time('intermediate_time_to')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consecutive', function (Blueprint $table) {
            $table->date('intermediate_date')->nullable(false)->change();
            $table->time('intermediate_time_from')->nullable(false)->change();
            $table->time('intermediate_time_to')->nullable(false)->change();
        });
    }
};
