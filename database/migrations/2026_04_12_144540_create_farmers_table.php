<?php

use App\Enums\RegistrationSource;
use App\Enums\VerificationStatus;
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
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->string('phone')->index();
            $table->string('national_id_number')->nullable()->unique();
            $table->string('passport_photo_path')->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('education_level')->nullable();
            $table->string('profession')->nullable();
            $table->unsignedInteger('household_size')->nullable();
            $table->unsignedInteger('number_of_dependants')->nullable();
            $table->json('languages_spoken')->nullable();
            $table->string('registration_source')->default(RegistrationSource::FieldOfficer->value)->index();
            $table->foreignId('registered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('verification_status')->default(VerificationStatus::Submitted->value)->index();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['full_name', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};
