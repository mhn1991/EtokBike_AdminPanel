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
        Schema::create('mobile_screen_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobile_screen_id')->constrained()->cascadeOnDelete();
            $table->string('section_id');
            $table->string('type');
            $table->json('data')->nullable();
            $table->json('layout')->nullable();
            $table->json('style')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['mobile_screen_id', 'section_id']);
            $table->index(['mobile_screen_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_screen_sections');
    }
};
