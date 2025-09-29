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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // Polymorphic sender and receiver
            $table->morphs('sender');   // sender_id + sender_type
            $table->morphs('receiver'); // receiver_id + receiver_type

            // Content fields
            $table->text('message')->nullable();          // message text (optional)
            $table->string('media_path')->nullable();     // for uploaded image/video
            $table->string('location_url')->nullable();   // for Google Maps link
            $table->timestamp('edited_at')->nullable();   // if edited

            $table->softDeletes();                        // soft delete support
            $table->timestamps();                         // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
