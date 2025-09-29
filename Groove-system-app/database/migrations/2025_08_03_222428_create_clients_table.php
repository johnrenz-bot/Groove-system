<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->char('client_id', 4)->primary();
            $table->string('role')->default('Client');

            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->date('birthdate');

            $table->string('region_code', 9);
            $table->string('province_code', 9);
            $table->string('city_code', 9);
            $table->string('barangay_code', 9);
            $table->string('region_name');
            $table->string('province_name');
            $table->string('city_name');
            $table->string('barangay_name');
            $table->string('street')->nullable();
            $table->string('postal_code', 10)->nullable();

            $table->string('address');
            $table->string('barangay');

            $table->string('contact')->unique();
            $table->string('talent')->default('N/A');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');

            $table->string('status')->default('offline');
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();

            // Uploaded ID path
            $table->string('valid_id_path')->nullable();

            $table->string('email_verification_code')->nullable()->index();
            $table->boolean('email_verified')->default(false);
            $table->boolean('terms_accepted')->default(true);

            // Admin approval fields
            $table->boolean('account_verified')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('admins')->nullOnDelete();

            $table->rememberToken();
            $table->timestamps();

            $table->index(['region_code', 'province_code', 'city_code', 'barangay_code'], 'clients_geo_codes_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
