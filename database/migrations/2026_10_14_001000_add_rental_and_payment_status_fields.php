<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_rentable')->default(false)->index();
            $table->decimal('rental_rate_per_day', 10, 2)->nullable();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('fulfillment_type', 16)->default('purchase');
            $table->unsignedSmallInteger('rental_days')->nullable();
            $table->date('rental_start_date')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 16)->default('pending')->index();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('fulfillment_type', 16)->default('purchase');
            $table->unsignedSmallInteger('rental_days')->nullable();
            $table->date('rental_start_date')->nullable();
            $table->date('return_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['fulfillment_type', 'rental_days', 'rental_start_date', 'return_date']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropColumn('payment_status');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['fulfillment_type', 'rental_days', 'rental_start_date']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_rentable']);
            $table->dropColumn(['is_rentable', 'rental_rate_per_day']);
        });
    }
};
