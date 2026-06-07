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
        Schema::create('store_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('status_title');
            $table->string('status_subtitle');
            $table->text('status_description')->nullable();
            $table->string('status_label')->nullable();
            $table->string('branch_title');
            $table->string('address');
            $table->string('hours');
            $table->string('action_label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_profiles');
    }
};
