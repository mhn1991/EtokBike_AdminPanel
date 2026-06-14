<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('site_name')->default('EtokBike');
            $table->string('default_title')->nullable();
            $table->text('default_description')->nullable();
            $table->string('default_og_image')->nullable();
            $table->string('twitter_handle')->nullable();
            $table->json('social_profiles')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};
