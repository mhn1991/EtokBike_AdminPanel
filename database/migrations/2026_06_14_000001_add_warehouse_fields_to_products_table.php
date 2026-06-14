<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('sku')->nullable()->unique()->after('slug');
            $table->unsignedInteger('stock_quantity')->default(0)->after('stock_label');
            $table->unsignedInteger('reserved_quantity')->default(0)->after('stock_quantity');
            $table->unsignedInteger('minimum_stock')->default(0)->after('reserved_quantity');
            $table->string('warehouse_location')->nullable()->after('minimum_stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['sku']);
            $table->dropColumn([
                'sku',
                'stock_quantity',
                'reserved_quantity',
                'minimum_stock',
                'warehouse_location',
            ]);
        });
    }
};
