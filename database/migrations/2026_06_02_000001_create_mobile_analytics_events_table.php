<?php

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
        Schema::create('mobile_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 128);
            $table->string('session_id', 128)->nullable();
            $table->string('event_name', 64);
            $table->string('screen_id', 64)->nullable();
            $table->string('action', 128)->nullable();
            $table->string('platform', 32)->nullable();
            $table->string('app_version', 32)->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'occurred_at']);
            $table->index(['event_name', 'occurred_at']);
            $table->index(['screen_id', 'occurred_at']);
            $table->index('occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_analytics_events');
    }
};
