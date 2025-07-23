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
        Schema::create('admin_facility_blocks', function (Blueprint $table) {
            $table->id('block_id');
            $table->unsignedBigInteger('facility_id');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('purpose');
            $table->text('notes')->nullable();
            $table->string('status')->default('active'); // active, cancelled
            $table->timestamps();

            $table->foreign('facility_id')->references('facility_id')->on('facilities')->onDelete('cascade');
            $table->index(['facility_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_facility_blocks');
    }
};
