<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id('feedback_id'); // feedback ID
            $table->char('user_id', 4);   // client submitting feedback
            $table->char('coach_id', 4);  // coach being rated
            $table->tinyInteger('rating'); 
            $table->string('comment', 500);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('client_id')->on('clients')->onDelete('cascade');
            $table->foreign('coach_id')->references('coach_id')->on('coaches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks'); 
    }
};
