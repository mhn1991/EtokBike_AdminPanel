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
        Schema::create('mobile_screens', function (Blueprint $table) {
            $table->id();
            $table->string('screen_id')->unique();
            $table->string('title');
            $table->unsignedBigInteger('version')->default(1);
            $table->boolean('hide_title')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_screens');
    }
};
