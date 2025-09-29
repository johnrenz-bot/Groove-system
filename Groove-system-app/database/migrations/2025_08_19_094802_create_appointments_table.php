<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->unsignedInteger('appointment_id')->primary(); // Custom 5-digit ID
            $table->char('client_id', 4);
            $table->char('coach_id', 4);
            $table->string('talent')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('contact', 20);
            $table->string('address');
            $table->date('date');
            $table->string('start_time', 20);
            $table->string('end_time', 20);
            $table->string('session_type', 100)->nullable();
            $table->string('experience');
            $table->string('purpose');
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->text('feedback')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('client_id')
                ->references('client_id')
                ->on('clients')
                ->onDelete('cascade');

            $table->foreign('coach_id')
                ->references('coach_id')
                ->on('coaches')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
