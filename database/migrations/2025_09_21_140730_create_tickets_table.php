<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Optional link to your custom PKs
            $table->char('client_id', 4)->nullable()->index();
            $table->char('coach_id', 4)->nullable()->index();

            // Core fields (allow public submit without IDs)
            $table->string('name');              // requester name
            $table->string('email');             // requester email
            $table->string('subject');           // ticket subject
            $table->longText('message');         // ticket body

            // Simple workflow flags
            $table->string('status', 20)->default('open');     // open|pending|closed
            $table->string('priority', 20)->default('normal'); // low|normal|high

            // ✅ SINGLE attachment fields (image/file)
            // Store the file under storage/app/public/... then save the relative path here (e.g. "tickets/2025/09/abc123.png")
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();          // original filename (for display)
            $table->string('attachment_mime', 100)->nullable();     // e.g. image/png
            $table->unsignedBigInteger('attachment_size')->nullable(); // bytes

            // Fast count helper (0 or 1 for single-attachment setup)
            $table->unsignedInteger('attachment_count')->default(0);

            $table->timestamps();

            // FKs to your char(4) PKs
            $table->foreign('client_id')
                  ->references('client_id')->on('clients')
                  ->nullOnDelete();

            $table->foreign('coach_id')
                  ->references('coach_id')->on('coaches')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
