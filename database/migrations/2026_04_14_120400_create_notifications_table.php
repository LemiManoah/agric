<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('notifiable');
            $table->string('template_key')->index();
            $table->string('channel', 50)->index();
            $table->string('recipient')->index();
            $table->string('subject')->nullable();
            $table->json('payload')->nullable();
            $table->longText('rendered_message')->nullable();
            $table->string('status', 50)->index();
            $table->string('provider_message_id')->nullable()->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('delivered_at')->nullable()->index();
            $table->timestamp('failed_at')->nullable()->index();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['template_key', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
