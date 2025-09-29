<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('coach_profile_posts', function (Blueprint $table) {
            $table->id();
            $table->string('coach_name')->nullable();
            $table->char('coach_id', 4);
            $table->string('media_path');
            $table->text('caption')->nullable();
            $table->timestamps();

            $table->foreign('coach_id')
                  ->references('coach_id')
                  ->on('coaches')
                  ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('coach_profile_posts');
    }
};
