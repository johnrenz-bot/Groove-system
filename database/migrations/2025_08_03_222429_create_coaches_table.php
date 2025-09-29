<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaches', function (Blueprint $table) {
            // Primary key (string, non-incrementing)
            $table->string('coach_id', 10)->primary();

            // Basic information
            $table->string('firstname', 80);
            $table->string('middlename', 80)->nullable();
            $table->string('lastname', 80);
            $table->string('suffix', 10)->nullable();
            $table->date('birthdate');

            // Address details
            $table->string('region_code', 10)->nullable();
            $table->string('province_code', 10)->nullable();
            $table->string('city_code', 10)->nullable();
            $table->string('barangay_code', 10)->nullable();
            $table->string('region_name', 120)->nullable();
            $table->string('province_name', 120)->nullable();
            $table->string('city_name', 120)->nullable();
            $table->string('barangay_name', 120)->nullable();
            $table->string('street', 160)->nullable();
            $table->string('postal_code', 12)->nullable();

            // Contact and account
            $table->string('contact', 20);                 // e.g. 09XXXXXXXXX (store normalized)
            $table->string('email', 255)->unique();
            $table->string('username', 60)->unique();
            $table->string('password');

            // Profile
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->string('status', 20)->default('offline');  // no ->change() inside create()

            // Talents and genres (you can store JSON instead if you prefer)
            $table->string('talents', 255);
            $table->text('genres')->nullable();

            // Service/booking info
            $table->string('role', 40)->nullable();
            $table->integer('service_fee')->nullable();           // in pesos
            $table->string('duration', 40)->nullable();
            $table->enum('payment', ['cash','online'])->nullable();

            // NEW online payment details
            $table->string('payment_provider', 40)->nullable();   // gcash | maya | paypal | bank
            $table->string('payment_handle', 120)->nullable();    // mobile number / PayPal email / bank acct no.

            $table->integer('notice_hours')->nullable();
            $table->integer('notice_days')->nullable();
            $table->string('method', 255)->nullable();            // email for cancellations

            // File uploads
            $table->string('portfolio_path')->nullable();
            $table->string('valid_id_path')->nullable();
            $table->string('id_selfie_path')->nullable();

            // Verification & terms
            $table->boolean('terms_accepted')->default(false);
            $table->string('email_verification_code', 100)->nullable();
            $table->boolean('email_verified')->default(false);
            $table->boolean('account_verified')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable(); // references admins.id if needed

            $table->timestamps();

            // If you have an admins table for approved_by foreign key, uncomment:
            // $table->foreign('approved_by')->references('id')->on('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coaches');
    }
};
