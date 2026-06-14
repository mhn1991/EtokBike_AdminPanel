<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('seo_title')->nullable()->after('description');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->string('canonical_url')->nullable()->after('seo_description');
            $table->string('robots')->default('index,follow')->after('canonical_url');
            $table->string('og_title')->nullable()->after('robots');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('og_image')->nullable()->after('og_description');
            $table->boolean('include_in_sitemap')->default(true)->after('og_image');
            $table->decimal('sitemap_priority', 2, 1)->default(0.7)->after('include_in_sitemap');
            $table->string('sitemap_change_frequency')->default('weekly')->after('sitemap_priority');
        });

        Schema::table('product_categories', function (Blueprint $table): void {
            $table->string('seo_title')->nullable()->after('label');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->string('canonical_url')->nullable()->after('seo_description');
            $table->string('robots')->default('index,follow')->after('canonical_url');
            $table->string('og_title')->nullable()->after('robots');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('og_image')->nullable()->after('og_description');
            $table->boolean('include_in_sitemap')->default(true)->after('og_image');
            $table->decimal('sitemap_priority', 2, 1)->default(0.8)->after('include_in_sitemap');
            $table->string('sitemap_change_frequency')->default('weekly')->after('sitemap_priority');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'seo_title',
                'seo_description',
                'canonical_url',
                'robots',
                'og_title',
                'og_description',
                'og_image',
                'include_in_sitemap',
                'sitemap_priority',
                'sitemap_change_frequency',
            ]);
        });

        Schema::table('product_categories', function (Blueprint $table): void {
            $table->dropColumn([
                'seo_title',
                'seo_description',
                'canonical_url',
                'robots',
                'og_title',
                'og_description',
                'og_image',
                'include_in_sitemap',
                'sitemap_priority',
                'sitemap_change_frequency',
            ]);
        });
    }
};
