<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();

            $table->char('client_id', 4);
            $table->char('coach_id', 4);

            $table->date('agreement_date')->nullable();
            $table->string('appointment_price')->nullable();
            $table->string('session_duration')->nullable();
            $table->string('payment_method')->nullable();

            // Notice columns merged here
            $table->integer('notice_hours')->nullable();
            $table->integer('notice_days')->nullable();

            $table->string('cancellation_method')->nullable();

            $table->text('client_signature')->nullable();
            $table->string('coach_signature')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
