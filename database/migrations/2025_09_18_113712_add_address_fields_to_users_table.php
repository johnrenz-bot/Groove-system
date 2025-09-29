<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_address_fields_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('email'); // if wala pa
            $table->date('birth_date')->nullable();

            $table->string('region_code', 20)->nullable();
            $table->string('province_code', 20)->nullable();
            $table->string('city_code', 20)->nullable();
            $table->string('barangay_code', 20)->nullable();

            $table->string('region_name')->nullable();
            $table->string('province_name')->nullable();
            $table->string('city_name')->nullable();
            $table->string('barangay_name')->nullable();

            $table->string('street', 120)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('address_summary')->nullable();
            $table->string('contact', 20)->nullable(); // +63XXXXXXXXXX
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username','birth_date',
                'region_code','province_code','city_code','barangay_code',
                'region_name','province_name','city_name','barangay_name',
                'street','postal_code','address_summary','contact'
            ]);
        });
    }
};
