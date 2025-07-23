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
        Schema::create('reservation_requests', function (Blueprint $table) {
            $table->id('reservation_id')->primary();
            $table->string('name');
            $table->string('organization');
            $table->string('contact_no');
            $table->string('purpose');
            $table->text('instruction');
            $table->string('electric_equipment');
            $table->date('transaction_date');
            $table->enum('reservation_type', ['Single', 'Consecutive', 'Multiple']);

            $table->unsignedBigInteger('facility_id');
            $table->foreign('facility_id')
                ->references('facility_id')
                ->on('facilities')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('equipment_id');
            $table->foreign('equipment_id')
                ->references('equipment_id')
                ->on('equipments')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->text('signature')->nullable();
            $table->enum('status', ['pending', 'accepted', 'declined', 'completed', 'cancelled'])->default('pending');
            $table->string('total_payment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_requests');
    }
};
