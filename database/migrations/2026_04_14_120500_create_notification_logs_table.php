<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->string('event');
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['notification_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
