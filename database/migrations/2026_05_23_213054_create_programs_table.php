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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_category_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('subtitle');
            $table->date('date_value');
            $table->string('date_label');
            $table->string('program_state')->default('future')->index();
            $table->string('status_label')->nullable();
            $table->string('book_label')->nullable();
            $table->string('view_label')->nullable();
            $table->string('ad_title')->nullable();
            $table->text('advertisement')->nullable();
            $table->json('details')->nullable();
            $table->string('gallery_title')->nullable();
            $table->string('thumbnail_text')->default('ETOK');
            $table->string('thumbnail_color', 16)->default('#101114');
            $table->string('image_url')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('reserved_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
