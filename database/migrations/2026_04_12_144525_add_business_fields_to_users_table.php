<?php

use App\Enums\UserStatus;
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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('status')->default(UserStatus::Active->value)->index()->after('password');
            $table->foreignId('region_id')->nullable()->after('theme_preference')->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->after('region_id')->constrained()->nullOnDelete();
            $table->timestamp('last_login_at')->nullable()->after('district_id');
            $table->foreignId('created_by')->nullable()->after('last_login_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('last_login_at');
            $table->dropConstrainedForeignId('district_id');
            $table->dropConstrainedForeignId('region_id');
            $table->dropColumn('status');
            $table->dropUnique('users_phone_unique');
            $table->dropColumn('phone');
        });
    }
};
