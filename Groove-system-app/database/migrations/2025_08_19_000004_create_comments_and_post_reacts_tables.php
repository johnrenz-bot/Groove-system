<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Comments table
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id'); // FK will be added later
            $table->char('client_id', 4)->nullable();
            $table->char('coach_id', 4)->nullable();
            $table->text('body');
            $table->timestamps();

            // Foreign keys for clients and coaches
            $table->foreign('client_id')->references('client_id')->on('clients')->onDelete('cascade');
            $table->foreign('coach_id')->references('coach_id')->on('coaches')->onDelete('cascade');
        });

        // Add foreign key to community_posts separately
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('post_id')
                  ->references('id')
                  ->on('community_posts')
                  ->onDelete('cascade');
        });

        // Post reacts table
        Schema::create('post_reacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->string('reactor_type'); // 'client' or 'coach'
            $table->string('reactor_id');   // the client_id or coach_id (char(4) in your app)
            $table->timestamps();

            // Foreign key to community_posts
            $table->foreign('post_id')->references('id')->on('community_posts')->onDelete('cascade');

            // Ensure unique combination
            $table->unique(['post_id', 'reactor_type', 'reactor_id'], 'post_reactor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_reacts');
        Schema::dropIfExists('comments');
    }
};
