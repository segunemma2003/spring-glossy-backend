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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique()->index();
            $table->string('sku')->unique()->index();
            $table->text('description');
            $table->decimal('price', 10, 2)->index();
            $table->json('category')->nullable();
            $table->json('colors')->nullable();
            $table->json('images')->nullable(); // Store S3 paths
            $table->boolean('is_new')->default(false)->index();
            $table->boolean('is_best_seller')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('stock_quantity')->default(0)->index();
            $table->string('weight')->nullable();
            $table->string('dimensions')->nullable();
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['is_active', 'is_best_seller']);
            $table->index(['is_active', 'is_new']);
            $table->index(['is_active', 'price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
