<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('financial_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->default('manual');
            $table->string('status')->default('pending')->index();
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('currency', 8)->default('IRR');
            $table->string('transaction_reference')->nullable()->index();
            $table->string('authorization_code')->nullable();
            $table->json('gateway_payload')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
