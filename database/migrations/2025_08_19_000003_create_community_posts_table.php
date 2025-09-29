<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();

            // Must exactly match parents' type/length
            $table->char('client_id', 4)->nullable();
            $table->char('coach_id', 4)->nullable();

            $table->string('caption');
            $table->string('media_path')->nullable();
            $table->string('talent');

            $table->timestamps();
            $table->softDeletes();

            // FKs require indexed/primary parent columns (we set primary above)
            $table->foreign('client_id')
                  ->references('client_id')
                  ->on('clients')
                  ->cascadeOnDelete();

            $table->foreign('coach_id')
                  ->references('coach_id')
                  ->on('coaches')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_posts');
    }
};
