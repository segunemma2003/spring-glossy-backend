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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->index();
            $table->string('order_number')->unique()->index();
            $table->string('status')->default('pending')->index();
            $table->string('payment_status')->default('pending')->index();
            $table->string('payment_method')->nullable()->index();
            $table->string('payment_reference')->nullable()->index();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->index();
            $table->json('shipping_address');
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('paystack_reference')->nullable()->index();
            $table->string('payment_receipt_path')->nullable(); // S3 path
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['user_id', 'status']);
            $table->index(['payment_status', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
