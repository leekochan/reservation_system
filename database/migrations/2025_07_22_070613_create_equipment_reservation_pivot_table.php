<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_reservation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('equipment_id');
            $table->date('reservation_date');
            $table->integer('quantity');
            $table->timestamps();

            $table->foreign('reservation_id')
                ->references('reservation_id')
                ->on('reservation_requests')
                ->onDelete('cascade');

            $table->foreign('equipment_id')
                ->references('equipment_id')
                ->on('equipments')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_reservation');
    }
};
