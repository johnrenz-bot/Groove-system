    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up(): void {
            Schema::create('client_profile_posts', function (Blueprint $table) {
                $table->id();
                $table->string('client_name')->nullable();
                $table->string('client_id');
                $table->string('media_path');
                $table->text('caption')->nullable();
                $table->timestamps();

                $table->foreign('client_id')
                    ->references('client_id')
                    ->on('clients')
                    ->onDelete('cascade');
            });
        }

        public function down(): void {
            Schema::dropIfExists('client_profile_posts');
        }
    };

